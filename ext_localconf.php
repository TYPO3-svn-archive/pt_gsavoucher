<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_ptgsavoucher_pi1 = < plugin.tx_ptgsavoucher_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_ptgsavoucher_pi1.php','_pi1','list_type',0);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_ptgsavoucher_pi2 = < plugin.tx_ptgsavoucher_pi2.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_ptgsavoucher_pi2.php','_pi2','list_type',0);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_ptgsavoucher_pi3 = < plugin.tx_ptgsavoucher_pi3.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_ptgsavoucher_pi3.php','_pi3','list_type',0);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_ptgsavoucher_pi4 = < plugin.tx_ptgsavoucher_pi4.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi4/class.tx_ptgsavoucher_pi4.php','_pi4','list_type',0);

if (TYPO3_MODE == 'FE') { // WARNING: do not remove this condition since this may stop the backend from working!
    /*
     * pt_gsashop pi3 hooks
     */
    require(t3lib_extMgm::extPath('pt_gsavoucher').'res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_pi3.php');
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['pi3_hooks']['mainControllerHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_pi3';  // hook array (loop processing)
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['pi3_hooks']['processOrderOverview_checkoutHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_pi3';  // hook array (loop processing)
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['pi3_hooks']['displayOrderOverview_MarkerArrayHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_pi3';  // hook array (loop processing)
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['pi3_hooks']['displayOrderOverview_returnDeliveryMarkersHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_pi3';  // hook array (loop processing)
}
    /*
     * pt_gsashop gsaTransactionHandler_hooks
     */
    require(t3lib_extMgm::extPath('pt_gsavoucher').'res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler.php');
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['gsaTransactionHandler_hooks']['processShopOrderTransactionStorage_manipulateOrderConfDocRecordHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler';  // hook array (loop processing)
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['gsaTransactionHandler_hooks']['processShopOrderTransactionStorage_manipulateOrderConfPosRecordHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler';  // hook array (loop processing)
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['gsaTransactionHandler_hooks']['processShopOrderTransactionStorage_manipulateDelNoteDocRecordHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler';  // hook array (loop processing)
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['gsaTransactionHandler_hooks']['processShopOrderTransactionStorage_manipulateDelNotePosRecordHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler';  // hook array (loop processing)
    
    /*
     * pt_gsashop orderPresentator_hooks
     */
    require(t3lib_extMgm::extPath('pt_gsavoucher').'res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_orderPresentator.php');
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['orderPresentator_hooks']['getPlaintextPresentation_MarkerArrayHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_orderPresentator';  // hook array (loop processing)
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['orderPresentator_hooks']['getPlaintextPresentation_returnDeliveryMarkersHook'][] = 'tx_ptgsavoucher_hooks_ptgsashop_orderPresentator';  // hook array (loop processing)
    
    /*
     * pt_gsashop orderProcessor_hooks
     */
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['orderProcessor_hooks']['fixFinalOrderHook'][] = 'EXT:pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_orderProcessor.php:tx_ptgsavoucher_hooks_ptgsashop_orderProcessor->fixFinalOrderHook';     // hook array (loop processing)
    
    
    /*
     * pt_gsapdfdocs ivoice_hooks
     */
    require(t3lib_extMgm::extPath('pt_gsavoucher').'res/hooks/class.tx_ptgsavoucher_hooks_ptgsapdfdocs_invoice.php');
    $TYPO3_CONF_VARS['EXTCONF']['pt_gsapdfdocs']['tx_ptgsapdfdocs_invoice']['markerArrayHook'][] = 'tx_ptgsavoucher_hooks_ptgsapdfdocs_invoice';  // hook array (loop processing)

// eID-Skript for downloading the files
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_ptgsavoucher_download'] = 'EXT:pt_gsavoucher/eID/download.php';

?>