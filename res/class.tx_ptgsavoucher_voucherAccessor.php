<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dorit Rottner <rottner@punkt.de>
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
 * Database accessor class for tx_ptgsavoucher_voucher of the 'pt_gsavoucher' extension
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-01-22
 */ 

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general helper library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_iSingleton.php'; // interface for Singleton design pattern

 
/**
 * voucher Database Accessor class 
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-01-22
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_voucherAccessor  implements tx_pttools_iSingleton {
    /*
     * Contansts
     */
    const DB_TABLE = 'tx_ptgsavoucher_voucher';
    
    /**
     * Properties
     */

    private static $uniqueInstance = NULL; // (tx_ptgsavoucher_voucherAccessor object) Singleton unique instance
    

    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
    

    /**
     * Class constructor: prefills the object's properies depending on given params
     *
     * @param   void 
     * @return  void   
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */

    private function __construct () {
    }

    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the private/protected class constructor.
     *
     * @param   void
     * @return  object      unique instance of the object (Singleton) 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    public static function getInstance() {
        
        if (self::$uniqueInstance === NULL) {
            $className = __CLASS__;
            self::$uniqueInstance = new $className;
        }
        return self::$uniqueInstance;
        
    }
    
    /**
     * Final method to prevent object cloning (using 'clone'), in order to use only the unique instance of the Singleton object.
     * @param   void
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    public final function __clone() {
        
        trigger_error('Clone is not allowed for '.get_class($this).' (Singleton)', E_USER_ERROR);
        
    }
    


    /***************************************************************************
     *   GETTER & SETTER METHODS
     **************************************************************************/

    /***************************************************************************
     *   Business METHODS
     **************************************************************************/
    /**
     * Returns an array with Id of all voucher records with given voucher od or documentNumber in GSA
     *
     * @param   integer      Address Id of Customer who has payed vouchers 
     * @param   integer     Address Id of Customer who has encashed vouchers
     * @return  array       array with all voucher record IDs 
     * @throws  tx_pttools_exception if the query fails/returns false
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    public function getVoucherIdArr($payedGsaUid=0,$encashedGsaUid=0)
    {
        trace('[METHOD] '.__METHOD__);
        $voucherIdArr = array();

        // get all relation ID's  
        $select = 'uid as uid';
        $from = self::DB_TABLE;
        $where = '1';
        if (intval($payedGsaUid) > 0) {
            $where .= " AND payed_gsa_uid >= ".intval($payedGsaUid);
        }
        if (intval($encashedGsaUid) > 0) {
            $where .= " AND encashed_gsa_uid >= ".intval($payedGsaUid);
        }
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        trace(tx_pttools_div::returnLastBuiltSelectQuery($GLOBALS['TYPO3_DB'], $select, $from, $where, $groupBy, $orderBy, $limit));
        
        if ($res === FALSE) {
            throw new tx_pttools_exception('Query failed: '.__METHOD__, 2, $GLOBALS['TYPO3_DB']->sql_error());
        }
        $relationIdArr = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $voucherIdArr[] = $row['uid'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        trace($voucherIdArr);
        return $voucherIdArr;
    }


    /**
     * Method to store Record in Database
     * @param   array    contains data to be stored in Database
     * @return  void 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    
    public function storeVoucherData($dataArr) {
        trace('[METHOD] '.__METHOD__);
        trace($dataArr,0,'$dataArr');
        $row = array (
               'uid' => intval($dataArr['uid']),
      		   'type' => intval($dataArr['type']),
               'code' => (string)$dataArr['code'],
        	   'pdfdoc_uid' => intval($dataArr['pdfdocUid']),
               'gsa_uid' => intval($dataArr['gsaUid']),
               'order_wrapper_uid' => intval($dataArr['orderWrapperUid']),
               'related_doc_no' => (string)$dataArr['relatedDocNo'],
               'category_confinement' => (string)($dataArr['categoryConfinement']),
               'article_confinement' => (string)($dataArr['articleConfinement']),
               'amount' => (double)$dataArr['amount'],
               'is_percent' => (boolean)$dataArr['isPercent'],
               'once_per_order' => (boolean)$dataArr['oncePerOrder'],
               'is_encashed' => (boolean)$dataArr['isEncashed'],
               'encashed_amount' => (double)$dataArr['encashedAmount'],
               'expiry_date' => (string)$dataArr['expiryDate'],

        );

        if (intval($dataArr['uid']) == 0) {
            $row = tx_pttools_div::expandFieldValuesForQuery( $row, true, 1);
            $dataArr['uid'] = $this->insertRecord($row);
        } else {
            $row = tx_pttools_div::expandFieldValuesForQuery($row);
            $this->updateRecord($row);
        }
        return $dataArr['uid'];
    }

 
    /**
     * Method to insert Record in Database
     * @param  array    contains data to insert
     * @return integer  uid after Insert statement 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    
    public function insertRecord($row) {
        trace('[METHOD] '.__METHOD__);

        trace($row,0,'$row');
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery(self::DB_TABLE, $row);
        trace(tx_pttools_div::returnLastBuiltInsertQuery($GLOBALS['TYPO3_DB'], self::DB_TABLE, $row));
        if ($result == false) {
            throw new tx_pttools_exception('Error in Insert pt_gsavoucher_voucher', 1);
        }
        trace ('result nach insert');
        trace ($result);
        
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }


    /**
     * Method to update Record in Database
     * @param   array   which contains the Data to be updated
     * @return  integer uid of updated Record 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    
    public function updateRecord($row) {
        trace('[METHOD] '.__METHOD__);
        $where = 'uid ='.intval($row['uid']);
		trace($row,0,'$row');
        $result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(self::DB_TABLE, $where, $row);
        if ($result == false) {
            throw new tx_pttools_exception('Error in Update pt_gsaacounting_voucher', 1);
        }
        return $row['uid'];
    }


    /**
     * Returns a document for a given voucher code
     *
     * @param 	string	voucher code 
     * @return 	array	database row
     * @author	Dorit Rottner <rottner@punkt.de>
     * @since	2008-07-22
     */
    public function selectVoucherDocumentByCode($code) {
        trace('[METHOD] '.__METHOD__);
    	
    	tx_pttools_assert::isNotEmptyString($code);
    	
        // query preparation
        $select  = 'doc.*';
        $from    = 'tx_ptgsashop_order_wrappers as ow, tx_ptgsapdfdocs_documents as doc,'.self::DB_TABLE.' as voucher';
        $where   = 'voucher.code = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($code, self::DB_TABLE);
        $where  .= ' AND voucher.pdfdoc_uid = doc.uid'; 
        $where  .= ' AND ow.uid = doc.ow_uid'; 
        $where  .=  tx_pttools_div::enableFields('tx_ptgsashop_order_wrappers', 'ow') ; 
        $where  .=  tx_pttools_div::enableFields('tx_ptgsapdfdocs_documents', 'doc') ;
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        tx_pttools_assert::isMySQLRessource($res);
        
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        return $row;
    }
    
    
    /**
     * Select voucher Data from Database Record
     * @param   integer uid of database Record
     * @return  array   array of database record fields
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    
    public function selectVoucherByUid($uid) {
        trace('METHOD] '.__METHOD__);
        // query preparation
        $where   = 'uid = '.intval($uid);
        
        return $this->selectRecord( $where);
        
    }

    /**
     * Select voucher Data from Database Record
     * @param   integer uid of database Record
     * @return  array   array of database record fields
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    
    public function selectVoucherByCode($code) {
        trace('METHOD] '.__METHOD__);
        // query preparation
        $from    = self::DB_TABLE;
        $where   = 'code = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($code, $from);
        
        return $this->selectRecord( $where);
        
    }

    /**
     * Method to select record by given Parameters
     * @param Id of the record
     * @return array    record as array
     * @global TYPO3_DB     
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-01-22
     */
    public function selectRecord($where, $orderBy='', $groupBy='', $limit='' ) {
        trace('METHOD] '.__METHOD__);
        // query preparation
        $select  = 
            'uid AS uid'.
            ', pdfdoc_uid AS pdfdocUid'.
        	', type AS type'.
        	', code AS code'.
            ', order_wrapper_uid AS orderWrapperUid'.
            ', gsa_uid AS gsaUid'.
            ', related_doc_no AS relatedDocNo'.
            ', category_confinement AS categoryConfinement'.
            ', article_confinement AS articleConfinement'.
            ', amount AS amount'.
            ', is_percent AS isPercent'.
            ', once_per_order AS oncePerOrder'.
            ', is_encashed AS isEncashed'.
            ', encashed_amount AS encashedAmount'.
            ', expiry_date AS expiryDate'.
        '';
        $from    = self::DB_TABLE;

        trace($select,0,'$select');
        trace($where,0,'$where');
        $where .= tx_pttools_div::enableFields($from);
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);        
        trace(tx_pttools_div::returnLastBuiltSelectQuery($GLOBALS['TYPO3_DB'], $select, $from, $where, $groupBy, $orderBy, $limit));
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $GLOBALS['TYPO3_DB']->sql_error());
        }
        $a_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        trace($a_row,0,'voucherArray'); 
        return $a_row;
    }



}    


/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherAccessor.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherAccessor.php']);
}
?>
