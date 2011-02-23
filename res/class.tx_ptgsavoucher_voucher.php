<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dorit Rottner (rottner@punkt.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Class for voucher objects in the pt_gsavoucher extension
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-01-22
 */
  
 /**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */
  
/**
 * Inclusion of extension specific libraries
 */
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherAccessor.php';  // extension specific database accessor class for voucher data
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherEncashAccessor.php';  // extension specific database accessor class for voucher data

/**
 * Inclusion of punkt.de libraries
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class

/**
 *  Class for voucher objects
 *
 * @access      public
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-01-22
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_voucher  {
    
    /**
     * Constants
     */

    const EXTKEY = 'tx_ptgsavoucher';
                                        // well known payment methods from GSA
    
    /**
     * Properties
     */

    protected $uid;                     // integer  unique Id of voucher Record
    protected $type;						// integer 0 for standard voucher, 1 for multi encashable voucher
    protected $code;                    // string   generated code of voucher
    protected $orderWrapperUid;                // integer  Id of the Order Object in the GSA shop extension   
    protected $pdfdocUid;             	// integer  Id of the related Pdf Document (id is not set if pdfdocument is not genrated)
    protected $gsaUid;                  // integer  Adress Id of the GSA user who has payed the voucher. Attribute is not set, if it is discount voucher
    protected $relatedDocNo;            // string   Docmunent Number of the GSA Record in table FSCHRIFT who is encashin the voucher. Attribute is not set, if it is discount voucher
    protected $categoryConfinement;     // string   Category for articles for which the voucher is confined. Attribute is empty if there is no confinement or for a single article 
    protected $articleConfinement;      // string   Article for which the voucher is confined. Attribute is empty if there is no confinement or confinement for whole category 
    protected $amount;                  // double   amount for this voucher.  
    protected $isPercent;               // boolean  amount for this voucher is given as percent  
    protected $oncePerOrder;            // boolean  only one voucher of this type allowed in order  
    protected $isEncashed;              // boolean  voucher is totally encashed  
    protected $encashedAmount;          // double   encashed amount for this voucher 
    protected $expiryDate;              // string   Expiry date of voucher. Only evaluated if voucher is not payed.
     
    
    
    /**
     * Class constructor - fills object's properties with param array data
     *
     * @param   integer     (optional) ID of the voucher Record. Set to 0 if you want to use the 2nd param.
     * @param   string      voucher Code 
     * @return  void   
     * @throws  tx_pttools_exception   if the first param is not numeric  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    public function __construct($voucherId=0, $voucherCode='') {
    
        trace('***** Creating new '.__CLASS__.' object. *****');
        
        tx_pttools_assert::isValidUid($voucherId,true,array('message'=>'Invalid Voucher Id: '.$voucherId));
                
        // if a customer record ID is given, retrieve customer array from database accessor (and overwrite 2nd param)
        if ($voucherId  > 0) {
            $voucherDataArr = tx_ptgsavoucher_voucherAccessor::getInstance()->selectVoucherByUid($voucherId);
        }
        
        if ($voucherCode  != '') {
            $voucherDataArr = tx_ptgsavoucher_voucherAccessor::getInstance()->selectVoucherByCode($voucherCode);
        }
        $this->setVoucherFromGivenArray($voucherDataArr);
            
        trace($this);
        
    }
    
    
    /***************************************************************************
     *   GENERAL METHODS
     **************************************************************************/
    
    /**
     * Sets the properties using data given by param array
     *
     * @param   array     Array containing voucher data to set as voucher object's properties; array keys have to be named exactly like the proprerties of this class.
     * @return  void        
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    protected function setVoucherFromGivenArray($voucherDataArr) {
        
        foreach (get_class_vars( __CLASS__ ) as $propertyname => $pvalue) {
            if (isset($voucherDataArr[$propertyname])) {
                $setter = 'set_'.$propertyname;
                $this->$setter($voucherDataArr[$propertyname]);
            }
        }
    }
    
    /**
     * returns array with data from all properties
     *
     * @param   void
     * @return  array   array with data from all properties        
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    protected function getDataArray() {

        $dataArray = array();

        foreach (get_class_vars( __CLASS__ ) as $propertyname => $pvalue) {
            $getter = 'get_'.$propertyname;
            $dataArray[$propertyname] = $this->$getter();
        }

        return $dataArray;
    }
        
    /**
     * Stores current voucher data in TYPO3 DB
     * if uid is non-zero, these records are updated;
     * otherwise new records are created
     *
     * @param   void        
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    public function storeSelf() {
        trace('[METHOD] '.__METHOD__);
        $dataArray = $this->getDataArray();
        $this->set_uid(tx_ptgsavoucher_voucherAccessor::getInstance()->storeVoucherData($dataArray));
        

    }
        
    /**
     * Generates voucher code  
     *
     * @param   integer id could be an integer or the gsa payedUid from order         
     * @return  boolean true or false if Voucher is encashable
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-07-15
     */
    public function createCode($id = 0) {
		// create code strings until unique or more than 9 tries
		$cnt = 1;
		if (!$id) {
			$id = $this->get_payedGsaUid();
		}
		if (!id) {
			$id = time();
		}
		$seedstr = t3lib_div::stdAuthCode($id, '', 8);
		while ($cnt < 10) {
			$candidate = $seedstr.'-v-'.$cnt.'-'.md5(uniqid('',false));
			$checkArray = tx_ptgsavoucher_voucherAccessor::getInstance()->selectVoucherByCode($candidate);
			if (!(isset($checkArray['uid']) && (intval($checkArray['uid']) > 0))) {
				break;	// use this candidate
			}
			$cnt++;
		}
		if ($cnt >= 10) {	// did not find a unique id in 9 tries
			throw new tx_pttools_exception('could not assign unique id', tx_pttools_exception::EXCP_INTERNAL);
		}
		$this->set_code($candidate);
    	
    }
    
    /**
     * Proofs if voucher is dicount voucher 
     *
     * @param   void        
     * @return  boolean flag if voucher is discount voucher 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    public function isDiscountVoucher() {
        if (!$this->get_gsaUid()) {
        	trace('Discount Voucher');
        	return true;
        } else {
            trace('Gift Voucher');
        	return false;
        }
    }

    /**
     * Proofs if voucher could encashed for this Customer 
     *
     * @param   object   tx_ptgsashop_order         
     * @return  integer  possible encashableError or 0 if encashable 1, wrong voucher code, 2 voucher already encashed, 3 nothing to encash, 4 gift voucher could not be percent, 5 voucher expired, 6 voucher not valid for this articel 
     * @author  Dorit Rottner <rottner@punkt.de>, Daniel Lienert <lienert@punkt.de>
     * @since   2008-09-24
     */
    public function getEncashable(tx_ptgsashop_order $orderObj=null) {

    	trace('[METHOD] '.__METHOD__);
        #$GLOBALS['trace'] = 1;
        trace($this,0,'this');
        $encashableErr = 0;

        // TODO Hooke für eigenen isEncashable routine
	    
        // standard voucher
        if (!$this->get_uid() ) {
                $encashableErr = 1; 
        } else if ($this->get_isEncashed()){
                if((int)$this->type != 1) {
                	$encashableErr = 2;	
                }
        } else {
            trace($this->get_isPercent(),0,'$this->isPercent()');
            trace(bcsub($this->get_amount(),$this->get_encashedAmount(),2 ),0,'bcsub($this->get_amount(),$this->get_encashedAmount(),2 )');
            if ($this->get_gsaUid()) { // gift voucher
                trace('Gift voucher');
                if (floatval(bcsub($this->get_amount(),$this->get_encashedAmount(),2 )) <= 0) {  
                    trace(bcsub($this->get_amount(),$this->get_encashedAmount(),2 ),0,'bcsub($this->get_amount(),$this->get_encashedAmount(),2 )');
                    $encashableErr = 3;
                } else if ($this->get_isPercent() == true) {
                    trace($this->get_isPercent(),0,'$this->isPercent()');
                	$encashableErr = 4;
                }
            } else { 
                if ($this->get_expiryDate() <= date('Y-m-d')) {
                    // discount voucher expired
                	$encashableErr = 5;
                }
            }
        } 
        
        // check for multi encashable 
        if((int)$this->type != 1  && $encashableErr == 0) {
        	$dataArr = tx_ptgsavoucher_voucherEncashAccessor::getInstance()->selectByUid($this->uid);
        	if(is_array($dataArr) && count($dataArr) > 0) $encashableErr = 2;
        }
	        	    
        // TODO check if article resp. Category Confinement
        #$article = new tx_ptgsashop_article();
        #$article->
        if (!$encashableErr) {
        	if ($this->get_articleConfinement() ) {
	            $inArticle = false;
        		$delColObj = $orderObj->get_deliveryCollObj();
	            $artConfinementArr = explode(',',$this->get_articleConfinement()); 
	            trace($artConfinementArr,0,'$artConfinementArr');
	            foreach ($delColObj as  $delivery) {
	                foreach($delivery->get_articleCollObj() as $article) {
			            trace($article->get_id(),0,'$article->get_id()');
			            if (in_array($article->get_id(),$artConfinementArr)) {
			            	$inArticle = true;
                            trace($inArticle,0,'$inArticle in if');
			            	break;
			            }
				        // TODO noch überprüfen
			            /*
			            if ($this->get_categoryConfinement() && $article->get_category()) {
				            $catConfinementArr = explode(',',$this->get_categoryConfinement()); 
				            if (in_array(explode(',',$catConfinementArr()))) {
				                $encashableErr = false;
				            }
				        }
		                */
		            }
	            }
                if (!$inArticle) {
                    trace($inArticle,0,'$inArticle');
                    // voucher is not valid for these articles 
                    $encashableErr = 6;
               }
        	}
        }

        return $encashableErr;
    }
        

    
    /**
     * sets the amount in encashed amount and looks if the encashing for this voucher is finished  
     *
     * @param   double  amount to encash        
     * @param   object  tx_ptgsashop_order         
     * @return  integer     EncashableError
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-15
     */
    public function encashAmount($amount, tx_ptgsashop_order $orderObj=null) {
        #$article = new tx_ptgsashop_article();
        if ($amount >0) {
            $this->encashedAmount = bcadd($amount, $this->encashedAmount,2);           
            if ($this->isPercent == true) {
            	$this->set_isEncashed(true);
            } else {
            	if (floatval(bcsub($this->amount,$this->encashedAmount,2))>0) {
            		$this->set_isEncashed(true);
            	}
            }
            
        }
        $this->storeSelf();
        return $amount;
    }
    
    
    /***************************************************************************
     *   PROPERTY GETTER/SETTER METHODS
     **************************************************************************/
    /**
     * Returns the property value
     *
     * @param   void
     * @return  integer property value
     * @since   2008-01-22
    */
    public function get_uid() {
        return $this->uid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-01-22
    */
    public function set_uid($uid) {
        $this->uid = intval($uid);
    }


        /**
     * Set the property value
     *
     * @param   string
     * @return  void
     * @since   2008-01-22
    */
    public function set_code($code) {
        $this->code = (string) $code;
    }

 
    /**
     * Returns the property value
     *
     * @param   void
     * @return  string  property value
     * @since   2008-01-22
    */
    public function get_code() {
        return $this->code;
    }

    /**
     * Returns the property value
     * 
     * @return string  property value
     * @author Daniel Lienert <lienert@punkt.de>
     * @since 30.06.2009
     */
    public function get_type() {
    	return $this->type;
    }
    
    /**
     * Set the property value
     * 
     * @param $voucherType
     * @return unknown_type
     * @author Daniel Lienert <lienert@punkt.de>
     * @since 30.06.2009
     */
    public function set_type($voucherType) {
    	$this->type = $voucherType;
    }
    
	/**
     * Returns the property value
     *
     * @param   void
     * @return  integer property value
     * @since   2008-07-23
    */
    public function get_pdfdocUid() {
        return $this->pdfdocUid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-07-23
    */
    public function set_pdfdocUid($pdfdocUid) {
        trace(($pdfdocUid),0,'($pdfdocUid)');
    	$this->pdfdocUid = intval($pdfdocUid);
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  integer property value
     * @since   2008-09-25
    */
    public function get_orderWrapperUid() {
        return $this->orderWrapperUid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-09-25
    */
    public function set_orderWrapperUid($orderWrapperUid) {
        $this->orderWrapperUid = intval($orderWrapperUid);
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  integer property value
     * @since   2008-01-22
    */
    public function get_gsaUid() {
        return $this->gsaUid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-01-22
    */
    public function set_gsaUid($gsaUid) {
        $this->gsaUid = intval($gsaUid);
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  string  property value
     * @since   2008-01-22
    */
    public function get_relatedDocNo() {
        return $this->relatedDocNo;
    }

    /**
     * Set the property value
     *
     * @param   string
     * @return  void
     * @since   2008-01-22
    */
    public function set_relatedDocNo($relatedDocNo) {
        $this->relatedDocNo = (string) $relatedDocNo;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  string  property value
     * @since   2008-01-22
    */
    public function get_categoryConfinement() {
        return $this->categoryConfinement;
    }

    /**
     * Set the property value
     *
     * @param   string
     * @return  void
     * @since   2008-01-22
    */
    public function set_categoryConfinement($categoryConfinement) {
        $this->categoryConfinement = (string) $categoryConfinement;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  string  property value
     * @since   2008-01-22
    */
    public function get_articleConfinement() {
        return $this->articleConfinement;
    }

    /**
     * Set the property value
     *
     * @param   string
     * @return  void
     * @since   2008-01-22
    */
    public function set_articleConfinement($articleConfinement) {
        $this->articleConfinement = (string) $articleConfinement;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  double  property value
     * @since   2008-01-22
    */
    public function get_amount() {
        return $this->amount;
    }

    /**
     * Set the property value
     *
     * @param   double
     * @return  void
     * @since   2008-01-22
    */
    public function set_amount($amount) {
        $this->amount = (double)$amount;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  boolean property value
     * @since   2008-01-22
    */
    public function get_isPercent() {
        return $this->isPercent;
    }

    /**
     * Set the property value
     *
     * @param   boolean
     * @return  void
     * @since   2008-01-25
    */
    public function set_isPercent($isPercent) {
        $this->isPercent = $isPercent ? true : false;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  boolean property value
     * @since   2008-01-25
    */
    public function get_oncePerOrder() {
        return $this->oncePerOrder;
    }

    /**
     * Set the property value
     *
     * @param   boolean
     * @return  void
     * @since   2008-01-25
    */
    public function set_oncePerOrder($oncePerOrder) {
        $this->oncePerOrder = $oncePerOrder ? true : false;
    }

    
    /**
     * Returns the property value
     *
     * @param   void
     * @return  boolean property value
     * @since   2008-10-09
    */
    public function get_isEncashed() {
        return $this->isEncashed;
    }

    /**
     * Set the property value
     *
     * @param   boolean
     * @return  void
     * @since   2008-10-09
    */
    public function set_isEncashed($isEncashed) {
    	$this->isEncashed = ($isEncashed && $this->type!=1) ? true : false;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  double  property value
     * @since   2008-01-22
    */
    public function get_encashedAmount() {
        return $this->encashedAmount;
    }

    /**
     * Set the property value
     *
     * @param   double
     * @return  void
     * @since   2008-01-22
    */
    public function set_encashedAmount($encashedAmount) {
        $this->encashedAmount = (double)$encashedAmount;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  string  property value
     * @since   2008-01-22
    */
    public function get_expiryDate() {
        return $this->expiryDate;
    }

    /**
     * Set the property value
     *
     * @param   string
     * @return  void
     * @since   2008-01-22
    */
    public function set_expiryDate($expiryDate) {
        $this->expiryDate = (string) $expiryDate;
    }



     
}


/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucher.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucher.php']);
}

?>