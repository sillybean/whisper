<?php 
/*------------------------------------------------------------------*
Invisible 1.0 -- A plugin for Whisper
REQUIRES:
	Whisper .3
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerDbVariable('visible');

function pluginDbVarDefault_visible($returnVal) {
	//NOTE: returnVal will be passed by reference
	$returnVal = "yes";
}

function pluginDbVarValidate_visible($pageData, $checkVal) {
	//NOTE: returnVal will be passed by reference
	$checkVal = $checkVal;
}

?>