<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Dorit Rottner <rottner@punkt.de>
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
 * Hooking class of the 'pt_gsavoucher' extension for hooks in tx_ptgsashop_pi3
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-07-15
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
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_sessionVoucherEncashCollection.php';

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_paymentModifierCollection.php';
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_paymentModifier.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'pi3/class.tx_ptgsashop_pi3.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php'; 
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class


/**
 * Class being included by pt_gsavoucher using hooks in tx_ptgsashop_pi3
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-07-15
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_hooks_ptgsashop_pi3 extends tx_ptgsashop_pi3 {
    
    /**
     * Constants
     */
    
	const EXT_KEY       = 'pt_gsavoucher';               // (string) the extension key
    const LL_FILEPATH   = 'res/hooks/locallang.xml';     // (string) path to the locallang file to use within this class
    const SESSION_KEY_ERROR = 'pt_gsavoucher_hooks_ptgsashop_pi3'; // SESSION KEY for posiible Error Code
    
    protected $shopConf;         // Configuration Array for GSA shop
    protected $voucherConf;      // Configuration Array for GSA voucher
    
    /**
     * Class constructor: sets the object's properties
     *
     * @param   void
     * @return  void     
     * @global  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-07-15
     */
    public function __construct() {
        trace('[METHOD] '.__METHOD__);
        $this->shopConf = tx_ptgsashop_lib::getGsaShopConfig(); 
        $this->voucherConf = tx_pttools_div::typoscriptRegistry('config.tx_ptgsavoucher.', NULL, self::EXT_KEY, 'tsConfigurationPid');
        tx_pttools_assert::isNotEmptyArray($this->voucherConf, array('message' => 'No configuration found under "config.tx_ptgsavoucher."'));
        
    }

    /**
     * This method is called by a hook in tx_ptgsashop_pi3::mainControllerHook: initiate pt_gsavoucher
     *
     * @param   object      current instance of parent object calling this hook
     * @return  string      content of the plugin        
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-25
     */
    public function mainControllerHook($pObj) {
        trace('[METHOD] '.__METHOD__);
        #$GLOBALS['trace'] = 1;
        trace($pObj->piVars,0,'piVars');
        t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsavoucher', 1, $this->voucherConf);
        $voucherEncashCollection = tx_pttools_sessionStorageAdapter::getInstance()->read(tx_ptgsavoucher_sessionVoucherEncashCollection::SESSION_KEY_NAME);
        if ($voucherEncashCollection == NULL) {
            $voucherEncashCollection= tx_ptgsavoucher_sessionVoucherEncashCollection::getInstance();
        }
        
        //$voucherEncashCollection->clearItems();
        
        if($pObj->piVars['voucher_code']) {
            $voucher = new tx_ptgsavoucher_voucher(0,(string)$pObj->piVars['voucher_code']);
            // There could be only one discount voucher per order
            if ($voucherEncashCollection->voucherInCollection($voucher->get_uid())) {
            	
            	if($voucher->get_type() != 1) {
            		$errorCode = 2;  // only one discount voucher per order
            	}
            	
            }
            
            if ($voucher->isDiscountVoucher() && $voucherEncashCollection->containsDiscountVoucher()) { 
	            $errorCode = 7; // Error that alleady used for this order
	        } else {
                $errorCode = $voucher->getEncashable($pObj->orderObj);
            }
            
            trace($errorCode,0,'$error');
            if (!$errorCode) {
                $voucherEncash = new tx_ptgsavoucher_voucherEncash(0,$voucher);
                $amount = $voucherEncash->getEncashableAmount($pObj->orderObj);
                trace($voucherEncash,0,'$voucherEncash');
                trace($amount,0,'$amount');
                t3lib_div::devLog(__METHOD__.': Amount: $maount', 'pt_gsavoucher', 1);
                if ($amount >0) {
                	$voucherEncashCollection->addItem($voucherEncash,$voucherEncash->get_voucherUid());
                    $voucherEncashCollection->store();
			        $paymentModifierColl = $pObj->orderObj->get_paymentModifierCollObj();
			        if ($paymentModifierColl == NULL) {
			            $paymentModifierColl = new tx_ptgsaaccounting_paymentModifierCollection();
			        }
                    $paymentModifier = new tx_ptgsaaccounting_paymentModifier();
			        $paymentModifier->set_value($amount);
                    $paymentModifier->set_addDataType('voucher');
                    $paymentModifier->set_addData(serialize($voucherEncash));
                    $paymentModifierColl->addItem($paymentModifier);
                    $pObj->orderObj->set_paymentModifierCollObj($paymentModifierColl);
                    
                }
            }
        }

        if ($errorCode) {
            tx_pttools_sessionStorageAdapter::getInstance()->store(self::SESSION_KEY_ERROR, $errorCode);
        } else {
            tx_pttools_sessionStorageAdapter::getInstance()->store(self::SESSION_KEY_ERROR, NULL);
        }
        return $pObj->exec_defaultAction();
    }

    /**
     * This method is called by a hook in tx_ptgsashop_pi3::processOrderOverview_checkoutHook: initiate pt_gsavoucher
     *
     * @param   object      current instance of parent object calling this hook
     * @return  void        
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-25
     */
    public function processOrderOverview_checkoutHook($pObj) {
        trace('[METHOD] '.__METHOD__);
    	// proof existent Collection
        if (TYPO3_DLOG) t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsavoucher', 1);
        $voucherEncashCollection = tx_pttools_sessionStorageAdapter::getInstance()->read(tx_ptgsavoucher_sessionVoucherEncashCollection::SESSION_KEY_NAME);
        if ($voucherEncashCollection == NULL) {
            $voucherEncashCollection= tx_ptgsavoucher_sessionVoucherEncashCollection::getInstance();
        }
    	$amountTotal = 0;
        $paymentModifierColl = $pObj->orderObj->get_paymentModifierCollObj();
        if ($paymentModifierColl == NULL) {
        	$paymentModifierColl = new tx_ptgsaaccounting_paymentModifierCollection();
        } else {
            // TODO clear it in accounting
        	$paymentModifierColl ->clearItems();
        }
        
        $totalOrderAmount = $pObj->orderObj->getArticleSumTotal(0);
        
        trace($totalOrderAmount,0,'$totalOrderAmount');
        // This has to be done because cart could be changed
        foreach ($voucherEncashCollection as $voucherEncash) {
            if (TYPO3_DLOG) t3lib_div::devLog('processOrderOverview_checkoutHook ','pt_gsavoucher', 1);
        	$voucher = $voucherEncash->get_voucherObj(false);
            if (!$voucher->get_isEncashed() == true) {
            	$errorCode = $voucher->getEncashable($pObj->orderObj);
            } else {
            	$errorCode = 0;
            }
        	if (!$errorCode && !$voucher->get_isEncashed()) {
            	
            	$amount = $voucherEncash->getEncashableAmount($pObj->orderObj, $voucherEncash);
                #$orderObj = new tx_ptgsashop_order();
            	
                    #$GLOBALS['trace']=1; 
            	// Look if total amount to encash is not greater than orderSumTotal
            	if ($amount > 0 &&  bcadd($amountTotal,$amount,2) > $totalOrderAmount) {
                	$amount = bcsub($totalOrderAmount,$amountTotal,2);                	
                }
                if ($amount != $voucherEncash->get_amount()){
                    $voucherEncash->set_amount($amount);
                }
                if ($amount >0 ) {
                	$amountTotal = bcadd($amountTotal,$amount,2);
                    
                	$paymentModifier = new tx_ptgsaaccounting_paymentModifier();
	                $paymentModifier->set_value($amount);
	                $paymentModifier->set_addDataType('voucher');
	                $paymentModifier->set_addData(serialize($voucherEncash));
	                $paymentModifierColl->addItem($paymentModifier);
                } else {
                	$voucherEncashCollection->deleteItem($voucherEncash->get_voucherUid());
	            }
            } else {
            	$voucherEncashCollection->deleteItem($voucherEncash->get_voucherUid());
            }
        }
	    $pObj->orderObj->set_paymentModifierCollObj($paymentModifierColl);
        
        if (count($voucherEncashCollection)>0) {
            $voucherEncashCollection->store();
        } else {
            $voucherEncashCollection->delete();
        }	
        trace($errorCode,0,'$errorCode'); 
        if ($errorCode) {
            tx_pttools_sessionStorageAdapter::getInstance()->store(self::SESSION_KEY_ERROR, $errorCode);
        } else {
            tx_pttools_sessionStorageAdapter::getInstance()->store(self::SESSION_KEY_ERROR, NULL);
        }
        return;
               
    }

    /**
     * This method is called by a hook in tx_ptgsashop_pi3::displayOrderOverview(): initiate pt_voucher specific stuff
     *
     * @param   object      current instance of parent object calling this hook
     * @param   array       markerArray
     * @return  array       changed markerArray 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-25
     */
    public function displayOrderOverview_MarkerArrayHook($pObj, $markerArray) {
    	trace('[METHOD] '.__METHOD__);
        trace($this->shopConf,0,'shopConfigArr');
        if (TYPO3_DLOG) t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsavoucher', 1, $this->voucherConf);
        $errorCode = tx_pttools_sessionStorageAdapter::getInstance()->read(self::SESSION_KEY_ERROR);
        trace($errorCode,0,'$errorCode');
        $voucherEncashCollection = tx_pttools_sessionStorageAdapter::getInstance()->read(tx_ptgsavoucher_sessionVoucherEncashCollection::SESSION_KEY_NAME);
        if ($voucherEncashCollection == NULL) {
        	$voucherEncashCollection= tx_ptgsavoucher_sessionVoucherEncashCollection::getInstance();
        }
        $llArray = tx_pttools_div::readLLfile(t3lib_extMgm::extPath(self::EXT_KEY).self::LL_FILEPATH); // get locallang data
        trace($llArray,0,'$llArray');
        trace($pObj->conf,0,'$pObj->conf');
        #    $orderWrapperObj = new tx_ptgsashop_orderWrapper(0, $pObj->orderObj->get_orderArchiveId());
        $tmpMarkerArray['faction_orderPage'] = $pObj->pi_getPageLink($this->shopConf['orderPage']);
        $tmpMarkerArray['fname_voucher_code'] = $pObj->prefixId.'[voucher_code]';

        //get MessageBox if there is an error         
        if($errorCode) {
            if ($markerArray['cond_displayMsgBox'] != true) {
                $markerArray['cond_displayMsgBox'] = true;
                $msgBox = new tx_pttools_msgBox('error',tx_pttools_div::getLLL('msg_error_'.$errorCode, $llArray));
                $markerArray['msgBox'] = $msgBox;
            }
        	
        }
        
        // There are vouchers for this order
        $smartyConf = array();
        $smartyConf['t3_languageFile'] = $this->voucherConf['languageFile'];
        if (count($voucherEncashCollection) > 0) {
        	foreach ($voucherEncashCollection  as $voucherEncash) {
	            $voucher = new tx_ptgsavoucher_voucher($voucherEncash->get_voucherUid());
                $voucherEncashArr[$voucherEncash->get_voucherUid()]['voucher_code'] = $voucher->get_code();
                $voucherEncashArr[$voucherEncash->get_voucherUid()]['voucher_amount'] = tx_pttools_finance::getFormattedPriceString($voucherEncash->get_amount(),$pObj->conf['currencyCode']); 
	        }	
            $paymentObj = $pObj->orderObj->get_paymentMethodObj();
            //get additionalPaymentModifierData
            $markerArray['ll_overview_paymentNotice'] = tx_pttools_div::getLLL('overview_paymentNotice_'.$paymentObj->get_method(),$llArray);
            $smarty = new tx_pttools_smartyAdapter($this, $smartyConf);
            $smarty->assign('cond_encashedVoucher',true);
            $smarty->assign('cond_encashVoucher',false);
            $smarty->assign('voucher_encashArr',$voucherEncashArr);
            $filePath = $smarty->getTplResFromTsRes($this->voucherConf['templateFileOrderOverview_additionalData']);
            $markerArray['additionalPaymentModifierData'] = $smarty->fetch($filePath);
        }

        if (TYPO3_DLOG) t3lib_div::devLog(__METHOD__.': encashArray', 'pt_gsavoucher', 1, $voucherEncashArr);
        // get AdditionalData Encash Voucher
        $tmpMarkerArray['cond_encashedVoucher'] = false;
        $tmpMarkerArray['cond_encashVoucher'] = true;
        $smarty = new tx_pttools_smartyAdapter($this, $smartyConf);
        foreach ($tmpMarkerArray as $markerKey=>$markerValue) {
            $smarty->assign($markerKey, $markerValue);
        }
        
        $filePath = $smarty->getTplResFromTsRes($this->voucherConf['templateFileOrderOverview_additionalData']);
        $markerArray['additionalBillingData'] = $smarty->fetch($filePath);
        return $markerArray;
    }
    
    
    /**
     * This method is called by a hook in tx_ptgsashop_pi3::displayOrderOverview(): initiate pt_voucher specific stuff for Delivery
     *
     * @param   object      tx_ptgsashop_pi3 current instance of parent object calling this hook
     * @param   object      tx_ptgsashop_delivery  
     * @param   array       markerArray for delivery
     * @return  array       changed markerArray 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-16
     */
    public function displayOrderOverview_returnDeliveryMarkersHook(tx_ptgsashop_pi3 $pObj,  tx_ptgsashop_delivery $delObj, $delArr) {
    	trace('[METHOD] '.__METHOD__);
        if (TYPO3_DLOG) t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsavoucher', 1, $this->voucherConf);
    	$voucherEncashCollection = tx_pttools_sessionStorageAdapter::getInstance()->read(tx_ptgsavoucher_sessionVoucherEncashCollection::SESSION_KEY_NAME);
        if ($voucherEncashCollection == NULL) {
            $voucherEncashCollection= tx_ptgsavoucher_sessionVoucherEncashCollection::getInstance();
        }
    	// There are vouchers for this order
        if (count($voucherEncashCollection) >0) {
            $smartyConf = array();
            $smartyConf['t3_languageFile'] = $this->voucherConf['languageFile'];
            $smarty = new tx_pttools_smartyAdapter($this, $smartyConf);
            $smarty->assign('cond_additionalDeliveryData',true);
            $filePath = $smarty->getTplResFromTsRes($this->voucherConf['templateFileOrderOverview_additionalData']);
            $delArr['additionalDeliveryData'] = $smarty->fetch($filePath);
            trace($delArr,0,'additional Delivery Data');
        }
        return $delArr;
    }
    
    /**
     * This method is called by a hook in tx_ptgsashop_pi3::processOrderSubmission(): initiate pt_accounting specific stuff
     *
     * @param   object      current instance of parent object calling this hook
     * @return  void        GSA ERP doc number (dt: "Vorgangsnumer)
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-07-15
     */
	public function processOrderSubmission_fixFinalOrderHook ($pObj, $relatedErpDocNo, tx_ptgsashop_orderWrapper $orderWrapperObj) {
        trace('[METHOD] '.__METHOD__);
        if (TYPO3_DLOG) t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsavoucher', 1, $this->voucherConf);
            	
        // look for possible vouchers 
        $voucherEncashCollection = tx_pttools_sessionStorageAdapter::getInstance()->read(tx_ptgsavoucher_sessionVoucherEncashCollection::SESSION_KEY_NAME);
        // There are vouchers for this order
        if ($voucherEncashCollection != NULL) {
            foreach ($voucherEncashCollection  as $voucherEncash) {
                $voucherEncash->finishEncash($orderWrapperObj,  $relatedErpDocNo, $pObj->customerObj->get_gsaMasterAddressId());                
            }
            $voucherEncashCollection->delete();
        }
        /* TODO uncomment this later
        $smartyConf = array();
        $smartyConf['compile_dir'] = PATH_site.'typo3temp/smarty_compile';
        $smartyConf['cache_dir'] = PATH_site.'typo3temp/smarty_cache';
        t3lib_div::devLog('compileDir: '.$smartyConf['compile_dir'],'pt_gsavoucher', 1);
        foreach ($pObj->orderObj->get_deliveryCollObj() as $delKey=>$delObj) {
	        if ($delObj->get_articleCollObj()->count() > 0) {
	            foreach ($delObj->get_articleCollObj() as $artKey=>$artObj) {
	                $artApplSpecUid = $artObj->get_artrelApplSpecUid();
	                t3lib_div::devLog('id: '.$artObj->get_id().' ident: '.$artObj->get_artrelApplIdentifier().' uid: '.$artApplSpecUid, 'pt_gsavoucher', 1);
	                if (intval($artApplSpecUid) > 0 && $artObj->get_artrelApplIdentifier() == self::EXT_KEY) {
	                    // Voucher Article: do special handling
	                    t3lib_div::devLog('generate voucher ', 'pt_gsavoucher', 1);
	                    $voucherObj = new tx_ptgsavoucher_voucher();
	
	                    $voucherObj->set_gsaUid($feCustomerObj->get_gsaCustomerObj()->get_gsauid());
	                    $voucherObj->set_relatedDocNo($relatedErpDocNo);
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
	                    t3lib_div::devLog('conf path: '.$this->conf['voucherPath'].' code: '.$voucherObj->get_code().' replace: '. $replace['###VOUCHERCODE###'], 'pt_gsavoucher', 1);
	                    $path = PATH_site.str_replace(array_keys($replace), array_values($replace), $this->conf['voucherPath']);
	                    t3lib_div::devLog('path: '.$path, 'pt_gsavoucher', 1);
	                        
	                    $pdfVoucher = new tx_ptgsavoucher_pdfdoc();
	                        
	                    $pdfVoucher->set_customerObj($feCustomerObj);
	                    t3lib_div::devLog('customer object set ', 'pt_gsavoucher', 1);
	                    $pdfVoucher->set_orderObj($pObj->orderObj);
	                    t3lib_div::devLog('order object set ', 'pt_gsavoucher', 1);
	                    $pdfVoucher->set_voucherObj($voucherObj);
	                    t3lib_div::devLog('voucher object set ', 'pt_gsavoucher', 1);
	                    $pdfVoucher->fillMarkerArray();
	                    $pdfVoucher->set_xmlSmartyTemplate($this->conf['templateVoucherPDF']);
	                    $pdfVoucher->set_languageFile($this->conf['voucherPdfLL']);
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
        }*/
    }
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_pi3.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_pi3.php']);
}

?>
