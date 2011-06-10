<?php

########################################################################
# Extension Manager/Repository config file for ext "cliinstall".
#
# Auto generated 10-06-2011 10:49
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'cliinstall',
	'description' => 'Adds the possibility to update the table definitions and purge the cache by CLI script.',
	'category' => 'plugin',
	'author' => 'Michael Birchler, snowflake productions gmbh',
	'author_email' => 'mbirchler@snowflake.ch',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.2.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:5:{s:9:"ChangeLog";s:4:"292d";s:12:"ext_icon.gif";s:4:"b744";s:17:"ext_localconf.php";s:4:"ec5b";s:41:"classes/class.tx_cliinstall_dbcompare.php";s:4:"f9ad";s:31:"cli/class.tx_cliinstall_cli.php";s:4:"92b3";}',
	'suggests' => array(
	),
);

?>