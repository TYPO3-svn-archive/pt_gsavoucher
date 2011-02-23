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

/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucher.php';


/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath("jquery").'class.tx_jquery.php';
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_feCustomer.php';  // GSA specific FE customer class
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_gsaTransactionAccessor.php';
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_gsaTransactionHandler.php';

//$trace     = 1; // (int) trace options: 0=disable, 1=screen output, 2= write to log file (if configured in Constant Editor of pt_tools)

/**
 * Plugin 'Handle Voucher' for the 'pt_gsavoucher' extension.
 *
 * @author	Dorit Rottner <rottner@punkt.de>
 * @package	TYPO3
 * @subpackage	tx_ptgsavoucher
 */
class tx_ptgsavoucher_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_ptgsavoucher_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_ptgsavoucher_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'pt_gsavoucher';	// The extension key.
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
        #$GLOBALS['trace']=1; 
        $this->conf = $conf;
        trace($this->conf,0,'$this->conf');
        $this->shopConfig = $GLOBALS['TSFE']->tmpl->setup['config.']['tx_ptgsashop.'];        
        $gsashopExtConfArr = tx_pttools_div::returnExtConfArray('pt_gsashop');
        $this->costTaxCode = (string)$gsashopExtConfArr['dispatchTaxCode'];
        trace($this->shopConfig,0,'shopConfig');

        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj=1;   // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
        trace($this->piVars,0,'$this->piVars');
        trace($GLOBALS['TSFE']->fe_user->user,0,'fe_user->user');

        if($this->shopConfig['usePricesWithMoreThanTwoDecimals'] == 1) {
            $this->precision = 4;
        } else {
            $this->precision = 2;
        }    
        
        try {
            if ($GLOBALS['TSFE']->loginUser != 1) {
                    throw new tx_pttools_exception('User Login expired.', 4);
            }

            if ($this->piVars) {
                $this->getEnvironment();
                if ($this->command == 'encash') {
                    $content = $this->exec_processEncash();
                }
            } else {
                $content = $this->exec_showVoucherInputForm();
            }
        } catch (tx_pttools_exception $excObj) {
                // if an exception has been catched, handle it and overwrite plugin content with error message
                $excObj->handleException();
                $content = '<i>'.$excObj->__toString().'</i>';
        }
        return $this->pi_wrapInBaseClass($content);
    
    }

    /**
     * show Voucher Input Form
     * @param   void
     * @return  string formated content for myAccount for this customer
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-02-23
     */
    public function exec_showVoucherInputForm() {
        trace('[CMD] '.__METHOD__);

        $content = '';
        $content = $this->display_voucherInputForm();

        return $content;
    }

    /**
     * show Voucher Process encash of voucher
     * @param   void
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-02-24
     */
    public function exec_processEncash() {
        trace('[CMD] '.__METHOD__);
        if ($this->voucherCode != '') {
            $voucherObj = new tx_ptgsavoucher_voucher(0,$this->voucherCode);
            trace($voucherObj,0,'$voucherObj');
            $feCustomer = new tx_ptgsauserreg_feCustomer($GLOBALS['TSFE']->fe_user->user['uid']);
            if ($voucherObj->isEncashable($feCustomer->get_gsaMasterAddressId(),1) == true) {
                trace('valid Voucher');
                $amount = bcsub($voucherObj->get_amount(),$voucherObj->get_encashedAmount(),2);
                
                $documentArr = array();
                $documentArr['VERSART'] = $this->conf['freeDispatchType'];
                $documentArr['ENDPRB'] = $amount;
                $documentArr['ENDPRN'] = $amount;
                $documentArr['FLDC02'] = 'VO-'.$this->voucherCode; 
                $documentArr['FLDN01'] = 0.0000;
                $documentArr['FLDN02'] = 0.0000;
                $documentArr['FLDN03'] = 0.0000;
                $documentArr['FLDN04'] = 0.0000;
                $gsaAccountingTransactionHandlerObj = new tx_ptgsaaccounting_gsaTransactionHandler();
                $creditMemoDocId = $gsaAccountingTransactionHandlerObj->freeDocCreditMemoOrCancellation('creditMemo', $amount, 
                             $this->costTaxCode, $documentArr, $feCustomer->get_gsaCustomerObj(), $GLOBALS['TSFE']->fe_user->user['loginname'], date('Y-m-d'));  
                $relatedDocNo = tx_ptgsashop_gsaTransactionAccessor::getInstance()->selectTransactionDocumentNumber($creditMemoDocId); 
                $voucherObj->set_encashedDate(date('Y-m-d'));  
                $voucherObj->set_encashedGsaUid($feCustomer->get_gsaMasterAddressId());
                if ($voucherObj->get_encashedRelatedDocNo()) {
                    $relatedDocNo = $voucherObj->get_encashedRelatedDocNo().','.$relatedDocNo;
                }
                $voucherObj->set_encashedRelatedDocNo($relatedDocNo);
                $voucherObj->set_encashedAmount($voucherObj->get_amount());
                $voucherObj->storeSelf(); 
                tx_pttools_div::localRedirect($this->pi_getPageLink($this->pi_getPageLink($GLOBALS['TSFE']->id), ''));
            } else {
                // Fehlermeldung
                trace('no valid voucher');
            }
        } else {
            trace('no voucher code given');
            // Fehlermeldung
        }
        $content = $this->display_voucherInputForm();
        return $content;
    }


    /**
     * Display voucher input form
     * @param   void   
     * @return  string  Formated voucher input form
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-23
     */
    private function display_voucherInputForm() {
        trace('[METHOD] '.__METHOD__);
        // fetch css
        // get includes from jQuery Extension
        tx_jquery::includeLib();

        $cssFile = $this->conf['cssFile'];
        if ($cssFile) {
            $cssPath = $GLOBALS['TSFE']->absRefPrefix.$GLOBALS['TSFE']->tmpl->getFileName($cssFile);
            $linkCss = '<link rel="stylesheet" type="text/css" href="'.$cssPath.'" />'."\n";
        }
        trace ($linkCss.':'.$cssPath,0,'$linkCss');
        $GLOBALS['TSFE']->additionalHeaderData['pt_gsavoucher_pi2'] = 
            $linkCss.
            ''
            ;

         // create Smarty object and assign prefix
        $smarty = new tx_pttools_smartyAdapter($this->extKey);
        $smarty->assign('fv_action', $this->pi_getPageLink($GLOBALS['TSFE']->id));
        $smarty->assign('tx_prefix',$this->prefixId); // prefix for name of the input fields
        $smarty->assign('fv_jspath','EXT:pt_gsaavoucher/res/js');        

        // Buttons
        $smarty->assign('bl_encash',$this->pi_getLL('bl_encash','[bl_encash]'));
        
        // Labels
        $smarty->assign('fl_note1',$this->pi_getLL('fl_note1','[fl_note1]'));
        $smarty->assign('fl_note2',$this->pi_getLL('fl_note2','[fl_note2]'));
        $smarty->assign('fl_note3',$this->pi_getLL('fl_note3','[fl_note3]'));

        // Generate voucher input form
        $smartyFile=$this->conf['templateFileVoucherInputForm'];
        #trace($smartyFile,'0','smartyFile');        
        $filePath = $smarty->getTplResFromTsRes($smartyFile);

        trace($filePath, 0, 'Smarty template resource filePath');
        return $smarty->fetch($filePath);

    }

    /**
     * get Environment for this plugin
     * @param   void
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-02-24
     */
    private function getEnvironment() {
        trace('[METHOD] '.__METHOD__);
        $session = tx_pttools_sessionStorageAdapter::getInstance();
        trace ($session,0,'session');
        foreach ($this->piVars as $key => $val ) {
            $fields = explode('_', $key); 
            #trace ($fields);
            if ($fields[0] == 'encash') {
                $this->command = $fields[0];
                $this->voucherCode = (string) $this->piVars['voucherCode'];
            } else if ($fields[0] == 'book' ) {
                $this->command = $fields[0];
                list($this->filename,$this->hbciId) = explode('#',substr($key, strlen($fields[0])+1, strlen($key)- strlen($fields[0])+1));
            }
        }
        
        trace ($this->command, 0, '$this->command');
        trace ($this->voucherCode, 0, '$this->voucherCode,');
        
    }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/pi2/class.tx_ptgsavoucher_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/pi2/class.tx_ptgsavoucher_pi2.php']);
}

?>