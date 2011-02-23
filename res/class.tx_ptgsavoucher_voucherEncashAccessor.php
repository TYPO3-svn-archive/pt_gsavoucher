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
 * Database accessor class for tx_ptgsavoucher_voucherEncash of the 'pt_gsavoucher' extension
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-10-09
 */ 

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general helper library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_iSingleton.php'; // interface for Singleton design pattern

 
/**
 * voucherEncash Database Accessor class 
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-10-09
 * @package     TYPO3
 * @subpackage  tx_ptgsavoucher
 */
class tx_ptgsavoucher_voucherEncashAccessor  implements tx_pttools_iSingleton {
    /*
     * Contansts
     */
    const DB_TABLE = 'tx_ptgsavoucher_voucher_encash';
    
    /**
     * Properties
     */

    private static $uniqueInstance = NULL; // (tx_ptgsavoucher_voucherEncashAccessor object) Singleton unique instance
    

    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
    

    /**
     * Class constructor: prefills the object's properies depending on given params
     *
     * @param   void 
     * @return  void   
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
     */

    private function __construct () {
    }

    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the private/protected class constructor.
     *
     * @param   void
     * @return  object      unique instance of the object (Singleton) 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
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
     * @since   2008-10-09
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
     * Returns an array with Id of all voucher records with given voucherUid or orderWrapperId and flaf if encashabe is done 
     *
     * @param   integer     (optional) Id of voucher 
     * @param   integer     (optional) Id of order Wraper
     * @param   boolean     (optional) flag if encashing done  
     * @return  array       array with all voucher record IDs 
     * @throws  tx_pttools_exception if the query fails/returns false
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
     */
    public function getIdArr($voucherUid=0, $orderWrapperUid=0, $encashed=false)
    {
        trace('[METHOD] '.__METHOD__);
        $idArr = array();

        // get all relation ID's  
        $select = 'uid as uid';
        $from = self::DB_TABLE;
        $where = '1';
        if (intval($voucherUid) > 0) {
            $where .= " AND voucher_uid = ".intval($voucherUid);
        }
        if (intval($orderWrapperUid) > 0) {
            $where .= " AND order_wrapper_uid = ".intval($orderWrapperUid);
        }
        if ($encashed == false) {
        	$where .= ' AND order_wrapper_uid = 0';
        } else {
        	$where .= ' AND order_wrapper_uid <> 0';
        }
        $where .= tx_pttools_div::enableFields($from);
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        trace(tx_pttools_div::returnLastBuiltSelectQuery($GLOBALS['TYPO3_DB'], $select, $from, $where, $groupBy, $orderBy, $limit));
        
        if ($res === FALSE) {
            throw new tx_pttools_exception('Query failed: '.__METHOD__, 2, $GLOBALS['TYPO3_DB']->sql_error());
        }
        $idArr = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $idArr[] = $row['uid'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        trace($idArr);
        return $idArr;
    }


    /**
     * Method to delete Record in Database
     * @param   integer uid of datarecord to delete    
     * @return  void 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-14
     */
    
    public function deleteData($uid) {
        trace('[METHOD] '.__METHOD__);
        // mark it as deleted
        $row = array (
            'uid' => intval($uid),    
            'deleted' => 1,
        );

        if (intval($uid) != 0) {
            $row = tx_pttools_div::expandFieldValuesForQuery($row);
            $this->updateRecord($row);
        }
    }

 
    /**
     * Method to store Record in Database
     * @param   array    contains data to be stored in Database
     * @return  void 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
     */
    
    public function storeData($dataArr) {
        trace('[METHOD] '.__METHOD__);
        trace($dataArr,0,'$dataArr');
        $row = array (
               'uid' => intval($dataArr['uid']),
               'voucher_uid' => intval($dataArr['voucherUid']),
               'gsa_uid' => intval($dataArr['gsaUid']),
               'order_wrapper_uid' => intval($dataArr['orderWrapperUid']),
               'discount_percent' => (string)$dataArr['discountPercent'],
               'related_doc_no' => (string)$dataArr['relatedDocNo'],
               'article_uid' => intval($dataArr['articleUid']),
               'amount' => (double)$dataArr['amount'],
               'encashed_date' => (string)$dataArr['encashedDate'],

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
     * @since   2008-10-09
     */
    
    protected function insertRecord($row) {
        trace('[METHOD] '.__METHOD__);

        trace($row,0,'$row');
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery(self::DB_TABLE, $row);
        trace(tx_pttools_div::returnLastBuiltInsertQuery($GLOBALS['TYPO3_DB'], self::DB_TABLE, $row));
        if ($result == false) {
            throw new tx_pttools_exception('Error in Insert pt_gsavoucher_voucherEncash', 1);
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
     * @since   2008-10-09
     */
    
    public function updateRecord($row) {
        trace('[METHOD] '.__METHOD__);
        $where = 'uid ='.intval($row['uid']);
		trace($row,0,'$row');
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(self::DB_TABLE, $where, $row);
        trace(tx_pttools_div::returnLastBuiltUpdateQuery($GLOBALS['TYPO3_DB'], self::DB_TABLE, $where, $row));
		if ($result == false) {
            throw new tx_pttools_exception('Error in Update pt_gsaacounting_voucherEncash', 1);
        }
        return $row['uid'];
    }


    /**
     * Select voucher Data from Database Record
     * @param   integer uid of database Record
     * @return  array   array of database record fields
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
     */
    
    public function selectByUid($uid) {
        trace('METHOD] '.__METHOD__);
        // query preparation
        $where   = 'uid = '.intval($uid);
        
        return $this->selectRecord($where);
    }


    /**
     * Method to select record by given Parameters
     * @param Id of the record
     * @return array    record as array
     * @global TYPO3_DB     
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-10-09
     */
    public function selectRecord($where, $orderBy='', $groupBy='', $limit='' ) {
        trace('METHOD] '.__METHOD__);
        // query preparation
        $select  = 
            'uid AS uid'.
            ', voucher_uid AS voucherUid'.
            ', order_wrapper_uid AS orderWrapperUid'.
            ', gsa_uid AS gsaUid'.
            ', discount_percent AS discountPercent'.
            ', article_uid AS articleUid'.
            ', amount AS amount'.
            ', encashed_date AS encashedDate'.
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherEncashAccessor.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/res/class.tx_ptgsavoucher_voucherEncashAccessor.php']);
}
?>
