<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dorit Rottner (rottner@punkt.de)
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
 * Main commission Handling module for the 'pt_gsapartners' extension to set status, orderAmount.
 *
 * $Id$
 *
 * @author	Dorit Rottner <rottner@punkt.de>
 */


#require_once(PATH_tslib.'class.tslib_pibase.php');
/**
 * Inclusion of external resources
 */
require_once 'Console/Getopt.php';  // PEAR Console_Getopt: parsing params as options (see http://pear.php.net/manual/en/package.console.console-getopt.php)

require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_cliHandler.php';


/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucher.php';
require_once t3lib_extMgm::extPath('pt_gsavoucher').'res/class.tx_ptgsavoucher_voucherAccessor.php';


class tx_ptgsavoucher_createVoucher  {

    /**
     * Constants
     */
	

    /**
     * Poperties
     */
    public $prefixId = 'tx_ptgsavoucher_createVoucher';		// Same as class name
	public $scriptRelPath = 'cronmod/class.tx_ptgsavoucher_createVoucher.php';	// Path to this script relative to the extension dir.
	public $extKey = 'pt_gsavoucher';	// The extension key.

	protected $command;     //command to execute
    protected $number;      //number of Vouchers generated 
    protected $amount;      //value of vouchers
    protected $articles;    //articleIds seperated with Commas
    protected $categories;  //categorIds seperated with Commas
    protected $outputfile;  //outputfile for generated vouchers
    protected $expiryDate;  //expiryDate if voucher expires
    
	protected $extConfArr;  //Extension Configuration array 
    
    /***************************************************************************
    *   CONSTRUCTOR & RUN METHOD
    ***************************************************************************/
    
    /**
     * Class constructor: define CLI options, set class properties 
     *
     * @param   void
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-30
     */
    public function __construct() {
            
            // for TYPO3 3.8.0+: enable storage of last built SQL query in $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery for all query building functions of class t3lib_DB
            $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
            //echo 'TYPO3_MODE: '.TYPO3_MODE;                        
            // define command line options
            $this->shortOptions = 'hc:n:v:o:e:a:g:'; // (string) short options for Console_Getopt  (see http://pear.php.net/manual/en/package.console.console-getopt.intro-options.php)
            $this->longOptionsArr = array('help', 'command='); // (array) long options for Console_Getopt (see http://pear.php.net/manual/en/package.console.console-getopt.intro-options.php)
            $this->helpString = "Availabe options:\n".
                                "-h/--help          Help: this list of available options\n".
                                "-c/--command       (required): Name of command to process\n".
                                "-n/--number        number of vouchers\n".
                                "-i/--identifier    identifier of voucher\n".
                                "-v/--value         value of voucher\n".
                                "-o/--outputfile    filename of uptputfile where generated voucher codes are written with path\n".
                                "-e/--expirydate     expiydate in format yyyy-mm-dd if voucher expires\n".
                                "-a/--articles      list of article Id's for which voucher is confined\n".
                                "-g/--categories    list of category Id's for which voucher is confined\n".
                            "\n";
            
            // start script output
            echo "\n".
                 "---------------------------------------------------------------------\n".
                 "CLI Signing processing started...\n".
                 "---------------------------------------------------------------------\n";
                
            // get extension configuration configured in Extension Manager (from localconf.php) - NOTICE: this has to be placed *before* the first call of $this->cliHandler->cliMessage()!!
            
            $this->extConfArr = tx_pttools_div::returnExtConfArray($this->extKey);
            if (!is_array($this->extConfArr)) {
                fwrite(STDERR, "[ERROR] No extension configuration found!\nScript terminated.\n\n");
                die();
            }
            
            // invoke CLI handler with extension configuration
            $this->cliHandler = new tx_pttools_cliHandler($this->scriptName, 
                                                          $this->extConfArr['cliAdminEmailRecipient'],
                                                          $this->extConfArr['cliEmailSender'], 
                                                          $this->extConfArr['cliHostName'],
                                                          $this->extConfArr['cliQuietMode'],
                                                          $this->extConfArr['cliEnableLogging'],
                                                          $this->extConfArr['cliLogDir']
                                                         );
            $this->cliHandler->cliMessage('Script initialized', false, 2, true); // start new audit log entry
            $this->cliHandler->cliMessage('$this->extConfArr = '.print_r($this->extConfArr, 1));

            echo 'extconf: ';var_dump($this->extConfArr); echo "\n";
            // dev only
            #fwrite(STDERR, "[TRACE] died: STOP \n\n"); die();
    }
    
    /**
     * Run of the CLI class: executes the business logic 
     * @param   void    
     * @return  boolean	if execution worked or failed
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2007-06-30
     */
    public function run() {
        #$GLOBALS['trace']=2;
    	#$this->gsaAccountingTransactionHandlerObj = new tx_ptgsavoucher_gsaTransactionHandler();
        try {
            
            trace($_SERVER['argv']);
        	if (!$_SERVER['argv'][1]) {
                throw new tx_pttools_exception('No options given.', 2);        	
        	}
            $this->processOptions();
            
            
            trace($this->command,0,'$this->command');
            switch ($this->command) {
                case 'create_voucher':
                    if (!$this->number) {
                        throw new tx_pttools_exception('No number of vouchers specified.', 3);
                    }
                	if (!$this->amount) {
                        throw new tx_pttools_exception('No value for voucher specified.', 3);
                    }
                    $this->createVoucher();
                    echo 
                     "---------------------------------------------------------------------\n".
                     "Command ".$this->command." ended\n".
                     "---------------------------------------------------------------------\n";
                    return true;
                    break;
                default:
                    throw new tx_pttools_exception('Invalid command: '.$this->command, 3);            }
            
            } catch (tx_pttools_exception $excObj) {
            
            // if an exception has been catched, handle it and display error message
            $this->cliHandler->cliMessage($excObj->__toString()."\n", true, 1);
            
        }
        return true;
        
    }
    
    
    
    /***************************************************************************
    *   BUSINESS LOGIC METHODS
    ***************************************************************************/
    
    /** 
     * Processes the command line arguments as options and sets the resulting class properties
     *
     * @param   void
     * @return  void       
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-30
     */
    private function processOptions() {
        trace('[METHOD] '.__METHOD__);
        
        $console = new Console_Getopt;  // PEAR module (see http://pear.php.net/manual/en/package.console.console-getopt.php)
        trace('before $parsedOptionsArr');
        $parsedOptionsArr = $this->cliHandler->getOptions($console, $this->shortOptions, $this->helpString, $this->longOptionsArr, true);
        
        trace($parsedOptionsArr,0,'$parsedOptionsArr');
        $this->documentType = 'invoice';
        // evaluate options, set properties
        for ($i=0; $i<sizeOf($parsedOptionsArr); $i++) {
            if ($parsedOptionsArr[$i][0] == 'h' || $parsedOptionsArr[$i][0] == '--help') {
                die($this->helpString);
            }
            if ($parsedOptionsArr[$i][0] == 'c' || $parsedOptionsArr[$i][0] == '--command') {
                $this->command = $parsedOptionsArr[$i][1];
            }
            if ($parsedOptionsArr[$i][0] == 'n' || $parsedOptionsArr[$i][0] == '--number') {
                $this->number = $parsedOptionsArr[$i][1];
            }
            if ($parsedOptionsArr[$i][0] == 'v' || $parsedOptionsArr[$i][0] == '--value') {
                $this->amount = $parsedOptionsArr[$i][1];
            }
            if ($parsedOptionsArr[$i][0] == 'o' || $parsedOptionsArr[$i][0] == '--outputfile') {
                $this->outputfile = $parsedOptionsArr[$i][1];
            }
            if ($parsedOptionsArr[$i][0] == 'e' || $parsedOptionsArr[$i][0] == '--expirydate') {
                $this->expiryDate = $parsedOptionsArr[$i][1];
            }
            if ($parsedOptionsArr[$i][0] == 'a' || $parsedOptionsArr[$i][0] == '--articles') {
                $this->articles = $parsedOptionsArr[$i][1];
            }
            if ($parsedOptionsArr[$i][0] == 'g' || $parsedOptionsArr[$i][0] == '--categories') {
                $this->categories = $parsedOptionsArr[$i][1];
            }
        }
        trace($parsedOptionsArr,0,'$parsedOptionsArr');
            
    }
    

    /**
     * cretae vouchers and write it in Outputfile if specified    
     * @param   void       
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-30
     */
    
    private function createVoucher(){
    	trace('[CMD] '.__METHOD__);
    	trace($this,0,'this');
        $vouchers = array();
    	// get Documents with are not signed and signing is not in process
        // TODO write assertions for articleIds und catgoryIds if defines
        $i = 0;
    	while ($i < $this->number) {
            $voucher = new tx_ptgsavoucher_voucher();
    	    $voucher->createCode(time());
            $voucher->set_amount($this->amount);
            #$voucher->set_oncePerOrder(1);
            if ($this->articles) {
    	       $voucher->set_articleConfinement($this->articles);
            }
            if ($this->categories) {
                $voucher->set_categoryConfinement($this->categories);
            }
            if ($this->expiryDate) {
                $voucher->set_expiryDate($this->expiryDate);
            }
            $voucher->storeSelf();
            $vouchers[] = $voucher->get_code() . ' '.$this->amount;
    	    trace($voucher,0,'voucher');
    	    $i++;
    	}
        if ($this->outputfile) {
	        $fh = fopen($this->outputfile,'w');
	        if ($this->expiryDate) {
	        	fwrite($fh, 'Expiry Date: ' .$this->expiryDate."\n\n");
	        }
	        foreach ($vouchers as $line) {
                fwrite($fh,$line."\n");
            }
            fclose($fh);
        }
    	
    }


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/cronmod/class.tx_ptgsavoucher_createVoucher.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsavoucher/cronmod/class.tx_ptgsavoucher_createVoucher.php']);
}

?>
