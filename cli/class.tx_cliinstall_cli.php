<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake GmbH
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

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

// Include basis cli class
require_once(PATH_t3lib.'class.t3lib_cli.php');

class tx_cliinstall_cli extends t3lib_cli  {
	
	public function __construct() {
		parent::t3lib_cli();
		$action = $_SERVER['argv'][1];
		$subAction = $_SERVER['argv'][2];

		switch ($action) {
			case 'dbCompare' :
				require_once(t3lib_extMgm::extPath('cliinstall') . 'classes/class.tx_cliinstall_dbcompare.php');
				
				$this->dbCompare = t3lib_div::makeInstance('tx_cliinstall_dbcompare');
				$sql = $this->dbCompare->getTableDefinitions();
				
				if(!empty($sql) && ! empty($subAction)) {
					$this->execSubAction($subAction, $sql);
				}
					
				$this->printMessage($sql ? $sql : 'Table and field definitions are OK.', $sql ? 1 : 0);
			case 'purgeCache' :
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->admin = 1;
				$tce->clear_cacheCmd('all');
				$this->printMessage('Cache purged');
			default:
				$availableActions = 'Available actions: dbCompare,purgeCache';
				$this->printMessage('Action not found! (' . $availableActions . ')', 1);
		}
	}
	
	protected function execSubAction ($subAction, $sql) {
		$queries = explode(';', str_replace(chr(10).chr(10), null, $sql));

		switch ($subAction) {
			case 'byStep' :
				foreach($queries as $query) {
					if(empty($query)) $this->printMessage('Finish!');
					
					if($this->cli_keyboardInput_yes(chr(10) .'Execute: ' . chr(10) . $query  . chr(10))) {
						$this->execAdminQuery($query, false);
					} else {
						$this->printMessage(chr(10) .'You\'ve choosen nope.' . chr(10), false);	
					}
				}
			break;
		
			case 'byRobot' :
				foreach($queries as $query) {
					if(empty($query)) $this->printMessage('Finish!');
					$this->execAdminQuery($query, false);
				}
			break;
		}
	}
	
	protected function execAdminQuery($query, $exitBySuccess = true) {
		$failure = $this->dbCompare->adminQuery($query . ';');
		if(empty($failure)) {
			$this->printMessage(chr(10) . 'Execution was succesfully!' . chr(10), 1, $exitBySuccess);
		} else {
			$failureMessage = $failure . chr(10) . 'Failure query:' . chr(10) . $query;
			$this->printMessage($failure);
		}
	}
	
	/**
	 * Prints the output message
	 *
	 * @param	string		$message for output
	 */	
	protected function printMessage($message, $status=0, $exit = true) {
		echo($message . PHP_EOL);
		if($exit) exit($status);
	}
}

t3lib_div::makeInstance('tx_cliinstall_cli');
?>
