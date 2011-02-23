<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_ptgsavoucher_voucher"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ptgsavoucher_voucher.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, code, pdfdoc_uid, order_wrapper_uid, gsa_uid, related_doc_no, related_doc_no, category_confinement, article_confinement, amount, is_percent, once_per_order, is_encashed, encashed_amount, expiry_date",
	)
);

$TCA["tx_ptgsavoucher_voucher_encash"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash',        
        'label'     => 'uid',   
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",  
        'delete' => 'deleted',  
        'enablecolumns' => array (      
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ptgsavoucher_voucher.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, voucher_uid, order_wrapper_uid, gsa_uid, discount_percent, related_doc_no, article_uid, amount, encashed_date",
    )
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:pt_gsavoucher/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


#t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","GSA Admin Voucher");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:pt_gsavoucher/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');


#t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","GSA Handle Voucher");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:pt_gsavoucher/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');


#t3lib_extMgm::addStaticFile($_EXTKEY,"pi3/static/","Plugin3");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:pt_gsavoucher/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY.'_pi4'),'list_type');


#t3lib_extMgm::addStaticFile($_EXTKEY,"pi4/static/","Plugin4");

t3lib_extMgm::addStaticFile($_EXTKEY,'static//', 'GSA Voucher');

t3lib_div::loadTCA('tx_ptgsapdfdocs_documents');
$TCA['tx_ptgsapdfdocs_documents']['columns']['documenttype']['config']['items'][] = array('LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsapdfdocs_documents.documenttype.voucher', 'voucher');
#print_r($TCA['tx_ptgsapdfdocs_documents']);
?>