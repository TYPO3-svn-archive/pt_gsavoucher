<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Dorit Rottner (rottner@punkt.de)
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
 * Session voucherEncash Collection class for the 'pt_gsavoucher' extension
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-10-17
 */ 
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */



/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherEncashCollection.php';  // GSA Shop order class

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_sessionStorageAdapter.php'; // storage adapter for TYPO3 _browser_ sessions


/**
 * Voucher Enash Collection
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-10-17
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_sessionVoucherEncashCollection extends tx_ptgsavoucher_voucherEncashCollection implements tx_pttools_iSingleton {
    
    /**
     * Properties
     */
    private static $uniqueInstance = NULL;   // (tx_ptgsavoucher_sessionVoucherEncashCollection object) Singleton unique instance
    private static $canInstantiate = false;  // (boolean) flag wether the Singleton class can be instatiated (needed because of public constructor inherited from parent class)
    
    /**
     * Class Constants
     */
    const SESSION_KEY_NAME = 'tx_ptgsavoucher_sessionVoucherEncashCollection'; // (string) session key name to store order in session
    
    /**
     * Class constructor: Use getInstance() to get the unique instance of this object (Singleton) - called from the global scope this constructor will trigger an error
     *
     * This special Singleton constructor is needed because of the constructor's access level 'public' inherited from parent class
     * 
     * @param   void      
     * @return  void     
     * @global  
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-05-09
     */
     public function __construct() {
        
        if (self::$canInstantiate !== true) {
            trigger_error (__CLASS__.' is not supposed to be instantiated from the global scope because it is a Singleton class', E_USER_ERROR);
        }
        
        trace('***** Creating new '.__CLASS__.' object. *****');
        parent::__construct();
        
    }
    
    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the class constructor.
     *
     * This special Singleton code is needed because of the constructor's access level 'public' inherited from parent class
     * 
     * @param   void   
     * @return  tx_ptgsashop_sessionFeCustomer      unique instance of the object (Singleton) 
     * @global     
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-05-09
     */
    public static function getInstance() {
        trace('***** getInstance of '.__CLASS__.' object. *****');
    	
        if (self::$uniqueInstance === NULL) {
            $selfClassName = __CLASS__;
            self::$canInstantiate = true;
            self::$uniqueInstance = new $selfClassName;
            self::$canInstantiate = false;
        }
        
        return self::$uniqueInstance;
        
    }
    
    /**
     * Final method to prevent object cloning (using 'clone'), in order to use only the singleton unique instance of the object.
     * @param   void
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2005-09-15
     */
    public final function __clone() {
        
        trigger_error('Clone is not allowed for '.__CLASS__.' (Singleton)', E_USER_ERROR);
        
    }
    
    
    
    
    /***************************************************************************
     *   GENERAL METHODS
     **************************************************************************/
    
    /**
     * loads the session voucher encash collection from paymentModifier and stores it to the browser session
     *
     * @param   object  tx_ptgsashop_order
     * @return  void
     * @global  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-17
     */
    public function load(tx_ptgsashop_order $orderObj) { 

        $paymentModifierCol = new tx_ptgsaaccounting_paymentModifierCollection($orderObj->get_orderArchiveId());
        $orderObj->set_paymentModifierCollObj($paymentModifierCol);
        if ($paymentModifierCol != NULL) {
        	foreach ($paymentModifierCol as $paymentModifier) {
                if ($paymentModifier->get_addDataType() == 'voucher') {
                    $voucherEncash = unserialize($paymentModifier->get_addData());
                    $this->addItem($voucherEncash,$voucherEncash->get_voucherUid());
                }
            }
        }
        if (count($this) > 0) {
            $this->store();
        }
    }
    
    /**
     * Stores the complete session voucher encash collection to the browser session
     *
     * @param   void
     * @return  void
     * @global  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-17
     */
    public function store() { 
        
        trace($this, 0, 'STORING COMPLETE session voucher Encash Collection (AS SERIALIZED Collection) TO BROWSER SESSION KEY '.self::SESSION_KEY_NAME);
        tx_pttools_sessionStorageAdapter::getInstance()->store(self::SESSION_KEY_NAME, $this);

    }
    
    /**
     * Deletes the complete the voucher encash collection from the browser session
     *
     * @param   void
     * @return  void
     * @global  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-17
     */
    public function delete() { 
        
        trace('DELETING COMPLETE SESSION Voucher Encash Collection FROM BROWSER SESSION KEY '.self::SESSION_KEY_NAME);
        tx_pttools_sessionStorageAdapter::getInstance()->delete(self::SESSION_KEY_NAME, $this);
        
    }
    
    
    
    
} // end class




/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_sessionVoucherEncashCollection.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_sessionVoucherEncashCollection.php']);
}

?>