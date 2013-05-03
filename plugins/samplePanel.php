<?php 
/*------------------------------------------------------------------*
samplePanel 1.0 -- A plugin for Whisper
This plugin demonstrates how to register and create a panel.
(c) 2004 Zach Shelton [http://zachofalltrades.net/] -- released GPL
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerPanel('sample');



function com_sample() {
	authenticate();
	$title = "Whisper";
	$subtitle = "Plugin Demo";
	$body = pluginPanel_sample();
	display_interface($title, $head, $subtitle, $body);
}


function pluginPanel_sample() {
	$panel .= panelPart_StartBlock("Sample Plugin Panel");
	$panel .= "<p>This is just a sample.</P><P>How about we show a list of the files in your plugins directory?</P>";
	$dir = opendir($GLOBALS['dirPlugins']);
	$fileCount = 0;
	while($fileName = readdir($dir)) { 
		if ($fileName{0} == "." ) continue; //skip curDir and parentDir, and any unix 'hidden' file
		$panel .= "<br />$fileName";
		$fileCount++;
	}
	closedir($dir);
	$panel .= "<p><a href='?command=samplePreference'>Here</a> is a link to a sample preferences page.</P>";
	$panel .= panelPart_EndBlock();
	return $panel;
}

?>