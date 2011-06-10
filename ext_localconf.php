<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
    //register cli
if (TYPO3_MODE=='BE')    {
    $TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array('EXT:'.$_EXTKEY.'/cli/class.tx_cliinstall_cli.php','_CLI_cliinstall');
}
?>