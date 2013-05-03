<?php 
/*------------------------------------------------------------------*
Breadcrumbs 1.0 -- A plugin for Whisper
This plugin makes available tags for breadcrumb style links.
(c) 2004 Benjamin 
REQUIRES:
	Whisper .2
	parentField plugin
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerTag('breadcrumbs');


function tagReplace_breadcrumbs($pageData, $replaceVar)	{
	$bcArray = generate_breadCrumbArray($pageData["name"]);
	for($k=0; $k<=count($bcArray)-1; $k++) {
		$bcRecord =	$bcArray[$k];
		if ($bcRecord != $pageData["name"])	{
			$breadcrumbs .= get_pageLink($bcRecord);
		} else {
			$breadcrumbs .=	$bcRecord;
		}
		if ($k != count($bcArray)-1) {
			$breadcrumbs .=	" >	";
		}
	}
	$replaceVar	= $breadcrumbs;
}


function generate_breadCrumbArray($pagename){
/*No fool proof way to stop an infinite loop here, but the idea is that 
there should always be a top level page who's parent doesn't exist 
Once a proper "parent Control panel" is setup to manage the pages 
then so long as the user uses that to configure the plugin, this should 
never be an infinite loop because it will always ensure there is at least 
one top level page. */
	$pageArr = db_getPage($pagename);
	while ($pageArr) {
		$bcArray[] = $pageArr["name"];        
		$pageArr = db_getPage($pageArr["parent"]);
	}
	$bcArray = array_reverse($bcArray);
	return($bcArray);
}
?>