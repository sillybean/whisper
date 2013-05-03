<?php 
/*------------------------------------------------------------------*
dateTags 1.0 -- A plugin for Whisper
This plugin generates date-based tags for the pages on your site.
(c) 2004 Zach Shelton [http://zachofalltrades.net/] -- released GPL
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerTag('page_datetime');
plugin_registerTag('page_date');
plugin_registerTag('page_time');


/*each tag can be tailored to you preference using the appropriate 
php date formatting, or you can let the function use the default
as specified in Whisper's preferences file 
for formatting see: http://www.php.net/manual/en/function.date.php 
 */

function tagReplace_page_datetime($pageData, $replaceVar) {
	$format = $GLOBALS['formatDateTime'];
	//$format = "j F Y g:i A";
	$replaceVar = date($format, $pageData['datetime']);
}

function tagReplace_page_time($pageData, $replaceVar) {
	$format = $GLOBALS['formatTime'];
	//$format = "g:i A";
	$replaceVar = date($format, $pageData['datetime']);
}

function tagReplace_page_date($pageData, $replaceVar) {
	$format = $GLOBALS['formatDate'];
	//$format = "j F Y";
	$replaceVar = date($format, $pageData['datetime']);
}

?>