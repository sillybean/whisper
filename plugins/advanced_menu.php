<?php 
/*------------------------------------------------------------------*
Advanced Menu 1.0 -- A plugin for Whisper
This plugin makes available tags for a main and sub menu for your site.
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

plugin_registerTag('main_menu');
plugin_registerTag('sub_menu');
plugin_registerPanel('advancedMenu');
plugin_registerCommand('menuPreference');

function com_menuPreference() {
	com_editConfig("menuPreferences.php"); //this file must exist in the configuration directory, and must be writable!
}

function com_advancedMenu() {
	authenticate();
	$title = "Whisper";
	$subtitle = "'Advanced Menu' Plugin Panel";
	$body = pluginPanel_advancedMenu();
	display_interface($title, $head, $subtitle, $body);
}

function pluginPanel_advancedMenu()
{
    include($GLOBALS['dirConfig']."menuPreferences.php");
    $panel .= panelPart_StartBlock("'Advanced Menu' Plugin Configuration");
    $panel .= "<H3>Main Menu Tag:</H3>";
    $panel .= "<p>Your current settings for the main_menu tag are:</p>";
    $panel .= "<blockquote><pre><code>HTML Placed before the menu items:\t".htmlspecialchars($mmStart)."<br />";
    $panel .= "HTML Placed before the link item:\t".htmlspecialchars($mmBeforeTag)."<br />";
    $panel .= "HTML Placed after the link item:\t".htmlspecialchars($mmAfterTag)."<br />";
    $panel .= "HTML Placed after the menu items:\t".htmlspecialchars($mmEnd)."</code></pre></blockquote>";
    $panel .= "<p>Here is an example of how this will look:</p>";
    $panel .= "<blockquote>$mmStart $mmBeforeTag <a href=#>Link1</a> $mmAfterTag $mmBeforeTag <a href=#>Link2</a> $mmAfterTag $mmBeforeTag <a href=#>Link3</a> $mmAfterTag $mmEnd</blockquote>";
    $panel .= "<H3>Sub Menu Tag:</H3>";
    $panel .= "<p>Your current settings for the sub_menu tag are:</p>";
    $panel .= "<blockquote><pre><code>HTML Placed before the menu items:\t".htmlspecialchars($smStart)."<br />";
    $panel .= "HTML Placed before the link item:\t".htmlspecialchars($smBeforeTag)."<br />";
    $panel .= "HTML Placed after the link item:\t".htmlspecialchars($smAfterTag)."<br />";
    $panel .= "HTML Placed after the menu items:\t".htmlspecialchars($smEnd)."</code></pre></blockquote>";
    $panel .= "<p>Here is an example of how this will look:</p>";
    $panel .= "<blockquote>$smStart $smBeforeTag <a href=#>Link1</a> $smAfterTag $smBeforeTag <a href=#>Link2</a> $smAfterTag $smBeforeTag <a href=#>Link3</a> $smAfterTag $smEnd</blockquote>";
    	$panel .= "<br /><p><a href='?command=menuPreference'><strong>Click here to modify any of these settings.</strong></a></p>";
	$panel .= panelPart_EndBlock();
	return $panel;
}

function tagReplace_main_menu($pageData, $replaceVar) {
	global $dbRecordsArray;
	include($GLOBALS['dirConfig']."menuPreferences.php");
	$mainMenu .= $mmStart;
	for ($k=0; $k<=count($dbRecordsArray)-1; $k++){
		unset ($dbRecord);
		$dbRecord = $dbRecordsArray[$k];
		if (!db_getPage($dbRecord["parent"])) {

/*			if ($dbRecord['showhide'] == "show") {
				$mainMenu .= $mmBeforeTag.get_pageLink($dbRecord['name']).$mmAfterTag;
			} elseif (($dbRecord['showhide'] == "hide") and ($_SESSION['logged_in']==true)) {
				$mainMenu .= $mmBeforeTag.get_pageLink($dbRecord['name']).$mmAfterTag;
			}
*/
			if (get_ShowHide($dbRecord['showhide'])!='hide')	
				$mainMenu .= $mmBeforeTag.get_pageLink($dbRecord['name']).$mmAfterTag;
		
		}
	}
	$mainMenu .= $mmEnd;
	$replaceVar = $mainMenu;
}

function tagReplace_sub_menu($pageData, $replaceVar){
	global $dbRecordsArray;
	include($GLOBALS['dirConfig']."menuPreferences.php");
	$subMenu .= $smStart;
	for ($k=0; $k<=count($dbRecordsArray)-1; $k++) {
		unset ($dbRecord);
		$dbRecord = $dbRecordsArray[$k];
		if ($dbRecord['parent'] == $pageData['name']) {
			$subMenu .= $smBeforeTag;
			if ( $dbRecord['showhide'] == "hide" and isset($_SESSION['logged_in'])) {
				$subMenu .= get_pageLink($dbRecord['name']);
				$subMenu .= "(hidden)";
			} elseif ($dbRecord['showhide'] == "show") {
				$subMenu .= get_pageLink($dbRecord['name']);
			}
			$subMenu .= $smAfterTag;
		}
	}
	$subMenu .= $smEnd;
	$replaceVar = $subMenu;
}
?>