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

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_document.php';



require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_order.php';  // GSA Shop order class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapper.php';// GSA Shop order wrapper class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapperAccessor.php';  // GSA Shop database accessor class for order wrappers
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderPresentator.php';// GSA shop order presentator class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_user.php';  // // TYPO3 FE user class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_feCustomer.php';  // TYPO3 FE user class


/**
 * Invoice class
 *
 * $Id$
 *  
 * @author	Dorit Rottner <rottner@punkt.de>
 * @since	2008-07-15
 */
class tx_ptgsavoucher_pdfdoc extends tx_ptgsapdfdocs_document {
	
	/**
	 * @var tx_ptgsashop_order
	 */
	protected $orderObj;
	
	/**
	 * @var tx_ptgsauserreg_feCustomer
	 */
	protected $customerObj;
	
	/**
	 * @var tx_ptgsavoucher_voucher
	 */
	protected $voucherObj;
	
	/**
	 * @var string Documenttype
	 */
	protected $documenttype = 'voucher';


	/**
	 * @var integer If of Document
	 */
	protected $uid;
	
	
    /**
     * Returns the property value
     *
     * @param   void
     * @return  integer property value
     * @since   2008-07-23
    */
    public function get_uid() {
        return $this->uid;
    }

    /**
     * Set the property value
     *
     * @param   integer
     * @return  void
     * @since   2008-07-23
    */
    public function set_uid($uid) {
        $this->uid = intval($uid);
    }


	/**
	 * Set property value
	 * 
	 * @param 	tx_ptgsauserreg_feCustomer $customerObj
	 * @return 	tx_ptgsavoucher_pdfdoc $this
	 */
	public function set_customerObj(tx_ptgsauserreg_feCustomer $customerObj) {

		$this->customerObj = $customerObj;
		
		return $this;
	}



	/**
	 * Set property value
	 * 
	 * @param 	tx_ptgsashop_order $orderObj
	 * @return 	tx_ptgsavoucher_pdfdoc $this
	 */
	public function set_orderObj(tx_ptgsashop_order $orderObj) {

		$this->orderObj = $orderObj;
		
		return $this;
	}
	
	
	
	/**
	 * Set property value
	 * 
	 * @param 	tx_ptgsavoucher_voucher $voucherObj
	 * @return 	tx_ptgsavoucher_pdfdoc $this
	 */
	public function set_voucherObj(tx_ptgsavoucher_voucher $voucherObj) {

		$this->voucherObj = $voucherObj;
		
		return $this;
	}
	
	
/**
     * Fill the marker array
     *
     * @param	void
	 * @return 	tx_ptgsavoucher_pdfdoc $this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2007-10-10
     */
    public function fillMarkerArray() {
    	
    	tx_pttools_assert::isType($this->voucherObj, 'tx_ptgsavoucher_voucher');
    	tx_pttools_assert::isType($this->customerObj, 'tx_ptgsauserreg_feCustomer');

    	if (is_object($GLOBALS['TSFE'])) { 
            // try FE retrieval to avoid overhead (performance)
            $shopConfigArr = $GLOBALS['TSFE']->tmpl->setup['config.']['tx_ptgsashop.'];
            $voucherConfigArr = $GLOBALS['TSFE']->tmpl->setup['config.']['tx_ptgsavoucher.'];
    	} else {
            $extConfArray = tx_pttools_div::returnExtConfArray(self::EXT_KEY);
            $shopConfigArr = tx_pttools_div::returnTyposcriptSetup($extConfArray['tsConfigurationPid'], 'config.tx_ptgsashop.');
            $voucherConfigArr = tx_pttools_div::returnTyposcriptSetup($extConfArray['tsConfigurationPid'], 'config.tx_ptgsavoucher.');
    	}
    
        $this->markerArray['shopOperatorContact'] = array(
            'name'        => $shopConfigArr['shopOperatorName'],
            'streetNo'    => $shopConfigArr['shopOperatorStreetNo'],
            'zip'         => $shopConfigArr['shopOperatorZip'],
            'city'        => $shopConfigArr['shopOperatorCity'],
            'countryCode' => $shopConfigArr['shopOperatorCountryCode'],
            'email'       => $shopConfigArr['shopOperatorEmail'],
        );
        
        
    	// add some customer data, that is not in the order object
        $this->markerArray['voucherCode'] = $this->voucherObj->get_code();
        $this->markerArray['date'] = date('d.m.Y');
        $this->markerArray['amount'] = $this->voucherObj->get_amount();
        $this->markerArray['backgroundPdf'] = $voucherConfigArr['backgroundPDF'];
        
		$additionalMarkers = $voucherConfigArr['additionalMarkers.'];
		var_dump($voucherConfigArr);
		var_dump($additionalMarkers);
		$this->markerArray = array_merge($this->markerArray, $additionalMarkers);
        return $this;
    }
    
}


?>