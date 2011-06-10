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

class tx_cliinstall_dbcompare {
	protected $install;
	protected $lang = array (
		'titles' => array (
			'add' => array (
				'fields' => 'Add fields',
				'table' => 'Add tables'
			),
			'change' => array (
				'title' => 'Changing fields',
				'change' => 'Remove unused fields (rename with prefix)',
				'table' => 'Removing tables (rename with prefix)',
				'comment' => 'Change:'
			),
			'remove' => array (
				'fields' => 'Remove unused fields (rename with prefix)',
				'tables' => 'Removing tables (rename with prefix)'
			),
			'drop' => array (
				'fields' => 'Drop fields (really!)',
				'table' => 'Drop tables (really!)'
			)
		)
	);
	
	public function __construct() {
		$this->install = t3lib_div::makeInstance('t3lib_install');
	}
	
	/**
	 * Get Table definitions from typo3 install tool
	 *
	 * @return	string		sql update query
	 */	
	public function getTableDefinitions() {
		$tblFileContent = t3lib_div::getUrl(PATH_t3lib.'stddb/tables.sql');

		foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $loadedExtConf) {
			if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql'])	{
				$tblFileContent.= chr(10).chr(10).chr(10).chr(10).t3lib_div::getUrl($loadedExtConf['ext_tables.sql']);
			}
		}
		if ($tblFileContent)	{
			$fileContent = implode(
				chr(10),
				$this->install->getStatementArray($tblFileContent,1,'^CREATE TABLE ')
			);
			
			$FDfile = $this->install->getFieldDefinitions_fileContent($fileContent);
			if (!count($FDfile)) throw new Exception("Error: There were no 'CREATE TABLE' definitions in the provided file");
			
				// Init again / first time depending...
			$FDdb = $this->install->getFieldDefinitions_database();
			$diff = $this->install->getDatabaseExtra($FDfile, $FDdb);
			
			$updateDiff = $this->install->getDatabaseExtra($FDfile, $FDdb);
			$removeDiff = $this->install->getDatabaseExtra($FDdb, $FDfile);
			
			$sqlDiff = array (
				'update' => $this->install->getUpdateSuggestions($updateDiff),
				'remove' => $this->install->getUpdateSuggestions($removeDiff, 'remove')
			);

			return $this->getSqlStatements($sqlDiff);
		}
	}
	
	/**
	 * Builds the update Sql Query
	 *
	 * @param	array		$statement array
	 * @return	string		sql update query
	 */
	protected function getSqlStatements(array $sqlDiff) {
		$sqlStatement = null;

		foreach ($sqlDiff as $sqlDiffKey => $sqlDiffArray) {
			$sqlArray[] = $sqlDiffArray;

			if($sqlDiffKey == 'update') {
				$sqlStatement .= $this->buildAddChangeFieldsSqlQuery($sqlDiffArray);	
			} else {
				$sqlStatement .= $this->buildRemoveDropFieldsSqlQuery($sqlDiffArray);	
			}
		}
		
		return $sqlStatement;
	}
	
	/**
	 * Builds the remove and drop Sql Query
	 *
	 * @param	array		$statement array
	 * @return	string		sql remove an drop sql query
	 */	
	protected function buildRemoveDropFieldsSqlQuery(array $sqlDiffArray) {
		$sqlQuery = null;

			//drop query
		if($sqlDiffArray['drop'] || $sqlDiffArray['drop_table']) {
			$dropQuery = array();
			if($sqlDiffArray['drop']) $dropQuery['fields'] = $sqlDiffArray['drop'];
			if($sqlDiffArray['drop_table']) $dropQuery['table'] = $sqlDiffArray['drop_table'];
			
			$sqlQuery = $this->buildSqlQuery($dropQuery, 'drop');
		}
		
			//change query
		if($sqlDiffArray['change'] || $sqlDiffArray['change_table']) {
			$changeQuery = array();
			if($sqlDiffArray['change']) $changeQuery['change'] = $sqlDiffArray['change'];
			if($sqlDiffArray['change_table']) $changeQuery['table'] = $sqlDiffArray['change_table'];

			$sqlQuery .= $this->buildSqlQuery($changeQuery, 'change');
		}

		return $sqlQuery;
	}
	
	/**
	 * Builds the add and change sql query
	 *
	 * @param	array		$sqlDiffArray from t3lib_install
	 * @return	string		add and change sql string
	 */	
	protected function buildAddChangeFieldsSqlQuery(array $sqlDiffArray) {
		$sqlQuery = null;

			//add query
		if($sqlDiffArray['add'] || $sqlDiffArray['create_table']) {
			$addQuery = array();
			if($sqlDiffArray['add']) $addQuery['fields'] = $sqlDiffArray['add'];
			if($sqlDiffArray['create_table']) $addQuery['table'] = $sqlDiffArray['create_table'];

			$sqlQuery = $this->buildSqlQuery($addQuery, 'add');
		}

			//change query
		if($sqlDiffArray['change']) {
			$sqlQuery .= $this->buildChangeSqlQuery($sqlDiffArray['change'], $sqlDiffArray['change_currentValue']);
		}
		
		return $sqlQuery;
	}
	
	protected function buildChangeSqlQuery (array $changeQuery, $changeQueryDiff = array()) {
		$changeQuerySql = $this->buildSqlTitleQuery($this->lang['titles']['change']['title']);

		foreach($changeQuery as $changeQueryKey => $changeQueryValue) {
			$changeQuerySql .= '#'.$this->lang['titles']['change']['comment'].' '.$changeQueryDiff[$changeQueryKey].chr(10);
			$changeQuerySql .= $changeQueryValue.chr(10);
		}
		
		return $changeQuerySql;
	}
	
	protected function buildSqlQuery (array $addQuery, $type) {
		foreach($addQuery as $addQueryKey => $addQueryValue) {
			$addSqlQuery .= $this->buildSqlTitleQuery($this->lang['titles'][$type][$addQueryKey]);
			
			foreach($addQueryValue as $addQueryValueKey => $addQueryValueSql) {
				$addSqlQuery .= $addQueryValueSql . chr(10);
			}
			
			$addSqlQuery .= chr(10);
		}
		
		return $addSqlQuery;
	}
	
	public function adminQuery($sql) {
		$sql = str_replace(chr(10).chr(10), null, $sql);
		$res = $GLOBALS['TYPO3_DB']->admin_query($sql);
		if ($res === FALSE) {
			$result = $GLOBALS['TYPO3_DB']->sql_error();
		} elseif (is_resource($res)) {
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		
		return $result;
	}
	
	/**
	 * Builds the update Sql Title
	 *
	 * @param	string		Title
	 * @return	string		### Title ###
	 */
	protected function buildSqlTitleQuery($title) {
		$title ='
--
-- ' . $title .'
--
';
		return $title;
	}
}
?>