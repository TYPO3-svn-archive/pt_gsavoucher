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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_pdfdoc.php';
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucher.php';


require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_order.php';  // GSA Shop order class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapper.php';// GSA Shop order wrapper class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapperAccessor.php';  // GSA Shop database accessor class for order wrappers
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderPresentator.php';// GSA shop order presentator class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_user.php';  // // TYPO3 FE user class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_feCustomer.php';  // TYPO3 FE user class

//$trace=1;
/**
 * Plugin 'Plugin4' for the 'pt_gsavoucher' extension.
 *
 * @author	Dorit Rottner <rottner@punkt.de>
 * @package	TYPO3
 * @subpackage	tx_ptgsavoucher
 */
class tx_ptgsavoucher_pi4 extends tslib_pibase {
	var $prefixId      = 'tx_ptgsavoucher_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_ptgsavoucher_pi4.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'pt_gsavoucher';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$conf['voucherPath'] = 'fileadmin/vouchers/###GSAUID###_###VOUCHERCODE###.pdf';
		$conf['voucherPdfLL'] = 'EXT:pt_gsavoucher/res/voucherpdf_locallang.xml';
		$conf['templateVoucherPDF'] = 'EXT:pt_gsavoucher/res/smarty_tpl/voucherpdf.tpl.xml';
		$conf['backgroundPDF'] = 'EXT:pt_gsapdfdocs/res/smarty_tpl/background.pdf';
		
		trace('In main');
		
        $smartyConf = array();
        	$smartyConf['compile_dir'] = PATH_site.'typo3temp/smarty_compile';
        	$smartyConf['cache_dir'] = PATH_site.'typo3temp/smarty_cache';
        	t3lib_div::devLog('compileDir: '.$smartyConf['compile_dir'],'pt_gsavoucher', 1);

        	t3lib_div::devLog('generate voucher ', 'pt_gsavoucher', 1);
        	$feCustomerObj = new tx_ptgsauserreg_feCustomer($GLOBALS['TSFE']->fe_user->user['uid']);
        	$voucherObj = new tx_ptgsavoucher_voucher(25);
			trace($voucherObj,0,'$voucherObj');
        	$orderWrapperObj = new tx_ptgsashop_orderWrapper(2106);
        	$orderObj = $orderWrapperObj->get_orderObj();
        	trace($orderObj,0,'$orderObj');
        	trace($GLOBALS['TSFE']->lang,0,'lang');
        try {

	        
        	#$GLOBALS['TSFE']->lang = 'de';
        	$replace = array(
		        			'###GSAUID###' => $feCustomerObj->get_gsaCustomerObj()->get_gsauid(),
		        			'###DAY###' => strftime('%Y'),
			        		'###MONTH###' => strftime('%m'),
			        		'###YEAR###' => strftime('%d'),
		        			'###VOUCHERCODE###' => ereg_replace('[^a-zA-Z0-9._-]', '_', $voucherObj->get_code())
		        		);

		        		//generate PDF-Voucher
                    	t3lib_div::devLog('conf path: '.$conf['voucherPath'].' code: '.$voucherObj->get_code().' replace: '. $replace['###VOUCHERCODE###'], 'pt_gsavoucher', 1);
		        		$path = str_replace(array_keys($replace), array_values($replace), $conf['voucherPath']);
                    	t3lib_div::devLog('path: '.$path, 'pt_gsavoucher', 1);
                    	
                    	$pdfVoucher = new tx_ptgsavoucher_pdfdoc();
                    	
                    	$pdfVoucher->set_customerObj($feCustomerObj);
						t3lib_div::devLog('customer object set ', 'pt_gsavoucher', 1);
                    	$pdfVoucher->set_orderObj($orderObj);
						t3lib_div::devLog('order object set ', 'pt_gsavoucher', 1);
                    	$pdfVoucher->set_voucherObj($voucherObj);
						t3lib_div::devLog('voucher object set ', 'pt_gsavoucher', 1);
						$pdfVoucher->fillMarkerArray();
			            $pdfVoucher->set_xmlSmartyTemplate($conf['templateVoucherPDF']);
			            $pdfVoucher->set_languageFile($conf['voucherPdfLL']);
			            $pdfVoucher->createXml($smartyConf);
			            $pdfVoucher->renderPdf($path);
			            	
			            // save to database
						$pdfVoucher->set_orderWrapperUid($orderWrapperObj->get_uid());
						$pdfVoucher->storeSelf();
						trace($pdfVoucher,0,'$pdfVoucher');
                    	$voucherObj->set_pdfdocUid($pdfVoucher->get_uid());
                    	$voucherObj->storeSelf();
						
			$pdfDocument = tx_ptgsavoucher_voucherAccessor::selectVoucherDocumentByCode($voucherObj->get_code());
	        if ($pdfDocument['file']) {
	        	$url =  'index.php?eID=tx_ptgsavoucher_download&vocc='.urlencode($voucherObj->get_code());
	        } else {
	        	$url = '';
	        }

			$content='Gutschein <a href="'.$url.'">'.$voucherObj->get_code().'</a> <a href="'.$url.'&saved=1">speichern</a>';
        } catch (tx_pttools_exception $excObj) {
                // if an exception has been catched, handle it and overwrite plugin content with error message
                $excObj->handleException();
                $content = '<i>'.$excObj->__toString().'</i>';
        }
			
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/pi4/class.tx_ptgsavoucher_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/pi4/class.tx_ptgsavoucher_pi4.php']);
}

?>