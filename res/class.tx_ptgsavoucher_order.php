<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Fabrizio Branca (branca@punkt.de)
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

require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_pdfdoc.php';
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucher.php';


require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_order.php';  // GSA Shop order class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapper.php';// GSA Shop order wrapper class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapperAccessor.php';  // GSA Shop database accessor class for order wrappers
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderPresentator.php';// GSA shop order presentator class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_user.php';  // // TYPO3 FE user class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_feCustomer.php';  // TYPO3 FE user class



/**
 * Order Class for Voucher
 *
 * $Id$
 *  
 * @author	Dorit Rottner <rottner@punkt.de>
 * @since	2008-07-16
 */
class tx_ptgsavoucher_order  {
	
    const EXT_KEY       = 'pt_gsavoucher';               // (string) the extension key
	
	public static function processOrder(tx_ptgsashop_order $orderObj, tx_ptgsauserreg_feCustomer $feCustomerObj, $relatedDocNo, tx_ptgsashop_orderWrapper $orderWrapperObj) {
		
        	$conf = $GLOBALS['TSFE']->tmpl->setup['config.']['tx_ptgsavoucher.'];
			
        	t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsavoucher', 1, $conf);
        	$smartyConf = array();
        	$smartyConf['compile_dir'] = PATH_site.'typo3temp/smarty_compile';
        	$smartyConf['cache_dir'] = PATH_site.'typo3temp/smarty_cache';
        	t3lib_div::devLog('compileDir: '.$smartyConf['compile_dir'],'pt_gsavoucher', 1);
        	foreach ($orderObj->get_deliveryCollObj() as $delKey=>$delObj) {
            if ($delObj->get_articleCollObj()->count() > 0) {
                foreach ($delObj->get_articleCollObj() as $artKey=>$artObj) {
                    $artApplSpecUid = $artObj->get_artrelApplSpecUid();
					t3lib_div::devLog('id: '.$artObj->get_id().' ident: '.$artObj->get_artrelApplIdentifier().' uid: '.$artApplSpecUid, 'pt_gsavoucher', 1);
                    if (intval($artApplSpecUid) > 0 && $artObj->get_artrelApplIdentifier() == self::EXT_KEY) {
                        // Voucher Article: do special handling
						t3lib_div::devLog('generate voucher ', 'pt_gsavoucher', 1);
                    	$voucherObj = new tx_ptgsavoucher_voucher();
                    	$voucherObj->set_payedGsaUid($feCustomerObj->get_gsaCustomerObj()->get_gsauid());
                    	$voucherObj->set_payedRelatedDocNo($relatedDocNo);
                    	$voucherObj->set_amount($artObj->getItemSubtotal(0));
                    	$voucherObj->createCode();

                    	$replace = array(
		        			'###GSAUID###' => $feCustomerObj->get_gsaCustomerObj()->get_gsauid(),
		        			'###DAY###' => strftime('%Y'),
			        		'###MONTH###' => strftime('%m'),
			        		'###YEAR###' => strftime('%d'),
		        			'###VOUCHERCODE###' => ereg_replace('[^a-zA-Z0-9._-]', '_', $voucherObj->get_code())
		        		);

		        		//generate PDF-Voucher
                    	t3lib_div::devLog('conf path: '.$conf['voucherPath'].' code: '.$voucherObj->get_code().' replace: '. $replace['###VOUCHERCODE###'], 'pt_gsavoucher', 1);
		        		$path = PATH_site.str_replace(array_keys($replace), array_values($replace), $conf['voucherPath']);
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
                    	$voucherObj->set_pdfdocUid($pdfVoucher->get_uid());
                    	$voucherObj->storeSelf();
                    }
        		}
            }
        }
		//$order1Obj->get_deliveryCollObj();
	}
}

?>