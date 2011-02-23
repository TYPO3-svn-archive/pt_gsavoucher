<?php

########################################################################
# Extension Manager/Repository config file for ext: "pt_gsavoucher"
#
# Auto generated 22-01-2008 18:22
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'GSA Vouchers',
	'description' => 'Process Vouchers (gift and discount) and book it in the GSA database',
    'category' => 'General Shop Applications',
	'author' => 'Dorit Rottner',
	'author_email' => 'rottner@punkt.de',
	'shy' => '',
	'dependencies' => 'pt_gsashop,pt_gsaaccounting,pt_gsapdfdocs,pt_tools',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.1dev',
	'constraints' => array(
		'depends' => array(
			'pt_gsashop' => '0.13.1-',
			'pt_gsaaccounting' => '0.0.3',
			'pt_gsapdfdocs' => '0.0.1',
			'pt_tools' => '0.4.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:25:{s:9:"ChangeLog";s:4:"e3c4";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"357b";s:14:"ext_tables.php";s:4:"35c1";s:14:"ext_tables.sql";s:4:"4f0a";s:32:"icon_tx_ptgsavoucher_voucher.gif";s:4:"475a";s:16:"locallang_db.xml";s:4:"0608";s:7:"tca.php";s:4:"2157";s:19:"doc/wizard_form.dat";s:4:"018b";s:20:"doc/wizard_form.html";s:4:"4169";s:33:"pi1/class.tx_ptgsavoucher_pi1.php";s:4:"c46f";s:17:"pi1/locallang.xml";s:4:"8a06";s:24:"pi1/static/editorcfg.txt";s:4:"dc2b";s:33:"pi2/class.tx_ptgsavoucher_pi2.php";s:4:"cdcf";s:17:"pi2/locallang.xml";s:4:"3040";s:24:"pi2/static/editorcfg.txt";s:4:"5121";s:33:"pi3/class.tx_ptgsavoucher_pi3.php";s:4:"3613";s:17:"pi3/locallang.xml";s:4:"4d69";s:24:"pi3/static/editorcfg.txt";s:4:"a254";s:33:"pi4/class.tx_ptgsavoucher_pi4.php";s:4:"8c9a";s:17:"pi4/locallang.xml";s:4:"7177";s:24:"pi4/static/editorcfg.txt";s:4:"3e19";s:20:"static/constants.txt";s:4:"11ab";s:16:"static/setup.txt";s:4:"11ab";}',
);

?>