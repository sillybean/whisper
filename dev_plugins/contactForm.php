<?php 
/*------------------------------------------------------------------*
ContactForm 0.1 -- A plugin for Whisper
This plugin demonstrates how to register and create a panel.
Based on phpAkismetContact, http://code.google.com/p/php-akismet-contact/
@copyright Jesse Newland, {@link http://jnewland.com/ http://jnewland.com/}
Adapted for Whisper by Stephanie Leary (http://stephanieleary.net)
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/
// Based on:
/**
 * @package phpAkismetContact
 * @author Jesse Newland, {@link http://jnewland.com/ http://jnewland.com/}
 * @version 0.1
 * @copyright Jesse Newland, {@link http://jnewland.com/ http://jnewland.com/}
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
 
// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerPanel('contactForm');
plugin_registerTag('contact_form');
plugin_registerCommand('contactPreferences');

function com_contactPreferences() {
	com_editConfig("contactFormPreferences.php"); //this file must exist in the configuration directory, and must be writable!
}


function com_contactForm() {
	authenticate();
	$title = "Whisper";
	$subtitle = "Contact Form";
	$body = pluginPanel_contactForm();
	display_interface($title, $head, $subtitle, $body);
}


function pluginPanel_contactForm() {
	$panel .= panelPart_StartBlock("Contact Form");
	$panel .= "<p>This plugin allows people to send you email. To display the contact form in a page, use the ". htmlspecialchars($GLOBALS['tagOpen']).
		"contact_form".htmlspecialchars($GLOBALS['tagClose'])." tag.</p>";
	$panel .= "<p><a href='?command=contactPreferences'>Edit the contact form options.</a></P>";
	$panel .= panelPart_EndBlock();
	return $panel;
}

function tagReplace_contact_form($pageData, $replaceVar) {	
	/**
	 * Configuration Options
	 */
	 
	//change this path if you need to
/*	require($GLOBALS['dirConfig'].'Akismet.class.php');
	$website_URL = $siteURL; //  Site URL // REQUIRED - website the contact form is being sumbitted from - 
							// if the form is located at http://www.test.com/contact, then this is http://www.test.com
	$referrers = array("whisper");  // Allowed referrers // REQUIRED - allowed referrers. POSTs coming from any other domain will be disallowed 
	include($GLOBALS['dirConfig']."contactFormPreferences.php");
	
	if (!empty($_POST['name'])){
		/**
		 * Here we go......
		 */
/*		//cleanup post parameters
		$post = array();
		$crack = false;
		foreach ($_POST as $key => $val) {
			//stripslashes
			$post[$key] = stripslashes($_POST[$key]);
			//if there is a newline followed by an email command, reject it.
			$crack = eregi("(%0A|%0D|\n+|\r+)(content-type:|to:|cc:|bcc:)",$post[$key]);
			if ($crack) {
				$contact_form .= error("Invalid input detected.");
			} // if ($crack)
		} // foreach
		//verify nothing is in the bcc field
		if (!empty($post["bcc"])) {
			$contact_form .= error("Invalid input detected");
		}
		
		//verify the sender as a valid email
		if (!valid_email($post["email"])) {
			$contact_form .= error("Sender is invalid");
		}
		
		//verify the referer is allowed
		if (!valid_referer($referrers)) {
			$contact_form .= error("Referrer is incorrect");
		}
		
		if (!empty($post["redirect"])) {
			$redirect = "Location: " . $post["redirect"];
		} else {
			$redirect = "Location: " . $_SERVER['HTTP_REFERER'];
		}
		
		$addlHeaders = "From: " . $post["email"] . "\n" .
					   "Reply-To: " . $post["email"] . "\n";
		
		//unset a couple vars we don't want in the email
		unset($post["redirect"]);
		unset($post["recipient"]);
		unset($post["bcc"]);
		
		//generate the message
		$message = "";
		foreach ($post as $key => $value) {
			$message .= ucfirst($key) . ": " . $value . "\n\n";
		} // foreach
		$message = wordwrap($message, 70, "\n");
		
		if (!empty($wordpress_API_key)) {
			//setup akismet
			$akismet = new Akismet($website_URL, $wordpress_API_key);
			$akismet->setAuthor($post["name"]);
			$akismet->setAuthorEmail($post["email"]);
			$akismet->setContent($post["comments"]);
			$akismet->setType("contact_form");
		}
		
		//submit to akismet for validation
		if($akismet->isSpam()) {
			$contact_form .= error("Your message was marked as spam by <a href='http://akismet.com'>Akismet</a>. Please try again.");
		} else {
			//akismet passed, send the email
			if (mail($recipient,"Contact Form Submission",$message,$addlHeaders)) {
				header($redirect);
				$contact_form .= $successMsg;
			} else {
				$contact_form .= error("Error sending your message");
			} // if (mail)
		} // if ($akismet)	
	} // if (!empty($_POST['name']))
	else {
*/		$contact_form .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">
	  <input type="hidden" class="hidden" name="redirect" value="'.$_SERVER['REQUEST_URI'].'">
	  <p><label for="name">Name</label>
	  <input class="text" maxlength="40" type="text" size="18" name="name" value="Name" /></p>
	  <!-- spam bait - if the bcc field contains anything, this email is rejected -->
	  <!--<label for="bcc">Bcc</label>-->
	  <!--<input class="text" maxlength="40" type="text" size="18" name="bcc" value="" />-->
	  <p><label for="email">Email</label>
	  <input class="text" maxlength="80" type="text" size="18" name="email" value="" /></p>
	  <p><label for="comments">Message</label>
	  <textarea name="comments" cols="15" rows="4" type="text"></textarea></p>
	  <p><input type="submit" value="Send"></p>
	</form>'; 
//	}	
	$replaceVar = $contact_form;
}

/**
 * Functions
 */
// validate referrer
function valid_referer($referers) {	
	if(!empty($_SERVER['HTTP_REFERER'])) {
		$referer = end(array_slice(explode("/", $_SERVER['HTTP_REFERER']), 2, 1));
		foreach ($referers as $valid_referer) {
				if($valid_referer == $referer) {
				return true;
				}
		}
	}
	return false;
}

//validate email
function valid_email($email){
	return preg_match("/^[A-Z0-9._%-\+]+@[A-Z0-9.-]+.[A-Z]{2,4}$/i", $email);
}

function error($error){
	$html = "<p class=\"error\">".$error."</p>";
	$ip = $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR') ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
	error_log("[client ". $ip ."] php-akismet-contact: " . $error);
	return $html;
}
?>