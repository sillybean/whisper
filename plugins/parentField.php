<?php 
/*------------------------------------------------------------------*
parentField 1.0 -- A plugin for Whisper
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerDbVariable('parent');

function pluginDbVarDefault_parent($returnVal) {
	//NOTE: returnVal will be passed by reference
	$returnVal = "none";
}

function pluginDbVarValidate_parent($pageData, $checkVal) {
	//NOTE: returnVal will be passed by reference
	$checkVal = $checkVal;
}

?>