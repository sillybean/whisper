<?php 
/*------------------------------------------------------------------*
Validator 1.0 -- A plugin for Whisper
This plugin provides assisatnce for creating sites that are consistent
with HTML and CSS validation rules.
(c) 2004 Zach Shelton [http://zachofalltrades.net/] -- released GPL
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerPanel('Validator');

function com_Validator() {
	authenticate();
	$title = "Whisper";
	$subtitle = "HTML and CSS Validation";
	$body = pluginPanel_Validator();
	display_interface($title, $head, $subtitle, $body);
}

function pluginPanel_Validator() {
	$CSSvalidator = "http://jigsaw.w3.org/css-validator/validator?uri=";
	$HTMLvalidator = "http://validator.w3.org/check?uri=";
	$LinkChecker = "http://validator.w3.org/checklink?hide_type=all&uri=";
	$localPage = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]."?";
	$mypath = "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER['PHP_SELF']);
	$mypath = str_replace($GLOBALS['this_script'], '', $mypath); 
	$layoutpath = $GLOBALS['dirLayout'];
	$layoutpath = str_replace(".", '', $layoutpath); 
	$layoutpath = $mypath.$layoutpath;
	$stylePath = $layoutpath."default.css";

	$panel .= panelPart_StartBlock("HTML and CSS Validation");
	$panel .= "<br />The links on this page will open new windows and request <a href='http://www.w3schools.com/css/'>CSS</a> and <a href='http://www.w3schools.com/html/'>HTML</a> validation checks using free online utilities from <a href='http://www.w3c.org'>W3C</a>.";
	$panel .= panelPart_EndBlock();

	$panel .= panelPart_StartBlock("Stylesheet");
	$panel .= "<a href='".$CSSvalidator.$stylePath."'>Check</a> to verify your site's stylesheet is valid.";
	$panel .= panelPart_EndBlock();

	$panel .= panelPart_StartBlock("Pages");
	$panel .= "<br />Note that hidden pages can not be validated, because the validator will not be able to log in to this site.";
	global $dbRecordsArray;
	$panel .= "<table cellspacing=0>\n<tr>";
	$panel .="<th>Page Name</th>";
	$panel .="<th>HTML Validation</th>";
	$panel .="<th>Link Checker</th>";
	$panel .="</tr>";
	for ($k=0; $k<=count($dbRecordsArray)-1; $k++) {
		$dbRecord = null;
		$dbRecord = $dbRecordsArray[$k];
		if ($dbRecord['showhide'] == 'show') {
			$bgc = ($k % 2 ? 'dark' : 'light');
			$panel .='<tr class="'.$bgc.'">';
			$panel .="<td>".stripslashes($dbRecord['name'])."</td>";
			$panel .="<td><a target='_blank' href = '".$HTMLvalidator.$localPage.$dbRecord['name']."'>Validate</a></td>";
			$panel .="<td><a target='_blank' href = '".$LinkChecker.$localPage.$dbRecord['name']."'>Check</a></td>";
			$panel .="</tr>";
		}
	}
	$panel .= "</table>";
	$panel .= panelPart_EndBlock();

	$panel .= panelPart_StartBlock("Templates");
	$panel .= "<br />Templates should validate, since they are actual HTML documents -- but you can ignore any warnings that reference Whisper's search and replace tags.";
	global $dbRecordsArray;
	$panel .= "<table cellspacing=0>\n<tr>";
	$panel .="<th>Template Name</th>";
	$panel .="<th>HTML Validation</th>";
	$panel .="<th>Link Checker</th>";
	$panel .="</tr>";
	$dir = opendir($GLOBALS['dirLayout']);
	$fileCount = 0;
	while($fileName = readdir($dir)) { 
		if ($fileName{0} == "." ) continue; //skip curDir and parentDir, and any unix 'hidden' file
		if (substr($fileName, -4) == ".css" ) continue; //skip css files
		$bgc = ($fileCount % 2 ? 'dark' : 'light');
		$panel .='<tr class="'.$bgc.'">';
		$panel .="<td>".$fileName."</td>";
		$panel .="<td><a target='_blank' href = '".$HTMLvalidator.$layoutpath.$fileName."'>Validate</a></td>";
		$panel .="<td><a target='_blank' href = '".$LinkChecker.$layoutpath.$fileName."'>Check</a></td>";
		$panel .="</tr>";
		$fileCount++;
	}
	closedir($dir);
	$panel .= "</table>";
	$panel .= panelPart_EndBlock();

	return $panel;
}

?>