<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_ptgsavoucher_voucher"] = array (
	"ctrl" => $TCA["tx_ptgsavoucher_voucher"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden, code, pdfdoc_uid, order_wrapper_uid, gsa_uid, related_doc_no, related_doc_no, category_confinement, article_confinement, amount, is_percent, once_per_order, is_encashed, encashed_amount, expiry_date"
	),
	"feInterface" => $TCA["tx_ptgsavoucher_voucher"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
	    'type' => array (        
        'exclude' => 0,        
        'label' => 'LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.type',        
        'config' => array (
                'type' => 'select',
                'items' => array (
                    array('LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.type.I.0', '0'),
                    array('LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.type.I.1', '1'),
                ),
                'size' => 1,    
                'maxitems' => 1,
            )
        ),
		"code" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "required,trim",
			)
		),
		"pdfdoc_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.pdfdoc_uid",		
            "config" => Array (
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'int,nospace',
                "default" => 0
			)
		),
        "order_wrapper_uid" => Array (      
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.order_wrapper_uid",      
            "config" => Array (
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'int,nospace',
                "default" => 0
            )
        ),
		"gsa_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.gsa_uid",		
            "config" => Array (
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'int,nospace',
                "default" => 0
            )
		),
		"related_doc_no" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.related_doc_no",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "trim",
			)
		),
		"category_confinement" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.category_confinement",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "trim",
			)
		),
		"article_confinement" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.article_confinement",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "trim",
			)
		),
		"amount" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.amount",		
            'config' => array(
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'double2',
            )
		),
		"is_percent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.is_percent",		
			"config" => Array (
				"type" => "check",
			)
		),
        "once_per_order" => Array (     
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.once_per_order",     
            "config" => Array (
                "type" => "check",
            )
        ),
        "is_encashed" => Array (     
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.is_encashed",     
            "config" => Array (
                "type" => "check",
            )
        ),
        "encashed_amount" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.encashed_amount",		
            'config' => array(
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'double2',
            )
		),
		"expiry_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher.expiry_date",		
            "config" => Array (
                "type" => "input",  
                "size" => "30", 
                "max" => "10",  
                "eval" => "trim",
            )
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, type, code, pdfdoc_uid, order_wrapper_uid, gsa_uid, related_doc_no, related_doc_no, category_confinement, article_confinement, amount, is_percent, once_per_order, is_encashed, encashed_amount, expiry_date")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);


$TCA["tx_ptgsavoucher_voucher_encash"] = array (
    "ctrl" => $TCA["tx_ptgsavoucher_voucher_encash"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden, order_wrapper_uid, gsa_uid, discount_percent,  related_doc_no, article_uid, amount, encashed_date"
    ),
    "feInterface" => $TCA["tx_ptgsavoucher_voucher_encash"]["feInterface"],
    "columns" => array (
        'hidden' => array (     
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "voucher_uid" => Array (     
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.voucher_uid",     
            "config" => Array (
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'int,nospace',
                "default" => 0
            )
        ),
        "order_wrapper_uid" => Array (      
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.order_wrapper_uid",      
            "config" => Array (
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'int,nospace',
                "default" => 0
            )
        ),
        "gsa_uid" => Array (        
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.gsa_uid",        
            "config" => Array (
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'int,nospace',
                "default" => 0
            )
        ),
        "discount_percent" => Array (     
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.discount_percent",     
            "config" => Array (
                "type" => "input",  
                "size" => "30", 
                "max" => "100", 
                "eval" => "trim",
            )
        ),
        "related_doc_no" => Array (     
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.related_doc_no",     
            "config" => Array (
                "type" => "input",  
                "size" => "30", 
                "max" => "100", 
                "eval" => "trim",
            )
        ),
        "article_uid" => Array (        
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.article_uid",        
            "config" => Array (
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'int,nospace',
                "default" => 0
            )
        ),
        "amount" => Array (     
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.amount",     
            'config' => array(
                'type' => 'input',    
                'size' => '10',    
                'max' => '10',    
                'eval' => 'double2',
            )
        ),
        "encashed_date" => Array (        
            "exclude" => 1,     
            "label" => "LLL:EXT:pt_gsavoucher/locallang_db.xml:tx_ptgsavoucher_voucher_encash.encashed_date",        
            "config" => Array (
                "type" => "input",  
                "size" => "30", 
                "max" => "10",  
                "eval" => "trim",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, order_wrapper_uid, gsa_uid, discount_percent, related_doc_no, article_uid, amount, encashed_date")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);

?>
