<?php 
/*------------------------------------------------------------------*
Page List 3.0 - A plugin for Whisper
This plugin generates lists of the pages on your site.
2.0 (c) 2004 Adam Newbold [http://www.neatnik.net/]
Reimplemented for plugin API by Zach Shelton [http://zachofalltrades.net]
Updated for Whisper 0.3 by Stephanie Leary
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerTag('archive');
plugin_registerTag('archive_short');

function tagReplace_archive($pageData, $return) {
	$archive = getList();
	$return = $archive;
}

function tagReplace_archive_short($pageData, $return) {
	$archive = getList();
	$archive_short_array = explode("\n", $archive);
	$archive_short_array = array_slice($archive_short_array, 0, 5);
	$archive_short = implode('', $archive_short_array);
	$return = $archive_short;
}

function getList() {
	global $dbRecordsArray;
	foreach ($dbRecordsArray as $dbRecord) {
		if (get_ShowHide($dbRecord['showhide'])!='hide') {
			$archive .= get_pageLink($dbRecord['name']); 	
		}
	}
	return $archive;
}
?>