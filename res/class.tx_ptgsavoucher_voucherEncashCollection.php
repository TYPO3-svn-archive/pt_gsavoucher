<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Dorit Rottner (rottner@punkt.de)
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
 *
 * $Id$
 *
 * @author	Dorit Rottner <rottner@punkt.de>
 * @since   2008-10-14
 */ 
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */



/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherEncash.php';// extension specific voucherEncash class 
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherEncashAccessor.php';  // extension specific voucherEncash Accesor class

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_paymentModifierCollection.php';
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_paymentModifier.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_objectCollection.php'; // abstract object Collection class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';



/**
 * voucherEncash collection class
 *
 * @author	    Dorit Rottner <rottner@punkt.de>
 * @since       2008-10-14
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_voucherEncashCollection extends tx_pttools_objectCollection {
    
    /**
     * Properties
     */
    
    
    
	/***************************************************************************
     *   CONSTRUCTOR
     **************************************************************************/
     
    /**
     * Class constructor: creates a collection of voucherEncash objects. If no parameter is specified all voucherEncash records are given back
     *
     * @param   integer     (optional) Id of voucher 
     * @param   integer     (optional) Id of order Wraper
     * @param   boolean     (optional) flag if encashing done  
     * @return  void
 	 * @author	Dorit Rottner <rottner@punkt.de>
 	 * @since   2008-10-14
     */
    public function __construct($voucherUid=0,  $orderWrapperUid=0, $encashed=false) { 
    
        trace('***** Creating new '.__CLASS__.' object. *****');
        tx_pttools_assert::isValidUid($voucherUid, true, array('message' => 'No valid voucherUid: '.$voucherUid.'!'));
        tx_pttools_assert::isValidUid($orderWrapperUid, true, array('message' => 'No valid orderWraooerUid: '.$orderWrapperUid.'!'));
        
		if ($voucherUid >0 || $orderWrapperUid >0) {
            $dataIdArr = tx_ptgsavoucher_voucherEncashAccessor::getInstance()->getIdArr($voucherUid,$orderWrapperUid,$encashed);
			foreach ($dataIdArr as $id) {
				$this->addItem(new tx_ptgsavoucher_voucherEncash($id), $id);
			}
		}
        
    }   
    
    /***************************************************************************
     *   extended collection methods
     **************************************************************************/
    
    /**
     * Check if voucher is allready in Collection 
     *
     * @param   integer     Id of voucher 
     * @return  boolean     return true if voucher is allready in Collection else false
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-21
     */
    public function voucherInCollection($id) {
    	trace('[METHOD] '.__METHOD__);
    	
    	foreach ($this as $voucherEncash) {
    		trace($voucherEncash,0,'$voucherEncash');
            trace($id,0,'$id');
    		if ($voucherEncash->get_voucherUid() == $id) {
    			return true;
    		}
    	}
    	return false;
    	    
    }
 
    /**
     * Check if encash collection contains at least one discount voucher  
     *
     * @param   void    
     * @return  boolean     return true ifcollection contains at one discountVoucher else false
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-21
     */
    public function containsDiscountVoucher() {
        trace('[METHOD] '.__METHOD__);
        
        foreach ($this as $voucherEncash) {
            $voucher = new tx_ptgsavoucher_voucher($voucherEncash->get_voucherUid());
        	trace($voucher,0,'$voucher');
            if ($voucher->isDiscountVoucher()) {
                trace('Discount Voucher in Collection');
        		return true;
        	}
        }
        return false;
            
    }
    /**
     * loads the session voucher encash collection from paymentModifier and stores it to the browser session
     *
     * @param   object  tx_ptgsashop_order
     * @return  void
     * @global  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-17
     */
    public function load(tx_ptgsashop_order $orderObj) { 

        $paymentModifierCol = new tx_ptgsaaccounting_paymentModifierCollection($orderObj->get_orderArchiveId());
        $orderObj->set_paymentModifierCollObj($paymentModifierCol);
        if ($paymentModifierCol != NULL) {
            foreach ($paymentModifierCol as $paymentModifier) {
                if ($paymentModifier->get_addDataType() == 'voucher') {
                    $voucherEncash = unserialize($paymentModifier->get_addData());
                    $this->addItem($voucherEncash,$voucherEncash->get_voucherUid());
                }
            }
        }
    }
    
    /***************************************************************************
     *   GENERAL METHODS
     **************************************************************************/
        
    
    
    /***************************************************************************
     *   PROPERTY GETTER/SETTER METHODS
     **************************************************************************/
     

} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherEncashCollection.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherEncashCollection.php']);
}

?>
