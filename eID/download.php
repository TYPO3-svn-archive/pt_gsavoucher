<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Dorit Rottner(rottner@punkt.de)
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

// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php';
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherAccessor.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapper.php';
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_feCustomer.php';

try {
	// Initialize FE user object:
	$feUserObj = tslib_eidtools::initFeUser();
	
	// Connect to database:
	tslib_eidtools::connectDB();
	
	//$trace = 1;
	$vocc = t3lib_div::_GP('vocc'); // "vocc" = "voucher Code"
	$saveDoc = t3lib_div::_GP('saved'); // Marker if Document should be saved
	tx_pttools_assert::isNotEmptyString($vocc, array('message' => 'No valid "vocc" set!'));
	$voucherCode = urldecode($vocc);
	
	$document = tx_ptgsavoucher_voucherAccessor::getInstance()->selectVoucherDocumentByCode($voucherCode);
	trace($document,0,'$document');
	tx_pttools_assert::isArray($document, array('No document found in database for this "vocc"!'));
	
	// check if current user is allowed to download this file!
	// gsauid of orderwrapper feuser have to be the same
	$orderwrapper = new tx_ptgsashop_orderWrapper($document['ow_uid']);
	$feCustomer = new tx_ptgsauserreg_feCustomer($feUserObj->user['uid']);
	if ($orderwrapper->get_customerId() != $feCustomer->get_gsaMasterAddressId()) {
		throw new tx_pttools_exception('No access right for this doucment: '. $voucherCode.' user: '.$feUserObj->user['uid']);
	} else {
		// user is allowd to see this document	
		$filepath = PATH_site.$document['file'];
		
		tx_pttools_assert::isFilePath($filepath, array('message' => 'File "'.$filepath.'" not found'));
	
		$path_parts = pathinfo($filepath);
		$filename = $path_parts['basename'];
		
		ob_clean();
		if ($saveDoc == true) {
			header("Content-Type: application/octet-stream");
			header('Content-Disposition: attachment; filename="'.$filename.'"');
		} else {
			header('Content-type: application/pdf');
		}
		header("Content-Length: " .(string)(filesize($filepath)) );
		header("Content-Transfer-Encoding: binary\n");
		
		readfile($filepath);
		
		exit();
	}
} catch (Exception $exception) {
	if (method_exists($exception, 'handle')) {
		$exception->handle();
	}
	echo $exception->__toString();
}

?>