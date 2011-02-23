<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Dorit Rottner <rottner@punkt.de>
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
 * Hooking class of the 'pt_gsavoucher' extension for hooks in tx_ptgsashop_orderPresentator
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-11-20
 */ 
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */



/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucher.php';
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherEncash.php';
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherEncashCollection.php';

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_paymentModifierCollection.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderPresentator.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class


/**
 * Class being included by pt_gsashop using hooks in tx_ptgsashop_orderProcessor
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-11-20
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_hooks_ptgsashop_orderProcessor {
    
    /**
     * Constants
     */
    
    const EXT_KEY       = 'pt_gsavoucher';               // (string) the extension key
    const LL_FILEPATH   = 'res/hooks/locallang.xml';     // (string) path to the locallang file to use within this class
    
    
    /**
     * Class constructor: sets the object's properties
     *
     * @param   void
     * @return  void     
     * @global  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-11-20
     */
    public function __construct() {
    }

	
    /**
     * This method is called by a hook in tx_ptgsashop_orderProcessor::fixFinalOrderHook 
     *
     * @param   array      params contains order Wrapper
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-11-20
     */
    public function fixFinalOrderHook(array $params /*, tx_ptgsashop_orderProcessor $pObj */) {
    	
    	$orderWrapperObj = $params['orderWrapperObj'];
    	tx_pttools_assert::isInstanceOf($orderWrapperObj,'tx_ptgsashop_orderWrapper',array('message'=>'No valid orderWrapper Object'));
        trace('[METHOD] '.__METHOD__);
        #$GLOBALS['trace'] = 1;
        if (TYPO3_DLOG) t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsavoucher', 1, $this->voucherConf);

        $orderObj =  $orderWrapperObj->get_orderObj();
        $paymentModifierCol = new tx_ptgsaaccounting_paymentModifierCollection($orderObj->get_orderArchiveId());
        $orderObj->set_paymentModifierCollObj($paymentModifierCol);
        $relatedDocNo = $orderWrapperObj->get_relatedDocNo();
        $gsaUid = $orderWrapperObj->get_customerId();
        // look for possible vouchers 
        if ($paymentModifierCol != NULL) {
            // There are vouchers for this order

        	foreach ($paymentModifierCol as $paymentModifier) {
        		if ($paymentModifier->get_addDataType() == 'voucher') {
        			$voucherEncash = unserialize($paymentModifier->get_addData());
                    $voucherEncash->finishEncash($orderWrapperObj, $relatedDocNo, $gsaUid);                
        		}
        	}
        }
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_orderProcessor.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_orderProcessor.php']);
}

?>
