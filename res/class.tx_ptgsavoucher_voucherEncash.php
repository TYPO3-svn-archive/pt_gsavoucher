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
 * Class for voucher encash objects in the pt_gsavoucher extension
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-10-09
 */
  
 /**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */
  
/**
 * Inclusion of extension specific libraries
 */
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
 * @since       2008-10-09
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_voucherEncash  {
    
    /**
     * Constants
     */

    const EXTKEY = 'tx_ptgsavoucher';
                                        // well known payment methods from GSA
    
    /**
     * Properties
     */

    protected $uid;                     // integer  unique Id of voucherEncash Record
    protected $voucherUid;              // integer  voucher Id 
    protected $orderWrapperUid;         // integer  Id of the Order Wrapper Object in the GSA shop extension   
    protected $gsaUid;                  // integer  Id of the customer who is encashing the voucher
    protected $relatedDocNo;            // string   Docmunent Number of the GSA Record in table FSCHRIFT who is encashing the voucher.
    protected $articleUid;              // integer  Id of Postition, if is an position in ERP Database
    protected $discountPercent;         // double   Discount in percent this value is 0 if it an gift voucher 
    protected $amount;                  // double   encashed amount for this voucher and order  
    protected $encashedDate;            // string   Date of encashing for the voucher
    protected $voucherObj;              // object  voucher for which encashig is done   
    
    
    /**
     * Class constructor - fills object's properties 
     *
     * @param   integer     (optional) ID of the voucher Record. Set to 0 if you want to use the 2nd param.
     * @param   tx_ptgsavoucher  (optional) voucherObj in this class
     * @return  void   
     * @throws  tx_pttools_exception   if the first param is not numeric  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
     */
    public function __construct($voucherEncashId=0, tx_ptgsavoucher_voucher $voucherObj) {
    
        trace('***** Creating new '.__CLASS__.' object. *****');
        
        tx_pttools_assert::isValidUid($voucherEncashId,true,array('message'=>'Invalid Voucher Encash Id'));
        
        // if a customer record ID is given, retrieve customer array from database accessor (and overwrite 2nd param)
        if ($voucherEncashId  > 0) {
            $dataArr = tx_ptgsavoucher_voucherEncashAccessor::getInstance()->selectByUid($voucherEncashId);
        }
        $this->setFromGivenArray($dataArr);
        if ($voucherObj!= NULL) {
            $this->set_voucherUid($voucherObj->get_uid());
            $this->set_voucherObj($voucherObj);
        }
        
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
     * @since   2008-10-09
     */
    protected function setFromGivenArray($dataArr) {
        
        foreach (get_class_vars( __CLASS__ ) as $propertyname => $pvalue) {
            if (isset($dataArr[$propertyname])) {
                $setter = 'set_'.$propertyname;
                $this->$setter($dataArr[$propertyname]);
            }
        }
    }
    
    /**
     * returns array with data from all properties
     *
     * @param   void
     * @return  array   array with data from all properties        
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
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
     * @since   2008-10-09
     */
    public function storeSelf() {
        trace('[METHOD] '.__METHOD__);
        $dataArray = $this->getDataArray();
        $this->set_uid(tx_ptgsavoucher_voucherEncashAccessor::getInstance()->storeData($dataArray));
    }

    
    /**
     * Deletes voucher encash data in TYPO3 DB
     *
     * @param   void        
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-14
     */
    public function deleteSelf() {
        trace('[METHOD] '.__METHOD__);
        tx_ptgsavoucher_voucherEncashAccessor::getInstance()->deleteData($this->get_uid());
        

    }

    /**
     * check if encash is discount of the whole order
     *
     * @param   void        
     * @return  boolean true if the encash is an discount of the whole order
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-23
     */
    public function isDiscountTotal() {
        trace('[METHOD] '.__METHOD__);
        $this->voucherObj = $this->get_voucherObj(); 
        trace($this,0,'voucher Encash in isDiscountTotal');
        if (!$this->voucherObj->get_gsaUid() && $this->voucherObj->get_articleConfinement()=='') {
        	return true;
        } else {
        	return false;
        }
    }
    
    /**
     * Finish encash for this Voucher Part
     *
     * @param   object  tx_ptgsashop_orderWrapper        
     * @param   string  related Document Number        
     * @param   integer uid of the GSA Customer        
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-15
     */
    public function finishEncash(tx_ptgsashop_orderWrapper $orderWrapper, $relatedDocNo, $gsaUid) {
        trace('[METHOD] '.__METHOD__);

        $this->set_gsaUid($gsaUid);
        $this->set_relatedDocNo($relatedDocNo);
        $this->set_encashedDate(date('Y-m-d'));
        
        $this->set_orderWrapperUid($orderWrapper->get_uid());
        $this->storeSelf();
        
        if ($this->voucherObj->isDiscountVoucher()) {
            $this->voucherObj->set_isEncashed(true);
            $this->voucherObj->set_encashedAmount($this->get_amount());        	
        } else {
            $this->voucherObj->set_encashedAmount(bcadd($this->voucherObj->get_encashedAmount,$this->get_amount(),2));
            if ($this->voucherObj->get_encashedAmount() >= $this->voucherObj->get_amount()) {
            	$this->voucherObj->set_isEncashed(true);
            }
        }
        $this->voucherObj->storeSelf();
    }
    

    /**
     * gets Amount for this voucherObj which will be encashed. It there are more than one orticle which is encashable, take maximum  
     *
     * @param   object  tx_ptgsashop_order         
     * @return  double  amount of encash
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
     */
    public function getEncashableAmount(tx_ptgsashop_order $orderObj=null) {
        #$article = new tx_ptgsashop_article();
        
        if ($this->voucherObj->getEncashable($orderObj,$encashedAmount)) {
            trace('not isEncashable');
            return 0;
        }

        $articleUid = 0;
        if ($this->voucherObj->get_isPercent() == true) {
            $amount = 0;
            if ($this->voucherObj->get_articleConfinement()) {
                $artConfinementArr = explode(',',$this->voucherObj->get_articleConfinement()); 
            	$delColObj = $orderObj->get_deliveryCollObj();
                foreach ($delColObj as $delivery) {
            	    foreach($delivery->get_articleCollObj() as $article) {
                        trace('articleConfinement'); 
                        if (in_array($article->get_id(),$artConfinementArr) && $article->getDisplayPrice($orderObj->get_isNet()) > $amount) {
	                        $amount = $article->getDisplayPrice(0);
	                        $articleUid=$article->get_id();
                        }
                    } 
                }
            } else {
                 trace('in else'); 
                 $amount = $orderObj->getArticleSumTotal(0);
            }
            trace($amount,0,'$amount');
            $amount = bcmul(bcdiv($amount,100,6),$this->voucherObj->get_amount(),2);
            $percent = $this->voucherObj->get_amount();
        } else if (!$this->voucherObj->get_gsauid()){
        	// No gift voucher
        	if ($this->voucherObj->get_articleConfinement()) {
        		$artConfinementArr = explode(',',$this->voucherObj->get_articleConfinement()); 
                $delColObj = $orderObj->get_deliveryCollObj();
                foreach ($delColObj as $delivery) {
                    foreach($delivery->get_articleCollObj() as $article) {
                        trace('articleConfinement'); 
                        if (in_array($article->get_id(),$artConfinementArr) && $article->getDisplayPrice($orderObj->get_isNet()) > $amount) {
                            $amount = $this->voucherObj->get_amount(); 
                        	$articleUid=$article->get_id();
                            $articleAmount = $article->getDisplayPrice(0);
                            break;
                        }
                    } 
                }
                if ($articleAmount) {
                	$percent =  round(bcdiv(bcmul(100,$amount,4),$articleAmount,6),4);
                }
        	} else {
                $amount = $this->voucherObj->get_amount();
                $totalOrderAmount = $orderObj->getArticleSumTotal(0);
                if ($amount > $totalOrderAmount) {
                    $amount = $totalOrderAmount;
                }
                $percent = round(bcdiv(bcmul(100,$amount,4),$orderObj->getArticleSumTotal(0),6),4);	
        	}
            
        } else {
        	$amount = bcsub($this->voucherObj->get_encashedAmount(),$this->voucherObj->get_amount(),2);
        	
        }
        trace($amount,0,'encashable Amount');
        $this->set_amount($amount);
        $this->set_articleUid($articleUid );
        $this->set_discountPercent($percent);
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
     * @since   2008-10-09
    */
    public function get_uid() {
        return $this->uid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-10-09
    */
    public function set_uid($uid) {
        $this->uid = intval($uid);
    }



	/**
     * Returns the property value
     *
     * @param   void
     * @return  integer property value
     * @since   2008-10-09
    */
    public function get_voucherUid() {
        return $this->voucherUid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-10-09
    */
    public function set_voucherUid($voucherUid) {
        trace(($voucherUid),0,'($voucherUid)');
    	$this->voucherUid = intval($voucherUid);
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
     * @since   2008-10-09
    */
    public function get_gsaUid() {
        return $this->gsaUid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-10-09
    */
    public function set_gsaUid($gsaUid) {
        $this->gsaUid = intval($gsaUid);
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  string  property value
     * @since   2008-10-09
    */
    public function get_relatedDocNo() {
        return $this->relatedDocNo;
    }

    /**
     * Set the property value
     *
     * @param   string
     * @return  void
     * @since   2008-10-09
    */
    public function set_relatedDocNo($relatedDocNo) {
        $this->relatedDocNo = (string) $relatedDocNo;
    }



    /**
     * Returns the property value
     *
     * @param   void
     * @return  integer property value
     * @since   2008-10-16
    */
    public function get_articleUid() {
        return $this->articleUid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-10-16
    */
    public function set_articleUid($articleUid) {
        $this->articleUid = intval($articleUid);
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  double  property value
     * @since   2008-10-09
    */
    public function get_amount() {
        return $this->amount;
    }

    /**
     * Set the property value
     *
     * @param   double
     * @return  void
     * @since   2008-10-09
    */
    public function set_amount($amount) {
        $this->amount = (double)$amount;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  double  property value
     * @since   2008-10-22
    */
    public function get_discountPercent() {
        trace($this->discountPercent,0,'$this->discountPercent');
    	return $this->discountPercent;
    }

    
    /**
     * Set the property value
     *
     * @param   double
     * @return  void
     * @since   2008-10-09
    */
    public function set_discountPercent($discountPercent) {
        $this->discountPercent = (double)$discountPercent;
    }


    /**
     * Returns the property value
     *
     * @param   void
     * @return  string  property value
     * @since   2008-10-09
    */
    public function get_encashedDate() {
        return $this->encashedDate;
    }

    /**
     * Set the property value
     *
     * @param   string
     * @return  void
     * @since   2008-10-09
    */
    public function set_encashedDate($encashedDate) {
        $this->encashedDate = (string) $encashedDate;
    }

    /**
     * Set the property value
     *
     * @param   tx_ptgsavoucher_voucher
     * @return  void
     * @since   2008-10-23
    */
    public function set_voucherObj(tx_ptgsavoucher_voucher $voucherObj) {
        $this->voucherObj = $voucherObj;
    }
    
    /**
     * Get the property value
     *
     * @param   boolean (optional) flag for lazyLoading, default true 
     * @return  tx_ptgsavoucher_voucher
     * @since   2008-10-23
    */
    public function get_voucherObj($lazyLoding = true) {
        trace('[METHOD] '.__METHOD__);
        
        if ($lazyLoding==false && !is_null($this->voucherObj)) {
        	unset($this->voucherObj); 
        }
        if (is_null($this->voucherObj) && $this->voucherUid) {
        	$this->voucherObj = new tx_ptgsavoucher_voucher($this->voucherUid);
        }
        return $this->voucherObj;
    }
   
     
}


/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherEncash.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherEncash.php']);
}

?>