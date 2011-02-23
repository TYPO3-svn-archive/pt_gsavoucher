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
 * @since   2008-10-14
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
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderPresentator.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class


/**
 * Class being included by pt_gsashop using hooks in tx_ptgsashop_orderPresentator
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-10-14
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_hooks_ptgsashop_orderPresentator extends tx_ptgsashop_orderPresentator {
    
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
     * @since   2008-10-14
     */
    public function __construct() {
    }

	
    /**
     * This method is called by a hook in tx_ptgsashop_orderPresentator::getPlaintextPresentation(): initiate pt_accounting specific stuff
     *
     * @param   object      current instance of parent object calling this hook
     * @param   array       markerArray
     * @return  array       changed markerArray 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-14
     */
    public function getPlaintextPresentation_MarkerArrayHook($pObj, $markerArray) {
        trace('[METHOD] '.__METHOD__);
        // look for possible vouchers 
        $llArray = tx_pttools_div::readLLfile(t3lib_extMgm::extPath(self::EXT_KEY).self::LL_FILEPATH); // get locallang data
        trace($llArray,0,'$llArray');
        
        $voucherConf = tx_pttools_div::typoscriptRegistry('config.tx_ptgsavoucher.', NULL, self::EXT_KEY, 'tsConfigurationPid');
        $voucherEncashCollection = new tx_ptgsavoucher_voucherEncashCollection();
        $voucherEncashCollection->load($pObj->get_orderObj());
        t3lib_div::devLog(__METHOD__.'orderObj', 'pt_gsavoucher', 1, serialize($pObj->get_orderObj()));
        trace($voucherEncashCollection,0,'$voucherEncashCollection');
        if (count($voucherEncashCollection) >0) {
        	foreach ($voucherEncashCollection  as $voucherEncash) {
                $voucher = new tx_ptgsavoucher_voucher($voucherEncash->get_voucherUid());
                $voucherString = str_replace('%s',$voucher->get_code(),tx_pttools_div::getLLL('fl_encashed_voucher', $llArray));
                #$padLength = 57 + strlen($voucherString) - mb_strlen($voucherString);
                $padLength = 58;
                $voucherEncashArr[$voucherEncash->get_voucherUid()]['voucher_code'] = str_pad($voucherString,$padLength,' ',STR_PAD_LEFT);
                $voucherEncashArr[$voucherEncash->get_voucherUid()]['voucher_amount'] = sprintf("%9.2f",round($voucherEncash->get_amount(),2)*-1); 
                trace(tx_pttools_finance::getFormattedPriceString($voucherEncash->get_amount()),0,'$voucherEncash->get_amount()');
        	}
            
            $paymentObj = $pObj->orderObj->get_paymentMethodObj();
            $markerArray['ll_overview_paymentNotice'] = tx_pttools_div::getLLL('overview_paymentNotice_'.$paymentObj->get_method(),$llArray);

            // Add additonal Billing Data
            $smartyConf = array();
            $smartyConf['t3_languageFile'] = $voucherConf['languageFile'];
            if (!is_object($GLOBALS['TSFE'])) {
                //TODO get it from conf
                $smartyConf['t3_languageKey'] = 'de';
            }
            $smarty = new tx_pttools_smartyAdapter($this, $smartyConf);
            $smarty->assign('cond_additionalPaymentModifierData',true);
            $smarty->assign('voucher_encashArr',$voucherEncashArr);
            trace($voucherEncashArr,0,'$voucherEncashArr');
            
            $filePath = $smarty->getTplResFromTsRes($voucherConf['templateFileFinalOrderMail_additionalData']);
            $markerArray['additionalPaymentModifierData'] = $smarty->fetch($filePath);
            trace($markerArray['additionalPaymentModifierData'],0,'additionalPaymentModifierData');
            $smarty->assign('cond_additionalPaymentModifierData',false);
            $smarty->assign('cond_additionalBillingData',true);
            $markerArray['additionalBillingData'] = $smarty->fetch($filePath);
            t3lib_div::devLog('In "'.__METHOD__.'"', 'pt_gsavoucher', 1, $markerArray);
        }
        $GLOBALS['trace'] = 0;
        return $markerArray;
        
    }
    
    /**
     * This method is called by a hook in tx_ptgsashop_orderPresentator::getPlaintextPresentation(): initiate pt_voucher specific stuff for Delivery
     *
     * @param   object     tx_ptgsashop_orderPresentator current instance of parent object calling this hook
     * @param   object      tx_ptgsashop_delivery  
     * @param   array       markerArray for delivery
     * @return  array       changed markerArray 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-28
     */
    public function getPlaintextPresentation_returnDeliveryMarkersHook(tx_ptgsashop_orderPresentator $pObj,  tx_ptgsashop_delivery $delObj, $delArr) {
        trace('[METHOD] '.__METHOD__);
        $voucherConf = tx_pttools_div::typoscriptRegistry('config.tx_ptgsavoucher.', NULL, self::EXT_KEY, 'tsConfigurationPid');
        $voucherEncashCollection = new tx_ptgsavoucher_voucherEncashCollection();
        $voucherEncashCollection->load($pObj->get_orderObj());
        // There are vouchers for this order
        if (count($voucherEncashCollection) >0) {
            $smartyConf = array();
            $smartyConf['t3_languageFile'] = t3lib_extMgm::extPath(self::EXT_KEY).self::LL_FILEPATH;
            if (!is_object($GLOBALS['TSFE'])) {
                //TODO get it from conf
                $smartyConf['t3_languageKey'] = 'de';
            }
            $smarty = new tx_pttools_smartyAdapter($this, $smartyConf);
            $smarty->assign('cond_additionalDeliveryData',true);
            $filePath = $smarty->getTplResFromTsRes($voucherConf['templateFileFinalOrderMail_additionalData']);
            $delArr['additionalDeliveryData'] = $smarty->fetch($filePath);
            trace($delArr,0,'additional Delivery Data');
        }
        return $delArr;
    }
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_orderPresentator.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_orderPresentator.php']);
}

?>
