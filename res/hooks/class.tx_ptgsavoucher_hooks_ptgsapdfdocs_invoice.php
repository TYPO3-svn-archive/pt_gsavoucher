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
 * Hooking class of the 'pt_gsavoucher' extension for hooks in tx_ptgpdfdocs_invoice
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-10-24
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
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_invoice.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class


/**
 * Class being included by pt_gsashop using hooks in tx_ptgpdfdocs_invoice
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-10-24
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_hooks_ptgsapdfdocs_invoice extends tx_ptgsapdfdocs_invoice {
    
    /**
     * Constants
     */
    
    const EXT_KEY       = 'pt_gsavoucher';               // (string) the extension key
    const LL_FILEPATH   = 'res/hooks/locallang.xml';     // (string) path to the locallang file to use within this class
	
    
    /**
     * This method is called by a hook in tx_ptgpdfdocs_invoice::fillMarkerArray(): initiate pt_gsavoucher specific stuff
     *
     * @param   object      tx_ptgsapdfdocs_invoice 
     * @param   array       markerArray
     * @return  array       changed markerArray 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-24
     */
    public function displayShoppingCart_MarkerArrayHook(tx_ptgsapdfdocs_invoice $invoice, $markerArray) {
        #$GLOBALS['trace'] = 1;
    	trace('[METHOD] '.__METHOD__);

        $voucherConf = tx_pttools_div::typoscriptRegistry('config.tx_ptgsavoucher.', NULL, 'pt_gsavoucher', 'tsConfigurationPid');
        $voucherEncashCollection = new tx_ptgsavoucher_voucherEncashCollection();
        $voucherEncashCollection->load($invoice->orderObj);
        t3lib_div::devLog(__METHOD__.' orderObj', 'pt_gsavoucher', 1, serialize($invoice->orderObj));
        
        if (count($voucherEncashCollection) >0) {
        	foreach ($voucherEncashCollection  as $voucherEncash) {
                $voucher = new tx_ptgsavoucher_voucher($voucherEncash->get_voucherUid());
            	$voucherEncashArr[$voucherEncash->get_voucherUid()]['voucher_code'] = $voucher->get_code();
                $voucherEncashArr[$voucherEncash->get_voucherUid()]['voucher_amount'] = tx_pttools_finance::getFormattedPriceString($voucherEncash->get_amount()); 
            }

            // Add additonal Table Snippets
            $smartyConf = array();
            $smartyConf['t3_languageFile'] = $voucherConf['languageFile'];
            if (!is_object($GLOBALS['TSFE'])) {
            	//TODO get it from conf
            	$smartyConf['t3_languageKey'] = 'de';
            }
            $smarty = new tx_pttools_smartyAdapter($this, $smartyConf);
            $smarty->left_delimiter = '<!--{';
            $smarty->right_delimiter = '}-->';
            
            $smarty->assign('cond_encashedVoucher',true);
            $smarty->assign('voucher_encashArr',$voucherEncashArr);
            
            $filePath = $smarty->getTplResFromTsRes($voucherConf['templateFileInvoice_additionalData']);
            $tableSnippets = array();
            $tableSnippets[] = $smarty->fetch($filePath);
            $markerArray['afterTableSnippets'] = $tableSnippets;
            trace($markerArray['afterTableSnippets'],0,'afterTableSnippets');
            $markerArray['cond_displayPaymentSumTotal'] = true;
            
            /* it seems not possible to get the paymentSumTotal as net price, because the paymentmodifiers are gross only
             * therefore we calculate the net price and taxes here from gross
             * TODO: implement net prices in payment modifiers
             */
            $shopConfigArr = tx_ptgsashop_lib::getGsaShopConfig();
            $taxCode = (string)$shopConfigArr['dispatchTaxCode'];
            $taxRate = tx_ptgsashop_lib::getTaxRate($taxCode);
            
            $paymentSumTotal =  $invoice->orderObj->getPaymentSumTotal();
            $paymentSumTotalNet = tx_pttools_finance::getNetPriceFromGross($paymentSumTotal, $taxRate);
            $paymentSumTotalTax = bcsub($paymentSumTotal, $paymentSumTotalNet, 4);
            $markerArray['orderPaymentSumTotal'] = sprintf("%9.2f", $paymentSumTotal);
            $markerArray['orderPaymentSumTotalNet'] = sprintf("%9.2f", $paymentSumTotalNet);
            $markerArray['orderPaymentSumTotalTax'] = sprintf("%9.2f", $paymentSumTotalTax);
        }
        return $markerArray;
        
    }
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsapdfdocs_invoice.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsapdfdocs_invoice.php']);
}

?>
