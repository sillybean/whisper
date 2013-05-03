<?php 
/*------------------------------------------------------------------*
randomQuote 1.0 -- A plugin for Whisper
This plugin allows you to maintain a list of quotes, and provides a
tag to display one at random.
(c) 2007 Stephanie Leary [http://stephanieleary.net/] -- released GPL
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerPanel('Quotes');
plugin_registerCommand('Quotes');
plugin_registerTag('random_quote');

function com_Quotes() {
	authenticate();
	$title = "Whisper";
	$subtitle = "Quotes";
	$body = pluginPanel_Quotes();
	display_interface($title, $head, $subtitle, $body);
}

function pluginPanel_Quotes() {
	$panel .= panelPart_StartBlock("Quotes"); 
	if ($_POST['quotesfield']) { // save changes
		$newquotesfile = fopen($GLOBALS['dirConfig']."randomQuotes.php", "w+");
		if (fwrite($newquotesfile, $_POST['quotesfield']) === FALSE) {
			$panel .= '<p class="message">Cannot write to file ($newquotesfile).</p>';
			exit;
    	}
		else $panel .= '<p class="message">Quotes saved.</p>';
	}
	$panel .= '<p>These are your current quotes. To include a random quote in a page, use the '.htmlspecialchars($GLOBALS['tagOpen'])."random_quote".htmlspecialchars($GLOBALS['tagClose']).' tag.</p><p>You may edit the quotes here. Place one quote per line.</p><form id="quotes" method="post" action="?command=Quotes">';
	$quotesfile = file_get_contents($GLOBALS['dirConfig']."randomQuotes.php");
	$panel .= '<p><textarea cols="80" rows="20" name="quotesfield" id="quotesfield">' . htmlspecialchars(rtrim(stripslashes($quotesfile))) . '</textarea></p>';
	$panel .= '<input type="submit" id="submit" value="Save changes" /></form>';
	$panel .= panelPart_EndBlock();
	return $panel;
}

function tagReplace_random_quote($pageData, $replaceVar) {
	$quotesfile = file($GLOBALS['dirConfig']."randomQuotes.php");
	$line = rand(0, count($quotesfile)-1);
	$quote = $quotesfile[$line];
	$replaceVar = htmlspecialchars(rtrim(stripslashes($quote)));
}
?>