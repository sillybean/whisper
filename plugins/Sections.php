<?php 
/*------------------------------------------------------------------*
Sections 2.0 -- A plugin for Whisper
This plugin adds the ability to create sections within a single page.
Each section correspnds to a separate page.
(c) 2004 Zach Shelton [http://zachofalltrades.net/] -- released GPL
REQUIRES:
	Whisper .3
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerTag('sections');
plugin_registerTag('reverse_sections');
plugin_registerDbVariable('sections');
plugin_registerPanel('Sections');

function pluginDbVarDefault_sections($returnVal) {
	//NOTE: returnVal will be passed by reference
	$returnVal = "";
}

function pluginDbVarValidate_sections($pageData, $checkVal) {
	//check to be sure that each section actually corresponds to another page 
	if ( $checkVal != '') {
		$sections = explode(';', $checkVal);
		for ($k=0; $k<=count($sections)-1; $k++) {
			if ($sections[$k] == $pageData['name']) {
				unset($sections[$k]); //page can not contain itself as a section
			}
			$checkSection = null;
			$checkSection = db_getPage($sections[$k]);
			if ($checkSection == false) {
				unset($sections[$k]); //section must already exist as another content page
			} else {
				if ($checkSection['sections'] != '') {
					unset($sections[$k]); //section can not be a page with more sections
				}
			}
		}
		$checkVal = implode(';', $sections);
	} else {
		$checkVal = '';
	}
}


function tagReplace_sections($pageData, $replaceVar) {
	$renderedSection = "";
	if ( $pageData['sections'] != '') {
		$sections = explode(';', $pageData['sections']);
		for ($k=0; $k<=count($sections)-1; $k++) {
			$thisSection = null;
			$thisSection = db_getPage($sections[$k]);
			if (($thisSection != false) && ($thisSection['visible'] == "yes")) {
				$renderedSection .= "\n<div class='section'>\n";
				$renderedSection .= renderPage($thisSection['name']);
				$renderedSection .= "\n</div>\n";
			}
		}
	}
	$replaceVar = $renderedSection;
}

function tagReplace_reverse_sections($pageData, $replaceVar) {
	$renderedSection = "";
	if ( $pageData['sections'] != '') {
		$sections = explode(';', $pageData['sections']);
		for ($k=count($sections)-1; $k>=0; $k--) {
			$thisSection = null;
			$thisSection = db_getPage($sections[$k]);
			if (($thisSection != false) && ($thisSection['visible'] == "yes")) {
				$renderedSection .= "\n<div class='section'>\n";
				$renderedSection .= renderPage($thisSection['name']);
				$renderedSection .= "\n<p class=\"meta\">Posted on ".date("F j, Y", (filectime($GLOBALS['dirContent'].$thisSection['name']) + $GLOBALS['timeDiff']))."</p>\n";
				$renderedSection .= "\n</div>\n";
			}
		}
	}
	$replaceVar = $renderedSection;
}

function com_Sections() {
	authenticate();
	$title = "Whisper";
	$subtitle = "Help with 'Sections'";
	$body = pluginPanel_Sections();
	display_interface($title, $head, $subtitle, $body);
}


function pluginPanel_Sections() {
	$panel .= panelPart_StartBlock("How to use 'Sections'");
	$panel .= "<p>'Sections' are basically pages within pages. They could be paragraphs, blocks, or anything you want.</p>";
	$panel .= "<P>Each section goes through the same rendering logic that a normal page does, meaning that each section can have its own template, and its own markup parser, as well as the full selection of core and plugin tags. There is validation logic to prevent you from creating a circular reference where a page contains itself as a section. Sections are also limited (for now) to one level. You can not have a page as a section if that page has sections of its own.</p>";
	$panel .= "<p>Go to the <a href='?command=editPluginDbVars'>plugin variables</a> screen to to enter sections names for any given page. Sections should be listed by page name (case matters), and should be a list separated by semi-colons ';' without any additional spacing.</P>";
	$panel .= "<p>The tag to use on the parent page into which you want to insert sections is ".htmlspecialchars($GLOBALS['tagOpen'])."sections".htmlspecialchars($GLOBALS['tagClose'])."</p>";
	$panel .= panelPart_EndBlock();
	return $panel;
}

?>