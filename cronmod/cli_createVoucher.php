#! /usr/local/bin/php
<?php
##########################################################################
#####  This CLI script must always be executed by its absolute path! #####
##########################################################################
##### First line of this file is OS specific: use the following for: #####
##### FreeBSD : #! /usr/local/bin/php                                #####
##### Linux(?): #! /usr/bin/php                                      #####
##########################################################################
/** 
 * Command Line Interface Caller Script for tx_ptgsaaccounting_dta
 * !!! This CLI script must always be executed by its absolute path !!!
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-09-30
 */ 
 
define('TYPO3_cliMode', TRUE);
// Defining PATH_thisScript: must be the ABSOLUTE path of this script in the right context - this will work as long as the script is called by it's absolute path!

define('PATH_thisScript', (isset($_ENV['_']) ? $_ENV['_'] : (isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : $_SERVER['_']))); // double fallback - $_SERVER['argv'][0] works for FreeBSD

// set up TYPO3-environment
require_once(dirname(PATH_thisScript).'/conf.php');
define('PATH_tslib',dirname(PATH_thisScript).'/'.$BACK_PATH.'sysext/cms/tslib/');
require_once(dirname(PATH_thisScript).'/'.$BACK_PATH.'init.php');


//Creating a fake TSFE object
require_once(t3lib_extMgm::extPath('pt_tools').'res/inc/faketsfe.inc.php');
require_once(t3lib_extMgm::extPath('pt_gsavoucher').'cronmod/class.tx_ptgsavoucher_createVoucher.php');


//$trace = 2;

$statuscode = false;
$processor = new tx_ptgsavoucher_createVoucher();
$statuscode = $processor->run($_SERVER['argv']);

exit ($statuscode);


?>