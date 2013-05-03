<?php 
/*------------------------------------------------------------------*
moreTags 1.0 -- A plugin for Whisper
This plugin generates miscellaneous tags for the pages on your site.
(c) 2004 Zach Shelton [http://zachofalltrades.net/] -- released GPL
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerTag('powered_by');
plugin_registerTag('edit_this_page');

function tagReplace_powered_by($pageData, $replaceVar) {
	$link = "Powered by: <a href=\"http://whisper.info/\">Whisper</a>";
	$replaceVar = $link;
}

function tagReplace_edit_this_page($pageData, $replaceVar) {
	$linkText = "Edit this page";
	$link = "<a href=\"?command=edit&mode=page&file=".$pageData['name']."\">$linkText</a>";
	$replaceVar = $link;
}

?>