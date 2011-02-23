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
/** 
 * Hooking class of the 'pt_gsavoucher' extension for hooks in tx_ptgsashop_gsaTransactionHandler
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-10-22
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

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_gsaaccounting').'res/class.tx_ptgsaaccounting_erpDocument.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_gsaTransactionAccessor.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_gsaTransactionHandler.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class


/**
 * Class being included by pt_gsashop using hooks in tx_ptgsashop_gsaTransactionHandler
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-10-22
 * @package     TYPO3
 * @subpackage  tx_ptggsavoucher
 */
class tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler extends tx_ptgsashop_gsaTransactionHandler {
    

    /**
     * Class constructor: sets the object's properties
     *
     * @param   void
     * @return  void     
     * @global  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    public function __construct() {
        $this->shopConfigArr = tx_ptgsashop_lib::getGsaShopConfig();        
        trace($this->shopConfigArr,0,'shopConfigArr');
    }

    
    /**
     * This method is called by a hook in tx_ptgsashop_gsaTransactionHandler::processShopOrderTransactionStorage_manipulateOrderConfDocRecordHook(): initiate pt_voucher specific stuff
     *
     * @param   integer    Id of the Order Confirmation Document which is possibly manipulated
     * @param   tx_ptgsashop_order 
     * @return  void         
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    public function processShopOrderTransactionStorage_manipulateOrderConfDocRecordHook($orderConfDocId, tx_ptgsashop_order $orderObj) {
        trace('[METHOD] '.__METHOD__);
        $this->manipulateErpDocument($orderConfDocId, $orderObj);        
    }
    
    /**
     * This method is called by a hook in tx_ptgsashop_gsaTransactionHandler::processShopOrderTransactionStorage_manipulateOrderConfPosRecordHook(): initiate pt_voucher specific stuff
     *
     * @param   integer     Id of the Order Confirmation Document which Position is possibly manipulated
     * @param   integer     Position Counter of this Position
     * @param   integer     Article key for this Position 
     * @param   tx_ptgsashop_order 
     * @return  void         
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    public function processShopOrderTransactionStorage_manipulateOrderConfPosRecordHook($orderConfDocId, $posCounter, $articleKey,  tx_ptgsashop_order $orderObj) {
        trace('[METHOD] '.__METHOD__);
        $this->manipulateErpPosition($orderConfDocId, $posCounter, $articleKey, $orderObj);        
    }
    
    /**
     * This method is called by a hook in tx_ptgsashop_gsaTransactionHandler::processShopOrderTransactionStorage_manipulateDelNoteDocRecordHook(): initiate pt_voucher specific stuff
     *
     * @param   integer     Id of the Delivery Note Document which is possibly manipulated
     * @param   tx_ptgsashop_order 
     * @return  void         
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    public function processShopOrderTransactionStorage_manipulateDelNoteDocRecordHook($noteConfDocId,  tx_ptgsashop_order $orderObj) {
        trace('[METHOD] '.__METHOD__);
        $this->manipulateErpDocument($noteConfDocId, $orderObj);        
    }
    
    /**
     * This method is called by a hook in tx_ptgsashop_gsaTransactionHandler::processShopOrderTransactionStorage_manipulateDelNotePosRecordHook(): initiate pt_voucher specific stuff
     *
     * @param   integer     Id of the Delivery NoteConfirmation Document which Position is possibly manipulated
     * @param   integer     Position Counter of this Position
     * @param   integer     Article key for this Position 
     * @param   tx_ptgsashop_order 
     * @return  void         
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    public function processShopOrderTransactionStorage_manipulateDelNotePosRecordHook($orderConfDocId, $posCounter, $articleKey,  tx_ptgsashop_order $orderObj) {
        trace('[METHOD] '.__METHOD__);
        $this->manipulateErpPosition($orderConfDocId, $posCounter, $articleKey, $orderObj);        
    }
    
    /**
     * This method manipulates ther EerDocument if there is voucher with Discount of the whole order
     *
     * @param   integer     Id of the ERP Document which is possibly manipulated
     * @param   tx_ptgsashop_order 
     * @return  void         
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    protected function manipulateErpDocument($erpDocumentId, tx_ptgsashop_order $orderObj) {
        $voucherEncashCollection = new tx_ptgsavoucher_voucherEncashCollection();
        $voucherEncashCollection->load($orderObj);
        foreach ($voucherEncashCollection  as $voucherEncash) {
        	// There could be only one discount per order
            if ($voucherEncash->isDiscountTotal()) {
                trace('Discount Total');
            	$erpDocument = new tx_ptgsaaccounting_erpDocument($erpDocumentId);
                trace($erpDocument,0,'$erpDocument');
            	$erpDocument->set_totalDiscountPercent($voucherEncash->get_discountPercent());
                $erpDocument->set_totalDiscountType(0);
                
                $discountGross = round(floatval(bcmul(bcdiv($erpDocument->get_amountGross(),100,6),$erpDocument->get_totalDiscountPercent(),2)),2);
                $discountNet = round(floatval(bcmul(bcdiv($erpDocument->get_amountNet(),100,6),$erpDocument->get_totalDiscountPercent(),4)),4);
                $erpDocument->set_amountGross(bcsub($erpDocument->get_amountGross(),$discountGross,2));
                $erpDocument->set_amountNet(bcsub($erpDocument->get_amountNet(),$discountNet,2));
                $erpDocument->storeSelf();                
            } else if ($voucherEncash->get_articleUid()){
                trace('Discount Article'.$voucherEncash->get_articleUid());
            	// Discount for article
            	$erpDocument = new tx_ptgsaaccounting_erpDocument($erpDocumentId);
                trace($erpDocument,0,'$erpDocument');
            	$amountGross = bcsub($erpDocument->get_amountGross(),$voucherEncash->get_amount(),2);
                $taxRate = tx_pttools_finance::getTaxRate($erpDocument->get_amountGross(),$erpDocument->get_amountNet(),4);
                $amountNet = tx_pttools_finance::getNetPriceFromGross($amountGross,$taxRate,$this->precission);
                $erpDocument->set_amountGross($amountGross);
                $erpDocument->set_amountNet($amountNet);
                $erpDocument->storeSelf();                
            }
        }
    }
    

    /**
     * This method manipulates ther EerDocument if there is voucher with Discount of the whole order
     *
     * @param   integer     Id of the ERP Document which Position is possibly manipulated
     * @param   integer     Position Counter of this Position
     * @param   integer     Article key for this Position 
     * @param   tx_ptgsashop_order     
     * @return  void         
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-22
     */
    protected function manipulateErpPosition($erpDocumentId, $posCounter, $articleKey, tx_ptgsashop_order $orderObj) {
        $voucherEncashCollection = new tx_ptgsavoucher_voucherEncashCollection();
    	$voucherEncashCollection->load($orderObj);
        foreach ($voucherEncashCollection  as $voucherEncash) {
            // There could be only one discount per order
            if ($articleKey == $voucherEncash->get_articleUid()) {
                $positionArr = tx_ptgsashop_gsaTransactionAccessor::getInstance()->selectTransactionDocPositions($erpDocumentId);
                trace($posCounter,0,'$posCounter');
                foreach ($positionArr as $position) {
                	if ($position['POSINR'] == $posCounter) {
                        $discountPercent =  $voucherEncash->get_discountPercent();
                		trace($voucherEncash,0,'$voucherEncash');
                        trace($discountPercent,0,'$discountPercent');
                		$singlePrice = round(floatval(bcmul(bcdiv($position['EP'],100,6),$position['RABATT'],2)),2);
                		if ($position['MENGE'] > 1) {
                			$maxPosCounter = $this->getMaxPosCounter($positionArr)+1;
                            trace($maxPosCounter,0,'$maxPosCounter');
                			// Split Record set update fields and add new   
                            $positionNew = $position;
                            $position['MENGE'] --;
                            $position['GP'] = bcmul($position['MENGE'],$position['EP'],4);
                            $positionNew['POSINR'] = $maxPosCounter;
                            $positionNew['MENGE'] = 1;
                            $positionNew['EP'] = $singlePrice;
                            $positionNew['GP'] = bcmul($position['MENGE'],$positionNew['EP'],4);
                            $positionNew['RABATT'] =  $discountPercent;
                            $positionNew['UREP'] = $singlePrice; 
                            $positionNew['INSZAEHLER'] = ($maxPosCounter * 10000); ### ??? TODO: find out where this comes from and what it is needed for....
                            tx_ptgsashop_gsaTransactionAccessor::getInstance()->insertTransactionDocPosition($positionNew);
                		} else {
                		   // set update fields
                            $position['EP'] = $singlePrice;
                            $position['UREP'] = $singlePrice; 
                            $position['GP'] = bcmul($position['MENGE'],$position['EP'],4);
                            $position['RABATT'] =  $discountPercent;
                			
                		}
                		// TODO update position
                		tx_ptgsashop_gsaTransactionAccessor::getInstance()->updateTransactionDocPosition($position['NUMMER'], $position);
                	}
                }
            }
        }
    }
    
    /**
     * get Maximum Position Counter of the Position Array
     *
     * @param   array    Position Array
     * @return  integer  Maximum Position Counter         
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-24
     */
    protected function getMaxPosCounter($posArr) {
    	$posCounter = array();
    	foreach ($posArr as $pos) {
    		$posCounter[] = $pos['POSINR'];
    	}
        trace(max($posCounter),0,'max($posCounter)');
    	return max($posCounter);
        
    }
    

} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/hooks/class.tx_ptgsavoucher_hooks_ptgsashop_gsaTransactionHandler.php']);
}

?>
