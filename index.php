<?php
//phpinfo();
/*-----------------------------------------------------------------------------
Whisper - quiet content management
Whisper is copyright (c) 2004 by Adam Newbold
http://www.neatnik.net

Re-coded for security and code modularity by Zach Shelton, May-June 2004
http://zachofalltrades.net

Resurrected November 2007 by Stephanie Leary
http://stephanieleary.net

Version 0.3

This file is part of Whisper.
Whisper is free software; you can redistribute it and/or 
modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation.
http://www.gnu.org/licenses/

Whisper is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty 
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public 
License along with Whisper; if not, write to:
Free Software Foundation, Inc.
59 Temple Place, Suite 330
Boston, MA  02111-1307
USA

Whisper uses Textile.
Textile - A Humane Web Text Generator
Copyright (c) 2003-2004, Dean Allen. All rights reserved.
-----------------------------------------------------------------------------*/

/*----------------------------------------------------------------------------
There are only two variables that NEED to be adjusted in this file (just below).
Most other settings can be changed from within the control panel.
-----------------------------------------------------------------------------*/
$username = "steph";   // Your user name
$password = "steph";   // Your password

/*-----------------------------------------------------------------------------
You can make Whisper somewhat more secure by using a separate password file 
that is located outside of your server's web root. To enable this feature:
1. set $SecurePass to true
2. set your password in whisperX.php
3. move whisperX.php to a location outside of the web doc root.
4. provide the _full machine path_ to the file (you can even rename the file)
------------------------------------------------------------------------------*/
$SecurePass = false; 
$SecurePassFilePath = "/home/not_public_html/whisperX.php"; 

/*------------------------------------------------------------------------------
ADDITIONAL SECURITY NOTES
--The '.htaccess' file is used by Apache web servers. This file will prevent the 
server from delivering directory listings of the root folder and the various 
subdirectories. In the absence of further security measures, this provides a
minimum level of protection. In order to use this protection, you need to remove
the word "optional" from the filename, so that the file name is just ".htaccess"
Note that you may not be able to see the file in some FTP clients, because files 
that begin with periods are "hidden" in linux/unix filesystems.
--Writable directories: The content, configuration, and templates directories
must be writable in order for this script to function. This is inherently 
insecure. However, these directories can be made more secure by blocking _web_ 
access. This can be done through .htpasswd or "web protect".
Blocking the web server from delivering content from thse directories does not 
block the script from reading and writing to these paths (or anyone else that 
has access to the filesystem on the host machine). So long as php can get 
through, it does not matter if you can not reach them with a web browser -- this
is the recommended method of securing your site content if you want to make use of
the "hidden" pages feature. In order to use file generation, the directory in which 
files will be generated must also be writable, but can not be protected. This may
be unacceptable to some security conscious webmasters.
--Note that when you use "file generation", you actually end up with two copies
of each content file. One, is a "source" file that is saved in your content
directory. This is the file that you actually see and edit in the edit window.
The second is the "generated" file that is merged with the selected template. 
--"?debug" allows you to get a great deal of information about the web 
server and its configuration. This information might be useful to a hacker.
You can require a login before this function will be allowed. If you are 
having trouble with your site, you may want to temporarily set this to true 
so that you can get the information for troubleshooting purposes.
-----------------------------------------------------------------------------*/
$allowAnonymousDebug = false;

/*-----------------------------------------------------------------------------
You should not need to adjust the following variables, but you could if you 
wanted to make life more difficult for potential hackers...
Note: The directory and file paths are _relative_ to the path for this script!
All path variables in this script must end with a trailing slash.
If you don't understand the syntax for relative paths in web documents, it is 
probably best that you not change these. Better security can be had through other 
methods (read the 'Security Notes' above).
------------------------------------------------------------------------------*/
$dirContent = "./content/";                //must be writable by php (CHMOD 777)
$dirLayout = "./layout/";                  //must be writable by php (CHMOD 777)
$dirConfig = "./configuration/";           //must be writable by php (CHMOD 777)
$dirUploads = "./uploads/";                //must be writable by php (CHMOD 777)
$dirPlugins = "./plugins/";                //can be left with 'normal' permissions
$fileNamePrefs = "preferences.php"; 
$fileNameDatabase = "whisperDB";  

/*-----------------------------------------------------------------------------
You only need to change these next two variables if you're running PHP 
through CGI (and even then you probably won't need to change them)
-----------------------------------------------------------------------------*/
$this_script = "index.php"; // The name of this file (usually index.php)
$php_cgi = "php.cgi"; // The name of the cgi file under which PHP runs

/*-----------------------------------------------------------------------------
END OF USER CONFIGURATION
Nothing should be edited beyond this point.
-----------------------------------------------------------------------------*/
// error_reporting(E_ALL);
// error_reporting(0);
// session_cache_limiter('private');
session_start();
$version = "0.3"; 
$serial = "1039";
$filePathPrefs = $dirConfig.$fileNamePrefs; 
$filePathDatabase = $dirConfig.$fileNameDatabase;  
$sep = ', ';
$sep2 = '&nbsp;&#187;&nbsp;';
$dbSeparator = ";";
$tagOpen = "<!";
$tagClose = "/>";
$fileGenSpaceReplace = "_"; 

require ($filePathPrefs);
$timeDiff = 60 * 60 * $timezoneOffset; //seconds/min * minutes/hr * hour offset

// These next lines allow Whisper to run with PHP through CGI
$script_file_name = basename($_SERVER['SCRIPT_FILENAME']);
if ( $script_file_name == $php_cgi ) { $script_file_name = $this_script; }

global $coreTags, $coreCommands, $pluginTags, $pluginPanels, 
$pluginCommands, $pluginMarkupParsers, $pluginDbVars;
core_Load();
plugins_Load();
db_LoadDatabase();
processRequest();
exit;

function processRequest() {
	if (isset($_GET['command'])) {
		$command=$_GET['command'];
		//all command functions require authentication, except the login check itself
		if ($command=='checkLogin') { com_checkLogin(); }
		authenticate();
		global $coreCommands, $pluginCommands;
		if (is_array($pluginCommands) == true) {
			$allCommands = array_merge($coreCommands, $pluginCommands);
		} else {
			$allCommands = $coreCommands;
		}
		//look for any command to support the request
		for ($k=0; $k<=count($allCommands)-1; $k++) {
			if ( $allCommands[$k] == $command ) {
				if (function_exists("com_$command") == true) {
					call_user_func("com_$command");
					exit;
				} else {
					break; // exit the for loop
				}
			}
		}
		//no core or plugin command was found to support the request
		err_unsupported("processRequest-->CommandsArray($command)");
	} else {
		$pageRequest = get_pageRequest();
		switch ($pageRequest) {
			case "panel":
				com_panel();
				break;
			case "debug":
				com_debug();
				break;
			default:
				display_content($pageRequest);
		}//end switch
	}//end If isset 'command'
}

/*-------------------------- COMMAND functions --------------------------------
Each possible 'command' has a corresponding function in this section. These 
functions may or may not use additional helper functions to carry out their task.
-----------------------------------------------------------------------------*/

// Log users out of the control panel (?command=logout)---------
function com_logout() {
	session_unset(); 
	session_destroy();
	authenticate();
}

function com_checkLogin() {
/* This function should only be called as a response to submission of the login form.
It should not be called for any other reason. For authenticating before proceeding with 
other funcitons, the authenticate() function should be called to verify admin access. */
		//make sure that this is a recent submission (not a re-post from a cached form)
		$timeStamp = gettimeofday();
		$checkTime = $timeStamp['sec'];
		$promptTime = $_POST['PromptTime'];
		$timeLimit = 120; //seconds allowed between time prompt is given and time for submission
		if (($checkTime - $promptTime) > $timeLimit) {
			$_SESSION['loginMessage'] = "Too much time has elapsed since login prompt, please try again.";
			display_login($_POST['pageName']);
		}
	if ($GLOBALS['SecurePass'] == true) {
		//option to use alternate password file from secure location
		if (file_exists($GLOBALS['SecurePassFilePath']) == true) {
			require_once($GLOBALS['SecurePassFilePath']);
		} else {
			//password file unavailable, so just deny them
			$_SESSION['loginMessage'] =  "Password Configuration Error.";
			display_login($_POST['pageName']);
		}
	}
	if (($_POST['input_username'] == $GLOBALS['username']) and ($_POST['input_password'] == $GLOBALS['password'])) { 
		$_SESSION['logged_in'] = true;
		$_SESSION['lastAuthTime'] = $checkTime;
		if ($_POST['pageName'] != '') {
			display_content($_POST['pageName']);
			exit;
		} else {
			com_panel();
		}
	} else {
		$_SESSION['logged_in'] = false;
		$_SESSION['loginMessage'] =  "Invalid username and password.";
		display_login($_POST['pageName']);
	}
}


//-------- provide environment information for debug purposes---
function com_debug(){
	if ($allowAnonymousDebug != true) { 
		authenticate(); 
	}
	display_debug();
}

function com_newDefault() {
	set_default($_GET['page']);
	com_panel();
}

// Display the control panel (?command=panel or ?panel)---------
function com_panel() { 
	authenticate();
	$title = "Whisper";
	$subtitle = "Control Panel";
	$body .= panel_CreateNewPage();
	if ($GLOBALS['viewPageTable']=='on') {
		$body .= panel_PageTable();
	} else {
		$body .= panel_IndividualPages();
	}
	if ($GLOBALS['viewTemplates']=='on') {
		$body .= panel_Templates();
	}
	$body .= panel_Plugins();
	$body .= panel_SiteManagement();
	$body .= panel_SiteInformation();
	display_interface ($title, $head, $subtitle, $body);
}

// Display the user manual (?command=manual or ?manual)----------
function com_manual() { 
	authenticate();
	$title = "Whisper";
	$body .= panel_Manual();
	$body .= panel_SiteInformation();
	$subtitle = "Manual";
	display_interface ($title, $head, $subtitle, $body);
}


// Check for an application update (?command=version)------------
function com_checkVersion() {
	authenticate();
	$latest_serial = implode('', file('http://purl.oclc.org/NET/whisper-serial'));
	if ( $GLOBALS['serial'] < $latest_serial ) { 
		$_SESSION['message'] .= 'A new version of Whisper might be available.  <a href="http://purl.oclc.org/NET/whisper-download">Click here for details.</a>'; 
		backToPanel(); 
	}
	if ( $GLOBALS['serial'] >= $latest_serial ) { 
		$_SESSION['message'] .= 'You are running the latest version of Whisper.'; 
		backToPanel(); 
	}
}

function com_templates () {
/* manage templates */
	$template = $_POST['templateName'];
	$mode = $_POST['mode'];
	switch ($mode) {
		case "delete":
			if ($template == "default" || $template == "none") {
				backToPanel("Removal of '".$template."' is not allowed."); 
			} else {
				$file_to_delete = $GLOBALS['dirLayout'].$template.".html"; 
				if (file_exists($file_to_delete)) { 
					unlink($file_to_delete); 
					backToPanel("Template '".$template."' was deleted."); 
				} else {
					backToPanel("Template '".$template."' not found in layout directory."); 
				}
			}
			break;
		case "edit":
			com_editFile($template, 'template');
			break;
		default:
			err_unsupported("com_templates ($template, $mode)");
	}
}

// Access the database management utility (?command=database)---
function com_database() { 
	authenticate();
	$title = "Whisper";
	$subtitle = "Database";
	$body = "<p>You can manage your database here.  Rename a page by simply changing its name, modify its creation date and time, mark it as hidden, or delete it (permanently).</p>";
	$body .= panelPart_CreatePage();
	$body .= panelPart_PageTable();
	display_interface($title, $head, $subtitle, $body);
}

function com_editPluginDbVars() {
	authenticate();
	$title = "Whisper";
	$subtitle = "Plugin Database Variables";
	$body = panelPart_PluginDbVars();
	display_interface($title, $head, $subtitle, $body);
}

function com_viewTags() {
	authenticate();
	$title = "Whisper";
	$subtitle = "Search and Replace Tags";
	$body = panel_TagList();
	display_interface($title, $head, $subtitle, $body);
}

function com_manageTemplates() { 
	authenticate();
	$title = "Whisper";
	$subtitle = "Templates";
	$body = panel_Templates();
	display_interface($title, $head, $subtitle, $body);
}

function com_updateDB() {
/* This function takes the form input from the database panel and batch
processes many changes at once. */
	authenticate();
	global $dbRecordsArray;
	$doDBupdate=false;

/* NAME changes: Parse database, comparing each database line to 
form input for pages that need to have their names changed */
	$dbRecordCounter = 0;
	while ( $dbRecordCounter < count($dbRecordsArray) ) { 
		$dbRecord = $dbRecordsArray[$dbRecordCounter];
		$nameCurrent = $dbRecord['name']; 
		$namePosted = $_POST["page_name_$dbRecordCounter"];
		if ( ($namePosted != $nameCurrent) && ($namePosted !='') ) {
			rename($GLOBALS['dirContent'].$nameCurrent, $GLOBALS['dirContent'].$namePosted);
			$dbRecord['name'] = $namePosted;
			$dbRecordsArray[$dbRecordCounter] = $dbRecord;
			$doDBupdate = true;
			$_SESSION['message'] .= "Name of '".$nameCurrent."' changed to '".$namePosted."'<br />";
			if ( $nameCurrent = $GLOBALS['defaultPageName'] ) {
				//update default page setting
				set_default($namePosted);
			}
		} 
	$dbRecordCounter++; 
	}

/* DATETIME changes */
	$dbRecordCounter = 0;
	while ( $dbRecordCounter < count($dbRecordsArray) ) { 
		$dbRecord = $dbRecordsArray[$dbRecordCounter];
		$datetimeCurrent = $dbRecord['datetime']; 
		$datetimePosted = $_POST["datetime_$dbRecordCounter"];
		$convertPosted = strtotime($datetimePosted);
		if ( $datetimeCurrent != $convertPosted ) {
			if ($datetimePosted == -1) {
				$_SESSION['message'] .= "DateTime of '".$dbRecord['name']."' was not changed becasue of invalid time format.<br />";
			} else {
				$dbRecord['datetime'] = $convertPosted;
				$dbRecordsArray[$dbRecordCounter] = $dbRecord;
				$doDBupdate = true;
				$_SESSION['message'] .= "DateTime of '".$dbRecord['name']."' changed to '".$datetimePosted."'<br />";
			}
		} 
	$dbRecordCounter++; 
	}

/* SHOW/HIDE Pages */
	$dbRecordCounter = 0;
	while ( $dbRecordCounter < count($dbRecordsArray) ) { 
		$dbRecord = $dbRecordsArray[$dbRecordCounter]; 
		$ShowHideCurrent = $dbRecord['showhide']; 
		$ShowHidePosted = get_ShowHide($_POST["ShowHide_$dbRecordCounter"]);
		if ( $ShowHideCurrent != $ShowHidePosted ) {
			$dbRecord['showhide'] = $ShowHidePosted;
			$dbRecordsArray[$dbRecordCounter] = $dbRecord;
			$doDBupdate=true;
			$_SESSION['message'] .= "show/hide status of '".$dbRecord['name']."' changed to '".$ShowHidePosted."'<br />";
		} 
	$dbRecordCounter++; 
	}

/* TEMPLATE changes */
	$dbRecordCounter = 0;
	while ( $dbRecordCounter < count($dbRecordsArray) ) { 
		$dbRecord = $dbRecordsArray[$dbRecordCounter]; 
		$templateCurrent = $dbRecord['template']; 
		$templatePosted = $_POST["template_$dbRecordCounter"];
		if ( $templateCurrent != $templatePosted ) {
			$dbRecord['template'] = $templatePosted;
			$dbRecordsArray[$dbRecordCounter] = $dbRecord;
			$doDBupdate=true;
			$_SESSION['message'] .= "Template for '".$dbRecord['name']."' changed to '".$templatePosted."'<br />";
		} 
	$dbRecordCounter++; 
	}

/*DELETE PAGES*/
 	$dbRecordCounter = 0;
	while ($dbRecordCounter <= count($dbRecordsArray) ) { 
		if (isset($_POST["delete_$dbRecordCounter"]) ) { 
			$dbRecord = $dbRecordsArray[$dbRecordCounter]; 
			$file_to_delete = addslashes($dbRecord['name']); 
			if ( $file_to_delete == $GLOBALS['defaultPageName'] ) {
				$_SESSION['message'] .= "<br />You can not delete your default page.<br />Set another default before deleting '".$file_to_delete."'";
			} else { 
				if (file_exists($GLOBALS['dirContent'].$file_to_delete)) {
					unlink($GLOBALS['dirContent'].$file_to_delete); 
					$file_to_delete = $GLOBALS['fileGenPath'].$file_to_delete.".$fileGenExtension";
					$file_to_delete = str_replace(" ", $fileGenSpaceReplace, $file_to_delete);
					if (file_exists($file_to_delete)) { 
						unlink($file_to_delete); 
					} 
				} 
				$_SESSION['message'] .= "Page '".$dbRecord['name']."' has been deleted.<br />";
				unset($dbRecordsArray[$dbRecordCounter]); //remove row from database
				$doDBupdate = true;
			}
		} 
		$dbRecordCounter++; 
	}

	if ($doDBupdate == true) {
		//write the updated database back out to a file
		if ( db_saveDatabase($dbRecordsArray) == true) {
			backToPanel("Changes were successfully saved to database.");
		} else {
			backToPanel("Unable to save changes to database.");
		}
	} else {
		backToPanel();
	}
}

function com_updatePluginDbVars() {
/* This function takes the form input from the pluginDbVars panel and batch
processes many changes at once. */
	authenticate();
	global $dbRecordsArray;
	global $pluginDbVars;
	$doDBupdate=false;
	$dbRecordCounter = 0;

	while ( $dbRecordCounter < count($dbRecordsArray) ) { 
		$dbRecord = $dbRecordsArray[$dbRecordCounter];
		$j = 0;
		while ( $j < count($pluginDbVars) ) { 
			$varName = $pluginDbVars[$j];
			$valueCurrent = $dbRecord[$varName]; 
			$valuePosted = $_POST[$varName."_".$dbRecordCounter];
			if ( $valuePosted != $valueCurrent ) {
				if (function_exists("pluginDbVarValidate_$varName") == true) {
					call_user_func("pluginDbVarValidate_$varName", $dbRecord, $valuePosted);
				}
				$dbRecord[$varName] = $valuePosted;
				$dbRecordsArray[$dbRecordCounter] = $dbRecord;
				$doDBupdate = true;
				$_SESSION['message'] .= $varName." for ".$dbRecord['name']." changed to '".$valuePosted."'<br />";
			} 
			$j++;
		}
		$dbRecordCounter++; 
	}
	if ($doDBupdate == true) {
		//write the updated database back out to a file
		if ( db_saveDatabase($dbRecordsArray) == true) {
			backToPanel("Changes were successfully saved to database.");
		} else {
			backToPanel("Unable to save changes to database.");
		}
	} else {
		backToPanel();
	}
}

function com_coreConfig(){
/* pass trhoguh wrapper -- allows config to be called through 
API from other functions) */
	com_editConfig($GLOBALS['fileNamePrefs']);
}

// Access the built-in configuration utility 
function com_editConfig($configFileName) {
	authenticate();
	$configFilePath = $GLOBALS['dirConfig'].$configFileName;
	$title = "Whisper";
	$body = "<p>You can change any of these settings to customize your site.</p><table id=\"coreconfig\" cellspacing=\"0\">\n";
	$body .= "<form method='post' action='?command=saveConfig'>\n";
	$body .= "<input type='hidden' name='configFileName' value='".$configFileName."'>\n";
	$readfile = file($configFilePath);
	$readfile = array_slice($readfile, 1, -1);
	for ($k=0; $k<=count($readfile)-1; $k++) { 
		$fields = split('( = | // )',$readfile[$k]);
		/*
		Key: 
		fields[0] = Variable name
		fields[1] = Variable code
		fields[2] = Variable title
		fields[3] = Variable description	
		*/
		$fields[1] = str_replace("\";", "", $fields[1]);
		$fields[1] = str_replace("\\\"", "QUOTATION_MARK_PLACEHOLDER", $fields[1]);
		$fields[1] = str_replace("\"", "", $fields[1]);
		$fields[1] = str_replace("QUOTATION_MARK_PLACEHOLDER", "\"", $fields[1]);
		$body .= '<input name="vname_' . $k . '" type="hidden" value="' . $fields[0] .'">';
		$body .= '<input name="title_' . $k . '" type="hidden" value="' . $fields[2] . '">';
		$body .= '<input name="description_' . $k . '"  type="hidden" value="' . $fields[3] .'">';
		$body .= '<tr class="dark">';
		$body .= '<td colspan="2"><b>' . $fields[2] . '</b></td>';
		$body .= '</tr>';
		$body .= '<tr class="light">';
		$body .= '<td>';
		$body .= '<input type="text" size="30" name="code_' . $k . '" value="' . htmlspecialchars($fields[1], ENT_QUOTES) . '" />';
		$body .= '</td>';
		$body .= '<td>' . $fields[3] . '</td>';
		$body .= '</tr>';
	}
	$body .= "<input name=counter type=hidden value=\"$k\"></table><input name='save' type='submit' value='Save Configuration'></form>";
	$subtitle = "Configuration";
	display_interface($title, $head, $subtitle, $body);
}


function com_saveConfig() {
	authenticate();
	$configFileName = ($_POST['configFileName']);
	$configFilePath = $GLOBALS['dirConfig'].$configFileName;
	$fp = fopen($configFilePath,'w') or die("The configuration file $configFilePath could not be opened for writing.  Please change its permissions.");
	$rowCount = $_POST['counter'];
	$new_counter = 0;
	$configuration_separator = " // ";
	$configuration_separator2 = " = ";
	$configuration_file_body = "<?\n";
	while ( $new_counter < $rowCount ) {
		$_POST["code_$new_counter"] = stripslashes($_POST["code_$new_counter"]);
		$_POST["code_$new_counter"] = str_replace("\n", "", $_POST["code_$new_counter"]);
		$_POST["code_$new_counter"] = str_replace("\"", "\\\"", $_POST["code_$new_counter"]);
		$configuration_file_body .= $_POST["vname_$new_counter"];
		$configuration_file_body .= $configuration_separator2;
		$configuration_file_body .= "\"";
		$configuration_file_body .= $_POST["code_$new_counter"];
		$configuration_file_body .= "\";";
		$configuration_file_body .= $configuration_separator;
		$configuration_file_body .= $_POST["title_$new_counter"];
		$configuration_file_body .= $configuration_separator;
		$configuration_file_body .= $_POST["description_$new_counter"];
		$configuration_file_body .= "\n";
		$new_counter++;
	}
	$configuration_file_body .= "?>";
	$configuration_file_body = str_replace("\n\n", "\n", $configuration_file_body);
	fwrite($fp, $configuration_file_body); fclose($fp);
	if ($fileGeneration == "on") { $_SESSION['message'] .= "Your Whisper configuration has been updated.  You should now <a href=\"?command=RebuildAllPages\">rebuild</b></a>."; }
	else { $_SESSION['message'] .= "Your Whisper configuration has been updated."; }
	backToPanel();
	exit;
}

// Rebuild the site (?command=rebuild)--------------------------
function com_RebuildAllPages() { 
	authenticate();
	global $dbRecordsArray;
	$title = "Whisper";
	$body = "<p>Rebuilding your site...</p><center><pre>\n";
	for ($k=0; $k<=count($dbRecordsArray)-1; $k++) {
		$dbRecord = $dbRecordsArray[$k];
		$tmpFileName = display_content($dbRecord['name'], true);
		$body .= $dbRecord['name'] . '  ->  <a href="'. $tmpFileName .'">' . $tmpFileName . "</a>\n";
	}
	$body .= "</center></pre><p>Your site has been rebuilt.</p>";
	$subtitle = "Rebuild";
	display_interface($title, $head, $subtitle, $body);
}

function com_RebuildDatabase() {
	db_RebuildDatabase();
}

// Create a new page (?command=create)--------------------------
function com_create() { 
	authenticate();
	$newItemName = $_POST['newItemName'];
	$mode = $_POST['mode'];
	if ( $newItemName == '' ) { 
		backToPanel("You need to type a file name before clicking 'Add'.");
		exit;
	}
	//$newItemName = str_replace("'", "^", $newItemName);
	$outFile = check_Mode($mode).$newItemName;
	if ($mode=="template") {
		$outFile .= ".html";
	}
	if (!file_exists($outFile)) {
		touch($outFile);
		chmod($outFile,0777); 
		if ( $mode == 'page' ) {
			global $dbRecordsArray;
			$newPageRecord['name'] = $newItemName;
			//$timediff is factored in when data is stored
			$newPageRecord['datetime'] = filemtime($outFile) + $GLOBALS['timeDiff'];
			$newPageRecord['showhide'] = $GLOBALS['defaultShowHide'];
			$newPageRecord['template'] = 'default';
			$newPageRecord['markup'] = $GLOBALS['defaultMarkup'];
			$newPageRecord['visible'] = "yes";
			$dbRecordsArray[] = $newPageRecord;
			if ( db_saveDatabase($dbRecordsArray) == true ) {
				com_editFile($newItemName, $mode);
			} else {
				backToPanel("Unable to update database.");
			}
		} else { //not 'page' mode 
			com_editFile($newItemName, $mode);
		}
	} else { 
		backToPanel("An item by that name already exists.  Please choose another name.");
	}
}
function com_edit(){
/* this is just a pass-through wrapper */
	com_editFile($_GET['file'], $_GET['mode']);
}

function com_editFile($editFileName, $editMode) {
/* Display interface for editing a file.
$editFileName = name of file
$editMode = page|config|template - this prevents any hacking that could enable a mailcious user to edit files outside of the designated paths
*/
	authenticate();
	$subPath = check_Mode($editMode);
	$editFilePath = $subPath.$editFileName;
	if ($editMode=="template") {
		$editFilePath .= ".html";
	}
	//$editFilePath = str_replace("^", "\\^", $editFilePath);
	if (!file_exists($editFilePath)) {
		backToPanel("$editFilePath does not exist, unable to enter edit mode.");
	}
	$fp = fopen($editFilePath,'r') or die("The file " . $editFilePath . " could not be opened for reading.  Please change its permissions.");
	$fsize = filesize($editFilePath);
	if ($fsize>0) {
		$content = fread($fp, $fsize); 
	}
	fclose($fp);
	$title = 'Whisper';
	$body = '<p>Editing file: <b>' . stripslashes($editFilePath) . '</b>';
	global $pluginMarkupParsers;
	if ( ($editMode == "page") && (is_array($pluginMarkupParsers)==true) ) { 
		for ($k=0; $k<=count($pluginMarkupParsers)-1; $k++) {
			$markup = $pluginMarkupParsers[$k];
			if (function_exists("helplink_$markup") == true) {
				$returnLink = "";
				call_user_func("helplink_$markup", &$returnLink);
				$body .= " | ".$returnLink;
			}
		}
	}
	$body .= '</p>';
	$body .= '
	<form method="post" action="?command=saveFile">
	<textarea rows="' . $GLOBALS['rows'] . '" cols="' . $GLOBALS['cols'] . '" name="content">' . htmlspecialchars($content) . '</textarea>
	<input name="file" type="hidden" value="' . $editFileName . '">
	<input name="mode" type="hidden" value="' . $editMode . '">';
	if ($editMode == "page") { //present additional page options
		//set as default
		$dbRecord = db_getPage($editFileName);
		//select template
		$varValue=$dbRecord['template'];
		$body .="   Template: ";
		$body .="<select name='useTemplate' value='".$varValue."'>";
		$body .="<option value='".$varValue."' selected>$varValue</option>";
		$body .= get_Templates(true, true);
		$body .="</select>";
		//select markup parser
		$varValue=$dbRecord['markup'];
		$body .="   Markup Parser: ";
		$body .="<select name='useMarkup' value='".$varValue."'>";
		if ($_POST['command'] == "create")
			$varValue = "none";
		else
			$body .="<option value='".$varValue."' selected>$varValue</option>";
		$body .= get_MarkupParsers();
		$body .="</select>";
		//select parent
		if (function_exists("pluginDbVarDefault_parent") == true) {
			$varValue=$dbRecord['parent'];
			$body .="   Parent Page: ";
			$body .="<select name='parent' value='".$varValue."'>";
			$body .="<option value='".$varValue."' selected>$varValue</option>";
			$body .= get_PageSelect();
			$body .="</select>";
			$body .= "<p>";
		}
		// Make default
		if ($editFileName !== $GLOBALS['defaultPageName'] ) { 
				$body .= "<input type=\"checkbox\" name=\"new_default\" value=\"active\"> Make this my new front page. "; 
			} else {
				$body .= "This page is your current front page.";
			}
		// Hidden?   
		if ($dbRecord['showhide'] == "hide") {
			$checked = " checked=\"checked\"";
		}
		else {
			$checked = "";
		}
		$body .= " <input type=\"checkbox\" name=\"hidden\" value=\"hide\" ".$checked."> Hide this page in lists. ";  
		// Visible?
		if ($dbRecord['visible'] == "yes") {
			$checked = " checked=\"checked\"";
		}
		else {
			$checked = "";
		}
		$body .= "&nbsp;<input type=\"checkbox\" name=\"visible\" value=\"visible\" ".$checked."> Allow visitors to see this page. ";
		$body .= "</p>";
	}
	$body .= "<p><input name='save' type='submit' value='Save ".$editMode."'></p></form>";
	if ($editMode=="template" || $editMode=="page" ) {
		$body .= panel_tagList();
	}
	$subtitle = "File Editor";
	display_interface($title, $head, $subtitle, $body);
}


function com_saveFile() {
/* This function should only be called in response 
to a form submission from com_editFile()
$saveFileName = name of file
$saveMode = page|config|template
*/
	authenticate();
	$saveFileName= stripslashes($_POST['file']);
	$saveMode = $_POST['mode'];
	$subPath = check_Mode($saveMode);
	$saveFilePath = $subPath.$saveFileName;
	if ($saveMode=="template") {
		$saveFilePath .= ".html";
	}
	$fp = fopen($saveFilePath,'w') or die("The file " . $saveFileName . " could not be opened for writing.  Please change its permissions.");
	$content_stripped = stripslashes($_POST['content']);
	fwrite($fp, $content_stripped);
	fclose($fp);
	$_SESSION['message'] .= "The $saveMode '".$saveFileName."' has been saved.<br />";
	if (($mode=='config')&&($saveFileName==$GLOBALS['fileNamePrefs'])){
		db_LoadDatabase(); //the database file was edited, so reload it
	}
	if ( $saveMode == 'page' ) {
		if (isset($_POST['useTemplate'])==true) {
			db_updateField($saveFileName, 'template', $_POST['useTemplate']);
		}
		if (isset($_POST['useMarkup'])==true) {
			db_updateField($saveFileName, 'markup', $_POST['useMarkup']);
		}
		if ($_POST['new_default'] == "active") {
			set_default($saveFileName);
		}
		if ($_POST['hidden'] == "hide") {
			db_updateField($saveFileName, 'showhide', "hide");
		}
		else {
			db_updateField($saveFileName, 'showhide', "show");
		}
		if (function_exists("pluginDbVarDefault_visible") == true) {
			if ($_POST['visible'] == "visible") {
				db_updateField($saveFileName, 'visible', "yes");
			}
			else {
				db_updateField($saveFileName, 'visible', "no");
			}
		}
		if (function_exists("pluginDbVarDefault_parent") == true) {
			if (isset($_POST['parent'])==true) 
				db_updateField($saveFileName, 'parent', $_POST['parent']);
		}
		if ( $GLOBALS['fileGeneration']  == "on" ) { 
			$link = display_content($saveFileName, true);
			$_SESSION['message'] .= "<br />The file is now available outside the script at <a href='".$link."'>$link</a><br />";
		}
	}
	backToPanel();
}


/*-------------------- panel widgets ------------------------------------------
The functions in this section are used to create panel sections. By having them
here as separate functions, their variables are isolated in scope, and they can
more easily be updated/modified or called from plugins, etc.
-----------------------------------------------------------------------------*/


function panel_IndividualPages() {
	global $dbRecordsArray;
	$panel .= panelPart_StartBlock("Individual Pages");
		for ($k=0; $k<=count($dbRecordsArray)-1; $k++) {
			if ($k<$GLOBALS['viewPageList']) {
				$dbRecord = $dbRecordsArray[$k];
				$is_first = 0;
				$is_front = 0;
				$is_hidden = 0;
				if ($k == 0) { $is_first = TRUE; }
				if ($dbRecord['name'] == $GLOBALS['defaultPageName']) { $is_front = TRUE; }
				if ($dbRecord['showhide'] == 'hide') { $is_hidden = TRUE; }
				$name = stripslashes($dbRecord['name']);
				if ( !$is_front && !$is_hidden ) {
					if ($k != 0) { $panel .= $GLOBALS['sep']; }
					$panel .= '<a href="?command=edit&mode=page&file='.$name.'" title="Edit '.$name.'">'.$name.'</a>';
					continue;
				}
				if ( $is_front && $is_hidden ) {
					if ($k != 0) { $panel .= $GLOBALS['sep']; }
					$body .= '<strong><em><a href="?command=edit&mode=page&file='.$name.'" title="Edit '.$name.' (front page, hidden)">'.$name.'</a></em></strong>';
					continue;
				}
				if ( $is_front ) {
					if ($k != 0) { $panel .= $GLOBALS['sep']; }
					$panel .= '<strong><a href="?command=edit&mode=page&file='.$name.'" title="Edit '.$name.' (front page)">'.$name.'</a></strong>';
					continue;
				}	
				if ( $is_hidden ) {
					if ($k != 0) { $panel .= $GLOBALS['sep']; }
					$panel .= '<em><a href="?command=edit&mode=page&file='.$name.'" title="Edit '.$name.' (hidden)">'.$name.'</a></em>';
					continue;
				}
			} 
		}
		if ($k>=$GLOBALS['viewPageList']) {
			$panel .= ' <a href="?command=database">... see all pages</a>';
		}
	$panel .= panelPart_EndBlock();
	return $panel;
}

function panel_SiteManagement() { 
	authenticate();
	$panel .= panelPart_StartBlock("Site Management");
	$panel .= '<a href="?command=coreConfig">Configuration</a>';
	$panel .= $GLOBALS['sep'].'<a href="?command=edit&mode=style&file=default.css">Stylesheet</a>';
	if ($GLOBALS['fileGeneration'] == "on") {
		$panel .= $GLOBALS['sep'].'<a href="?command=RebuildAllPages">Rebuild All Pages</a>';
	}
	if ($GLOBALS['viewPageTable'] != "on") {
		$panel .= $GLOBALS['sep'].'<a href="?command=database" title="Manage all pages">Page Database</a>';
	}	
	if ($GLOBALS['viewTemplates'] != "on") {
		$panel .= $GLOBALS['sep'].'<a href="?command=manageTemplates">Manage Templates</a>';
	}	
	$panel .= panelPart_EndBlock();
	return $panel;
}

function panel_SiteInformation() { 
	authenticate();
	$panel .= panelPart_StartBlock("Site Information");
	$panel .= 'This is <a href="?command=checkVersion" title="Check for a more recent version of Whisper">version</a> <b>' . $GLOBALS['version'] .$GLOBALS['sep'].'</b> Build serial <b>' . $GLOBALS['serial'] . '</b>, File Generation is <strong>'.$GLOBALS['fileGeneration'].'</strong>';
	$panel .= panelPart_EndBlock();
	return $panel;
}

function panel_Plugins() {
	authenticate();
	global $pluginDbVars, $pluginMarkupParsers, $pluginCommands, $pluginPanels, $pluginTags;
	$panel .= panelPart_StartBlock("Plugins");

	//$pluginCommands does not need to be displayed here 
	//-- hook is in place at the "commandSwitch"

	if (is_array($pluginMarkupParsers)) {
		$panel .= " Text Markup: ";
		for ($k=0; $k<=count($pluginMarkupParsers)-1;$k++){
			$panel .= "<strong>".$pluginMarkupParsers[$k]."</strong>";
			if ($k != count($pluginMarkupParsers)-1){
				$panel .= $GLOBALS['sep'];
			}
		}
	} else {
		$panel .= "<i>No Text Markup</i>";
	}
	
	if (is_array($pluginTags)) {
		$panel .= $GLOBALS['sep']."<a href='?command=viewTags' title='View All Available Tags'>Tags</a>";
	} else {
		$panel .= $GLOBALS['sep'].'<i> No Tags</i>';
	}

	if (is_array($pluginDbVars)) {
		$panel .= $GLOBALS['sep']."<a href='?command=editPluginDbVars' title='View / Edit Plugin Variables'>Variables</a>";
	} else {
		$panel .= $GLOBALS['sep'].'<i> No Variables </i>';
	}
	

	if (is_array($pluginPanels)) {
		for ($k=0; $k<=count($pluginPanels)-1; $k++) {
			$panelName=$pluginPanels[$k];
			$panel .= $GLOBALS['sep']."<a href='?command=".$panelName."'>$panelName</a>";
		}
	} else {
		$panel .= $GLOBALS['sep'].'<i> No Panels</i>';
	}


	$panel .= panelPart_EndBlock();
	return $panel;
}

function panel_PageTable() {
	authenticate();
	$panel .= panelPart_StartBlock("Pages");
	$panel .= 'There is no confirmation screen or "undo" when making changes in the table below!';
	$panel .= panelPart_PageTable();
	$panel .= panelPart_EndBlock();
	return $panel;
}

function panel_pluginDbFields() {
	authenticate();
	$panel .= panelPart_StartBlock("Plugin Database Fields");
	$panel .= 'There is no confirmation screen or "undo" when making changes in the table below!';
	$panel .= panelPart_pluginDbFields();
	$panel .= panelPart_EndBlock();
	return $panel;
	
}

function panel_CreateNewPage() {
	authenticate();
	$panel .= panelPart_StartBlock("Create Page");
	$panel .= panelPart_CreatePage();
	$panel .= panelPart_EndBlock();
	return $panel;
}

function panel_Templates() {
	authenticate();
	$panel .= panelPart_StartBlock("Templates");
	$panel .= "Add, edit, or delete templates.";
	$panel .= '<table id="paneltemplates" cellspacing="0"><tr>';
	$panel .= '<td>';
	$panel .= '<form method="post" action="?command=create">';
	$panel .= '&nbsp;&nbsp;<input type="text" name="newItemName" size="20" />';
	$panel .= '&nbsp;&nbsp;<input type="hidden" name="mode" value="template" />';
	$panel .= '&nbsp;&nbsp;<input name="add" type="submit" class="button" value="Add" />';
	$panel .= '</form></td>';
	$panel .= '<td>';
	$panel .= '<form method="post" action="?command=templates">';
	$panel .= '&nbsp;&nbsp;<input type="hidden" name="mode" value="edit" />';
	$panel .= "<select name='templateName' value=''>";
	$panel .= get_Templates(true, false);
	$panel .="</select>";
	$panel .= '&nbsp;&nbsp;<input name="go" type="submit" class="button" value="Edit" />';
	$panel .= '</form></td>';
	$panel .= '<td>';
	$panel .= '<form method="post" action="?command=templates">';
	$panel .= '&nbsp;&nbsp;<input type="hidden" name="mode" value="delete" />';
	$panel .= "<select name='templateName' value=''>";
	$panel .= get_Templates(false, false);
	$panel .="</select>";
	$panel .= '&nbsp;&nbsp;<input name="go" type="submit" class="button" value="Delete" />';
	$panel .= '</form></td>';
	$panel .= '</tr></table>';
	$panel .= panelPart_EndBlock();
	return $panel;
}

function panel_TagList() {
	$panel .= panelPart_StartBlock("Core Tags");
	global $coreTags, $tagOpen, $tagClose;
	for ($k=0; $k<=count($coreTags)-1; $k++) {
		$tag = htmlspecialchars($tagOpen).$coreTags[$k].htmlspecialchars($tagClose);
		if ( $k > 0) { $panel .= $GLOBALS['sep']; }
		$panel .= $tag;
	}
	$panel .= panelPart_EndBlock();
	global $pluginTags;
	if (is_array($pluginTags) == true) {
		$panel .= panelPart_StartBlock("Plugin Tags");
		for ($k=0; $k<=count($pluginTags)-1; $k++) {
			$tag = htmlspecialchars($tagOpen).$pluginTags[$k].htmlspecialchars($tagClose);
			if ( $k > 0) { $panel .= $GLOBALS['sep']; }
			//if ( ($k%4) == 0) { $panel .= '<br />'; } //only four tags per line
			$panel .= $tag;
		}
		$panel .= panelPart_EndBlock();
	}
	return $panel;
}

function panel_Manual() {
	$manual = file("./documentation/manual.html");
	$panel .= panelPart_StartBlock("Manual");
	$panel .= implode("", $manual);
	$panel .= panelPart_EndBlock();
	return $panel;
}

function panelPart_StartBlock($title) {
	$panelPart .= "\n";
	$panelPart .= "<div class='panel'><strong>$title</strong>".$GLOBALS['sep2']."\n";
	return $panelPart;
}

function panelPart_EndBlock() {
	$panelPart .= "</div>\n";
	return $panelPart;
}

function panelPart_PageTable() {
	global $dbRecordsArray;
	$templateOptions = get_Templates(true, true); //get this once so that we don't unnecessarily call the same function multiple times during the for loop
	$pageTable .="\n\n<table id=\"panelpagetable\" cellspacing=\"0\">\n<tr class=\"dark\">";
	$pageTable .="<form method='post' action='?command=updateDB'>";
	$pageTable .="<th>Page Name</th>";
	$pageTable .="<th>Template</th>";
	$pageTable .="<th>Page Date</th>";
	$pageTable .="<th>Hide</th>";
	$pageTable .="<th>Action</th>";
	$pageTable .="<th class=\"pageTableAlert\">Delete</th>";
	$pageTable .="</tr>";
	for ($k=0; $k<=count($dbRecordsArray)-1; $k++) { 
		$dbRecord = $dbRecordsArray[$k];
		//alternating table rows
		$bgc = ($k % 2 ? 'dark' : 'light');
		$pageTable .="\n<tr class=\"".$bgc."\">";
		//name
		$varName="page_name_".$k;
		$varValue=stripslashes($dbRecord['name']);
		$pageTable .= "<td style=\"text-align: left\"><input size='20' name=\"".$varName."\" value=\"".$varValue."\"></td>";
		
		//template
		$varName="template_".$k;
		$varValue=$dbRecord['template'];
		$pageTable .="<td style=\"text-align: left\">";
		$pageTable .="<select name=\"".$varName."\" value=\"".$varValue."\">";
		$pageTable .="<option value=\"".$varValue."\" selected>\"".$varValue."\"</option>";
		$pageTable .= $templateOptions;
		$pageTable .="</select>";
		$pageTable .="</td>";
		
		//creation datetime
		if ( $dbRecord['datetime'] == "" ) { // Handle empty date
			if (file_exists($GLOBALS['dirContent'].$dbRecord['name'])==true) {
				//$timediff is factored in when data is stored
				$modified = filemtime($GLOBALS['dirContent'].$dbRecord['name'] + $GLOBALS['timeDiff']);
				$datetimeDisplay = date( $GLOBALS['formatDateTime'], $modified);
			} else {
				$datetimeDisplay = "FILE NOT FOUND";
			}
		} else {
			$datetimeDisplay = date($GLOBALS['formatDateTime'], $dbRecord['datetime']);
		}
		$varName="datetime_".$k;
		$varValue= $datetimeDisplay;
		$pageTable .= "<td class=\"pageTable\" style=\"text-align: left\"><input size=\"25\" name=\"".$varName."\" value=\"".$varValue."\"></td>";
		
		//show / hide
		$varName="ShowHide_".$k;
		if ( get_ShowHide($dbRecord['showhide']) == "hide") { 
			$varValue = "checked"; 
		} else {
			$varValue = "";
		}
		$pageTable .= "<td class=\"pageTable\"><input type=\"checkbox\" name=\"".$varName."\" ".$varValue."></td>";
		

		//action links
		$pageTable .= '<td class="pageTable">';
		$pageTable .= '<a href="?command=edit&amp;mode=page&amp;file='.$dbRecord['name'].'" title="Edit '.$dbRecord['name'].'">edit</a>&nbsp;|&nbsp;';
		$pageTable .= '<a href="?page='.$dbRecord['name'].'" target="_blank" title="View '.$dbRecord['name'].'">view</a>&nbsp;|&nbsp;';
		if ($dbRecord['name'] == $GLOBALS['defaultPageName'] ) {
			$pageTable .= '<span style="color: red">default</span>';
		} else {
			$pageTable .= '<a href="?command=newDefault&page='.$dbRecord['name'].'" title="Make '.$dbRecord['name'].' the default front page">set def</a>';
		}
		$pageTable .= '</td>';
		
		//delete
		$pageTable .="<td class=\"pageTableAlert\">";
		$varName = "delete_".$k;
		$pageTable .= "<input type=\"checkbox\" name=\"".$varName."\"></td></tr>\n";
	}
	$pageTable .= "<tr colspan=\"5\"><td>";
	$pageTable .= "<input name=\"numPages\" type=\"hidden\" value=\"$k\"><br />";
	$pageTable .= "<input name=\"save\" type=\"submit\" value=\"Save Changes\">";
	$pageTable .= "</form>";
	$pageTable .= "</td></tr></table>\n";
	$pageTable .= '<i><b>Advanced Functions</b></i>'.$GLOBALS['sep2'];
	$pageTable .= '<a href="?command=edit&mode=config&file=whisperDB">Edit</a> database file directly';
	$pageTable .= ' | <a href="?command=RebuildDatabase">Rebuild</a> database file by scanning content directory';
	$pageTable .= '<br />Note that rebuilding the database file will reset all options to default settings.';
	if (is_array($GLOBALS['pluginDbVars']) == true) {
		
	}
	return $pageTable;
}

function panelPart_PluginDbVars() {
	global $dbRecordsArray;
	global $pluginDbVars;
	$pluginFieldTable .= "<p>Update plugin variables here (there is no confirmation or undo). Variables that support it will call their respective 'validate' functions.</p>";
	$pluginFieldTable .="\n<table cellspacing=0>\n<tr>";
	$pluginFieldTable .="\n<form method='post' action='?command=updatePluginDbVars'>";
	$pluginFieldTable .="<th>Page Name</th>";
	for ($k=0; $k<=count($pluginDbVars)-1; $k++) { 
		$pluginFieldName = $pluginDbVars[$k];
		$pluginFieldTable .="<th>$pluginFieldName</th>";
	}
	$pluginFieldTable .="</tr>";
	for ($j=0; $j<=count($dbRecordsArray)-1; $j++) { 
		$dbRecord = $dbRecordsArray[$j];
			if($j & 1) { 
				$class = "dark"; 
			} else { 
				$class = "light"; 
			}
		$pluginFieldTable .="\n<tr class=\"".$class."\">";
		//name
		$pluginFieldTable .= "<td>".stripslashes($dbRecord['name'])."</td>";
		
		//plugin fields
		for ($k=0; $k<=count($pluginDbVars)-1; $k++) { 
			$pluginFieldName = $pluginDbVars[$k];
			$varName= $pluginFieldName."_".$j;
			$varValue=$dbRecord[$pluginFieldName];
			$pluginFieldTable .="<td>";
			$pluginFieldTable .="<input name='".$varName."' value='".$varValue."'>";
			$pluginFieldTable .="</td>";
		}
		$pluginFieldTable .= "</tr>\n";
	}
	$pluginFieldTable .= "<tr colspan='5'><td>";
	$pluginFieldTable .= "<input name='save' type='submit' value='Save Changes'>";
	$pluginFieldTable .= "</form>";
	$pluginFieldTable .= "</td></tr></table>\n";
	return $pluginFieldTable;
}


function panelPart_CreatePage(){
	$createPage .= '<form method="post" action="?command=create">Type a name and click <em>Add</em>.';
	$createPage .= '&nbsp;&nbsp;<input type="text" name="newItemName" size="20" />';
	$createPage .= '&nbsp;&nbsp;<input type="hidden" name="mode" value="page"/>';
	$createPage .= '&nbsp;&nbsp;<input name="add" type="submit" class="button" value="Add" />';
	$createPage .= '</form>';
	return $createPage;
}


/* -----------------------------------------------------------------------------
DATABASE API:
The functions here are provided for interaction with the datbase.
No other functions should access the database file directly, whether from 
core funcitons, or from plugins. These functions provide an API that will allow
flexibility as the project moves forward. The database could eventually be 
replaced with an actual MySQL (or other) database -- by requiring other parts
of the application to go through these functions, then other parts of the 
application could be left unchanged even if the database itself is radically 
altered.
------------------------------------------------------------------------------*/

function db_rebuildDatabase() {
/* This function scans the content directory and rebuilds 
the database file based on the files that are found. */
	authenticate();
	global $pluginDbVars;
	if (is_dir($GLOBALS['dirContent'])==true) {
		//first--build an array of the files in the directory
		$dir = opendir($GLOBALS['dirContent']);
		$fileCount = 0;
		while($fileName = readdir($dir)) { 
			if ($fileName{0} == ".") continue; //skip curDir and parentDir and any unix 'hidden' file
			$dbRecord = null;
			$dbRecord['name'] = $fileName;
			//$timediff is factored in when data is stored
			$dbRecord['datetime'] = filemtime($GLOBALS['dirContent'].$fileName) + $GLOBALS['timeDiff'];
			$dbRecord['showhide'] = $GLOBALS['defaultShowHide'];
			$dbRecord['template'] = 'default';
			$dbRecord['markup'] = $GLOBALS['defaultMarkup'];
			//add plugin fields if they are defined
			if (is_array($pluginDbVars) == true) {
				for ($k=0; $k<=count($pluginDbVars)-1; $k++) {
					$varName = $pluginDbVars[$k];
					call_user_func("pluginDbVarDefault_$varName", &$getDefault);
					$dbRecord[$varName] = $getDefault;
				}
			}
			$newDb[$fileCount] = $dbRecord;
			$fileCount++;
		}
		closedir($dir);
		if ( db_saveDatabase($newDb) == true ) {
			backToPanel("Database successfully rebuilt.");
		} else {
			backToPanel("Unable to save changes to database.");
		}
	} else {
		backToPanel("Unable to read content directory.");
	}
}

function db_updateField($pageName, $fieldName, $newValue) {
/* updates a single field in a single record -- returns true or false */
	global $dbRecordsArray;
	$recordCount = 0;
	while ( $recordCount < count($dbRecordsArray) ) { 
		$dbRecord = $dbRecordsArray[$recordCount];
		if ( $dbRecord['name'] == $pageName ) {
			if (array_key_exists($fieldName, $dbRecord) == true) {
				$dbRecord[$fieldName] = $newValue;
				$dbRecordsArray[$recordCount] = $dbRecord;
				if ( db_saveDatabase($dbRecordsArray) == true ) {
					$_SESSION['message'] .= "$fieldName for $pageName was set to $newValue<br />";
					return true;
				} else {
					$_SESSION['message'] .= "Unable to save field update to database.<br />";
					return false;
				}
			} else {
				$_SESSION['message'] .= "Could not find field '".$fieldName."' in $pageName <br />";
				return false;
			}
		}//end if
		$recordCount++; 
	}
	$_SESSION['message'] .= "Unable to save field update to database because page '".$pageName."' was not found.<br />";
	return false; // the record was not found
}


function db_getPage($LookupPageName) {
/* returns an associative array of key/value pairs for the requested page */
	global $dbRecordsArray;
	$recordCount = 0;
	while ( $recordCount < count($dbRecordsArray) ) { 
		$dbRecord = $dbRecordsArray[$recordCount];
		if ( $LookupPageName == $dbRecord['name'] ) {
			return $dbRecord; //this is the one we wanted, so return now
		}//end if
		$recordCount++; 
	}
	return false; // the record was not found
}

function db_LoadDatabase() {
/* loads the database file into a global array that is available to other 
database functions. This global array allows us to reduce the number of times 
that the database file is accessed directly on the disk. */
	global $dbRecordsArray;
	global $pluginDbVars;
	$dbRecordsArray = null;
	$dbArray = file($GLOBALS['filePathDatabase']);
	$dbRecordCounter = 0;
	while ( $dbRecordCounter < count($dbArray) ) {
		unset($dbRecordToSave);
		unset($dbRecordTemp);
		$dbRecordTemp = explode($GLOBALS['dbSeparator'], $dbArray[$dbRecordCounter]);
		//core fields
		$dbRecordToSave['name'] = trim($dbRecordTemp[0]);
		$dbRecordToSave['datetime'] = trim($dbRecordTemp[1]);
		$dbRecordToSave['showhide'] = trim($dbRecordTemp[2]);
		$dbRecordToSave['template'] = trim($dbRecordTemp[3]);
		$dbRecordToSave['markup'] = trim($dbRecordTemp[4]);
		//now add plugin fields if they are defined
		if (is_array($pluginDbVars) == true) {
			for ($k=0; $k<=count($pluginDbVars)-1; $k++) {
				$varName = $pluginDbVars[$k];
				$columnNumber = $k + 5; //begin after the core variables
				$dbRecordToSave[$varName] = trim($dbRecordTemp[$columnNumber]);
			}
		}
		$dbRecordsArray[$dbRecordCounter] = $dbRecordToSave; //add record array as element of table array
		//db_echoRecordNum($dbRecordCounter);
		$dbRecordCounter++; 
	}
}


function db_echoRecord ($pageData) {
//code debugging helper function -- should not be called from a stable release
	echo "name = ".$pageData['name'];
	echo "  datetime = ".$pageData['datetime'];
	echo "  showhide = ".$pageData['showhide'];
	echo "  template = ".$pageData['template'];
	echo "  markup = ".$pageData['template'];
	echo "<br>\n";
}

function db_echoRecordNum ($k) {
//code debugging helper function -- should not be called from a stable release
	global $dbRecordsArray;
	$pageData = $dbRecordsArray[$k];
	echo "record number = $k";
	echo "  name = ".$pageData['name'];
	echo "  datetime = ".$pageData['datetime'];
	echo "  showhide = ".$pageData['showhide'];
	echo "  template = ".$pageData['template'];
	echo "  markup = ".$pageData['template'];
	echo "<br>\n";
}

function db_saveDatabase($newRecordsArray) {
/*  write a modified database record array back out the the disk file */
	authenticate();
	$delim = $GLOBALS['dbSeparator'];
	$recordCount = 0;
	while ( $recordCount <= count($newRecordsArray)) { 
		$outputLine = implode($delim, $newRecordsArray[$recordCount]);
		if (strlen($outputLine) != 0) {
			$outArray[$recordCount] = trim($outputLine);
		}
		$recordCount++; 
	}	
	$output = implode("\n", $outArray);
	$outFile = fopen($GLOBALS['filePathDatabase'],'w') or die("The database could not be opened for writing.  Please change its permissions.");
	fwrite($outFile, $output); 
	fclose($outFile);
	global $dbRecordsArray;
	$GLOBALS['dbRecordsArray'] = $newRecordsArray;
	return true;
}


/* ----------------------------------------------------------------------------
PLUGIN API:
The functions in this section are to be used to by plugins. 
Hooks are provided in the "commandSwitch", Plugins Panel, and all Database 
functions, and a tag viewer.

Note that plugin functions do not RETURN values through the 'return' function. 
Rather, due to the fact that they get called through the 'call_user_func', they
must return a value by modifying a variable that is passed _by reference_ 
(variables are usually passed _by value_).
------------------------------------------------------------------------------*/
function plugin_registerTag($tagName) {
/* This function should be called when the plugin is included into the global 
scope of the main whisper script.the given tagName will be added to a global 
array of search and replace tags if and only if there exists a function in the 
plugin with a name of the form: tagReplace_tagName -- where 'tagName' corresponds 
to the given tag. The function must be implemented to return a string, an 
associative array containing the database fields for the current page being 
renderedwill be passed to the function. */
	global $pluginTags;
	if (function_exists("tagReplace_$tagName") == true) {
		$pluginTags[] = $tagName;
	}
}

function plugin_registerPanel($panelName) {
/* This function should be called when the plugin is included into the global 
scope of the main whisper script. The given panelName will be added to a global 
array of control panel plugins if and only if there exist two functions in the 
plugin with names in the foliowing form (where 'panelName' corresponds to the 
given panel name): 
pluginPanel_panelName -- implemented to return a string to the com_ function
com_panelName -- will be called from the "commandSwtich"
  the com_ function should make use of the following core functions to put 
  together a page consistent with the rest of the interface:
    panelPart_StartBlock, panelPart_EndBlock, display_Interface
*/
	global $pluginPanels, $pluginCommands;
	if ((function_exists("pluginPanel_$panelName") == true)&&(function_exists("com_$panelName") == true)) {
		$pluginPanels[] = $panelName;
		$pluginCommands[] = $panelName;

	}
}

function plugin_registerCommand($commandName) {
/* This function should be called when the plugin is included into the global 
scope of the main whisper script. The given commandName will be added to a global 
array of control panel plugins if and only if there exists a function in the 
plugin with a name of the form: com_commandName -- where 'commandName' corresponds to 
the given command name. 
NOTE: do not call this function to register a 'display' function for a plugin panel 
Since the display function is REQUIRED for any panel, it will be registered through
the panel registration function. This function should be used for additional commands
that might be needed for plugin functionality. */
	global $pluginCommands;
	if (function_exists("com_$commandName") == true) {
		$pluginCommands[] = $commandName;
	}
}

function plugin_registerMarkupParser($parserName) {
/* This function should be called when the plugin is included into the global 
scope of the main whisper script. The global array "pluginMarkupParsers" will have
the given parserName added will be  if and only if there exists a function in the 
plugin with a name of the form: render_$parserName") -- where 'parserName' corresponds to 
the given parser name. */
	global $pluginMarkupParsers;
	if (function_exists("render_$parserName") == true) {
		$pluginMarkupParsers[] = $parserName;
	}
}

function plugin_registerDbVariable($varName) {
/* 
pluginDbVarDefault_$varName($value)- required 
pluginDbVarValidate_$varName($pageData, $value)- optional 
*/
	global $pluginDbVars;
	if (function_exists("pluginDbVarDefault_$varName") == true) {
		$pluginDbVars[] = $varName;
	}
}

function plugins_Load(){
	global $pluginTags, $pluginPanels, $pluginCommands, $pluginMarkupParsers, $pluginDbVars;
	//these are all simple integer indexed string arrays
	$pluginTags = null;      //hooks provided in panel_tagList() and renderPage()
	$pluginPanels = null;    //hooked in at panel_Plugins()
	$pluginCommands = null;  //hooked at 
	$pluginDbVars = null;
	$pluginMarkupParsers = null;

	// Locate and include plugins
	if(is_dir($GLOBALS['dirPlugins'])) {
		$directory = opendir($GLOBALS['dirPlugins']);
		while($fn = readdir($directory)) { 
			if ($fn == "." || $fn == "..") continue;
			if (substr($fn, -4) == ".php") { 
				include_once($GLOBALS['dirPlugins'].'/'.$fn);
			}
		}
		closedir($directory);
	}
}

/* ---------------------- misc helper functions -------------------------------
------------------------------------------------------------------------------*/

function core_Load() {
/* initialize core control arrays */
	global $coreTags;
	$coreTags = null;
	/* Because other tags are very likely to be contained in the body,
	the 'page_body' tag MUST be the very first in the array */
	$coreTags = null;
	$coreTags[] = "page_body"; 
	$coreTags[] = "page_title";
	$coreTags[] = "page_style";
	$coreTags[] = "script_file_name";

	global $coreCommands;
	$coreCommands = null;
	$coreCommands[] = "panel";
	$coreCommands[] = "logout";
	$coreCommands[] = "checkVersion";
	$coreCommands[] = "debug";
	$coreCommands[] = "create";
	$coreCommands[] = "database";
	$coreCommands[] = "templates";
	$coreCommands[] = "viewTags";
	$coreCommands[] = "edit";
	$coreCommands[] = "saveFile";
	$coreCommands[] = "saveConfig";
	$coreCommands[] = "coreConfig";
	$coreCommands[] = "updateDB";
	$coreCommands[] = "newDefault";
	$coreCommands[] = "editPluginDbVars";
	$coreCommands[] = "updatePluginDbVars";
	$coreCommands[] = "manageTemplates";
	$coreCommands[] = "RebuildDatabase";
	$coreCommands[] = "RebuildAllPages";
	$coreCommands[] = "manual";

}


function get_pageRequest() {
/* transform URI into simple page name variable 
$page may still need transforming below, so don't return right away
*/
    if (isset($_GET['page']) == true ) {
        //echo "explicit 'page' parameter given";
        $page = $_GET['page']; 
    } elseif($_SERVER["REQUEST_URI"] == $_SERVER["SCRIPT_NAME"]) {
        //echo "request = 'index.php'";
       $page = $GLOBALS['defaultPageName'];
    } else {
        //most cases should come through here...
        if(strpos($_SERVER['REQUEST_URI'], '?')) { // is there a '?' in URI?
            //without explicitly passing 'page=' page must be the first item after '?'
            //so lop off any parameters after '&'
            $loc = strpos($_SERVER['QUERY_STRING'], '&');
            $loc = ($loc === FALSE ? strlen($_SERVER['QUERY_STRING']) : $loc);
            $page = substr($_SERVER['QUERY_STRING'], 0, $loc);
            if (strpos($page, '=')) {
                //if there is an equal sign remaining, then we are dealing with a single
                //parameter that is not 'page' (template, style, PHPSESSID, etc.)
                //we may be rendering a template or stylesheet with no page
                //echo "alternate parameters: $page";
                return ''; 
            } else {
                //echo "parsed URI ";
            }
        } else { 
            // request just pointed to installation directory with no file or query
//          echo "request = dir";
            $page = $GLOBALS['defaultPageName'];
            //$qs = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            //$page = $qs[count($qs) - 1];
        }
        if($page=='') {
//          echo "somehow nothing";
            $page = $GLOBALS['defaultPageName'];
        }
    }
    $page = rawurldecode($page); // Decode any %s
    $page = str_replace($GLOBALS['fileGenSpaceReplace'], ' ', $page); // Change underscores to spaces
    return $page;
}



function get_pageLink($pageTitle, $linkText=false, $class=false) {
/* generates the HTML for a link to the given page */
	if (!$linkText) { 
		$linkText = stripslashes($pageTitle); 
	}
	$linkTitle = " title=\"".stripslashes($pageTitle)."\"";
	if ($class) { 
		$class = " class=\"$class\""; 
	} else { 
		$class = ""; 
	}
	if ($GLOBALS['fileGeneration'] == "on") { 
		$page_html = str_replace(" ", $GLOBALS['fileGenSpaceReplace'], $pageTitle); 
		$page_html = $page_html.".".$GLOBALS['fileGenExtension'];
		$link = "<a href=\"$page_html\"".$linkTitle.$class.">".$linkText."</a>"; 
	} else {
		$link = "<a href=\"?".$pageTitle."\"".$linkTitle.$class.">".$linkText."</a>"; 
	}
	return($link);  
}

function check_Mode ($editMode) {
/* given a mode, return the appropriate subdirectory prefix
to use for a file that is going to be parsed or written.
Exit if an invalid mode is passed. */
	switch ($editMode) {
		case "page":
			return $GLOBALS['dirContent'];
			break;
		case "config":
			return $GLOBALS['dirConfig'];
			break;
		case "template":
			return $GLOBALS['dirLayout'];
			break;
		case "layout":
			return $GLOBALS['dirLayout'];
			break;
		case "style":
			return $GLOBALS['dirLayout'];
			break;
		default:
			err_unsupported("check_Mode($editMode)");
	}
}

function get_ShowHide ($value) {
	switch ($value) {
		case "on":
			return "hide";
			break;
		case "off":
			return "show";
			break;
		case "hide":
			return "hide";
			break;
		case "show":
			return "show";
			break;
		case "1":
			return "hide";
			break;
		case "0":
			return "show";
			break;
		case true:
			return "hide";
			break;
		case false:
			return "show";
			break;
		default:
			return $GLOBALS['defaultShowHide'];
	}
}


function set_default($pageTitle) {
	authenticate(); //don't allow this function to be run without confirming authentication
	//get config file as array
	$configuration_file_data = file($GLOBALS['filePathPrefs']);
	//replace first line with new default file
	$configuration_file_data[1] = "\$defaultPageName = \"$pageTitle\"; // Front Page // The front page is what people see when they access your web site (it is the default page).\n";
	$configuration_file_data = implode('', $configuration_file_data);
	$configuration_file_data = str_replace("\n\n", "\n", $configuration_file_data);
	$fp = fopen($GLOBALS['filePathPrefs'],'w') or die("The configuration file could not be opened for writing.  Please change its permissions.");
	fwrite($fp, $configuration_file_data); fclose($fp);
	$_SESSION['message'] .= "The page \"$pageTitle\" has been set as your new front page.<br />";
	$GLOBALS['defaultPageName'] = $pageTitle;
}

function get_Templates($includeDefault, $includeNone) {
/* This function scans the layout directory and for template files (*.html)
and returns an option list for inclusion in a control panel. */
	authenticate();
	if (is_dir($GLOBALS['dirLayout'])==true) {
		//first--build an array of the files in the directory
		$dir = opendir($GLOBALS['dirLayout']);
		$fileCount = 0;
		while($fileName = readdir($dir)) { 
//			echo $fileName;
			if ($fileName == "." || $fileName == "..") continue; //skip curDir and parentDir
			if ((substr($fileName, -5, 5) == ".html")){
				$templateName = basename($fileName);
				$templateName = substr($templateName,0,-5);
				switch ($templateName) {
					case "none";
						$includeThis = $includeNone;
						break;
					case "default":
						$includeThis = $includeDefault;
						break;
					default:
						$includeThis = true;
				}
				if ($includeThis == true) {
					$fileArray[$fileCount] = $templateName;
					$fileCount++;
				}
			}
		}
		closedir($dir);
		//second, build up a list to pass back
		$returnValue = "";
		if ($fileCount > 0) {
			for ($i=0; $i<$fileCount; $i++) {
				$returnValue .= "<OPTION VALUE='".$fileArray[$i]."'>".$fileArray[$i]."</OPTION>";
			}
		}
		return $returnValue;
	}
}

function get_MarkupParsers() {
/* returns an option list of available markup parsers suitable for using 
withnin a form's SELECT tag */
	global $pluginMarkupParsers;
	$list = "<option value='none'>none</option>\n";
	if (is_array($pluginMarkupParsers)) { 
		for ($k=0; $k<=count($pluginMarkupParsers)-1; $k++) {
			$list .="<option value='".$pluginMarkupParsers[$k]."'>".$pluginMarkupParsers[$k]."</option>\n";
		}
	}
	return $list;
}

function get_PageSelect() {
/* returns an option list of pages, suitable for choosing a parent */
	global $dbRecordsArray;
	$list = "<option value='none'>none</option>\n";
	if (is_array($dbRecordsArray)) {
		for ($k=0; $k<=count($dbRecordsArray)-1; $k++) {
			$list .="<option value='".$dbRecordsArray[$k]['name']."'>".stripslashes($dbRecordsArray[$k]['name'])."</option>\n";
		} 
	}
	return $list;
}

function authenticate($pageName='') { // Authenticate users
	session_start();
	if (!isset($_SESSION['logged_in'])) {
		$_SESSION['logged_in'] = false;
		display_login($pageName); //pass originally requested page name so that user can be redirected
	}
	//just return quickly if user is already logged in
	if ($_SESSION['logged_in'] == true) { 
		$timeStamp = gettimeofday();
		$idleSecondsAllowed = $GLOBALS['inactivityTimeout'] * 60;
		if (($timeStamp['sec'] - $_SESSION['lastAuthTime']) > $idleSecondsAllowed) {
			display_login($pageName);
			exit;
		} else {
			$_SESSION['lastAuthTime'] = $timeStamp['sec'];
			return true;
		}
	}
	//we have not yet returned true, so display the login form and exit further processing
	display_login($pageName);
	exit;
}


function err_unsupported($source='') {
/*This function is called in situations where application logic
needs a "you can't get here from there" response. This insures 
a consistent message to the user, and encourages proper 
validation of variables in development.
*/
	$message = "You have requested an unsupported command function.<br />There may be a bug in your version of the Whisper core, or in one of your plugins.<br />This message was generated by $source<br />";
	$message .= "URI:  ".$_SERVER["REQUEST_URI"];
	backToPanel ($message);
}

function backToPanel($message='') {
/*This function appends the optional message into the session 
variable and then redirect/refreshes the user back to the panel.
*/
	if (isset($message)) {
		$_SESSION['message'] .= $message;
	}
	com_panel();
}

function set_Headers() {
//force page to expire so that it will not be cached locally
	if (headers_sent() == false) { 
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// HTTP/1.1
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		// HTTP/1.0
		header("Pragma: no-cache");
	}
}


/*---------------------------------------------------------------------------
PAGE RENDERING:
-----------------------------------------------------------------------------*/
function renderPage ($pageName) {
	$pageData = db_getPage($pageName);
	if ($pageData == false) {
		return "<b>Failed to load pageData for page:</b> '".$pageName."'";
	}
	if ($pageData['visible'] == "yes") {
		
		//load template
		$useTemplate = $GLOBALS['dirLayout'].$pageData['template'].".html";
		if (file_exists($useTemplate)) {
			$outputContent = implode('', file($useTemplate)); 
		} else {
			return "No Page Template Available.";
		}
		//perform tag replacements
		global $coreTags;
		global $pluginTags;
		if (is_array($pluginTags)) {
			$allTags = array_merge($coreTags, $pluginTags); //coreTags MUST be first!
		} else {
			$allTags = $coreTags;
		}
		for ($k=0; $k<=count($allTags)-1; $k++) {
			$tag = $GLOBALS['tagOpen'].$allTags[$k].$GLOBALS['tagClose'];
			$functionName = "tagReplace_$allTags[$k]";
			$replaceWith = "";
			//check again to be sure that the function exists because
			//plugin writers could have added elements to the array 
			//directly without going through the API
			if (function_exists($functionName) == true) {
				call_user_func("tagReplace_".$allTags[$k], $pageData, &$replaceWith);
				$outputContent = str_replace($tag, $replaceWith, $outputContent);
			}
		}
	}
	return $outputContent;
}//end renderPage

function core_renderPHP($string) {
	function eval_buffer($string) {
		ob_start();
		eval("$string[2];");
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}
	function eval_print_buffer($string) {
		ob_start();
		eval("print $string[2];");
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}
	$string = preg_replace_callback("/(<\?=)(.*?)\?>/si", "eval_print_buffer", $string);
	$string = preg_replace_callback("/(<\?php|<\?)(.*?)\?>/si", "eval_buffer", $string);
	return $string;
}

function tagReplace_page_body($pageData, $replaceVar) {
	//load page source file
	$pageFile = $GLOBALS['dirContent'].$pageData['name'];
	if (file_exists($pageFile)) {
		$pageBody = implode('', file($pageFile));
		//use designated markup for page if parser is available
		if ($pageData['markup'] != "none" ) {
			$functionName = "render_".$pageData['markup'];
			if (function_exists($functionName) == true) {
				//pass pageBody by reference rather than by value
				call_user_func($functionName, &$pageBody);
			}
		}
	} else {
		//source file for page does not exist on disk
		$pageBody = "<b>No content available for page:</b> '".$pageName."'";
	}
	$replaceVar = $pageBody;
}

function tagReplace_script_file_name($pageData, $replaceVar) {
	$replaceVar = $GLOBALS['script_file_name'];
}
function tagReplace_page_title($pageData, $replaceVar) {
	$replaceVar = stripslashes($pageData['name']);
}
function tagReplace_page_style($pageData, $replaceVar) {
	//provides explicit path to stylesheet to allow for for DirectoryPalooza
//	phpinfo();
	$mypath = "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER['PHP_SELF']);
	$layoutpath = $GLOBALS['dirLayout'];
	$layoutpath = str_replace(".", '', $layoutpath); 
	$mypath = str_replace($GLOBALS['this_script'], '', $mypath); 
	$stylePath = $mypath.$layoutpath."default.css";
	$linkStyle = '<link href="'.$stylePath.'" rel="stylesheet" type="text/css">';
	$replaceVar = $linkStyle;
}

/*---------------------------------------------------------------------------
Display functions:
The following fucnitons take the output that has been generated elsewhere
and deliver either public content (display_content) or the whisper user 
interface (display_interface). No other functions should 'echo' any output. ALL 
output is to be in the form of adding strings together and then eventually 
calling one of these common disply functions. run-time messages can be 
concatenated to $_SESSION['message'] to be displayed when the interface is 
finally rendered.
-----------------------------------------------------------------------------*/


function display_login ($pageName='') {
	global $version;
	global $defaultPageName;
	$timeStamp = gettimeofday();
	$PromptTime = $timeStamp['sec'];
	if (isset($_SESSION['loginMessage'])==true) {
		$myMessage = $_SESSION['loginMessage'];
		unset ($_SESSION['loginMessage']);
	}
	set_Headers();
	echo <<< login_html_marker
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Whisper - Please log in</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="googlebot" content="nosnippet, noarchive">
<script type="text/javascript"></script>
<link rel="stylesheet" href="whisper.css" type="text/css">
</head>
<body onLoad="self.focus();document.loginform.input_username.focus()">
<div class="login">
<h1>Welcome to Whisper</h1>
<p><strong>Whisper $version</strong>.  Please log in.</p>
<p class="message">$myMessage</p>
<form name="loginform" action="?command=checkLogin" method="post">
<p>
<label>Name:</label><input type="text" tabindex="1" name="input_username" value="" />
</p>
<p>
<label>Password:</label><input type="password" tabindex="2" name="input_password" value="" />
</p>
<p>
<p><input type="submit" tabindex="3" name="" value="Log in" class="button" /></p>
</p>
<p>Exit to: <a href="?">$defaultPageName</a></p>
<input type="hidden" name="pageName" value="$pageName" />
<input type="hidden" name="PromptTime" value="$PromptTime" />
</form>
</div>
</body>
</html>	
login_html_marker;
	exit; //this line is critical to stop further processing!
}

// Display Whisper's internal user interface
function display_interface($title, $head, $subtitle, $body) { 
	if ($_SESSION['message'] != "") { 
		$message = stripslashes($_SESSION['message']); 
		$message = "<p class='message'>".$message."</p> [<a href='?panel'>Clear this message</a>]"; 
		$_SESSION['message'] = null;
	}
	set_Headers();
	echo <<< global_html_marker
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>$title &mdash; $subtitle</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex, nofollow, noarchive" />
<meta name="googlebot" content="nosnippet, noarchive" />
<link rel="stylesheet" href="whisper.css" type="text/css" />
$head
</head>
<body>
<center>
<div id="main">
<span class="title">$title</span> &nbsp;&nbsp; <span class="subtitle">$subtitle</span>
<div class="main_nav">
	<a href='?command=panel'>Control Panel</a>
	&nbsp;|&nbsp;<a href="?command=manual">Manual</a>
	&nbsp;|&nbsp; <a href="?command=logout">Log out</a> 
	&nbsp;|&nbsp; <a href="?" target="_new">View Site</a>
</div>
$message
$body
</div>
</center>
</body>
</html>
global_html_marker;
exit; //prevent further processing
}

function display_debug() {
	set_Headers();
	echo <<< global_html_marker
<html>
<head>
<title>Whisper Debug</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex, nofollow, noarchive" />
<meta name="googlebot" content="nosnippet, noarchive" />
<style type="text/css"><!--
body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: sans-serif;}
pre {margin: 0px; font-family: monospace;}
a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center !important; }
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold; color: #000000;}
.h {background-color: #9999cc; font-weight: bold; color: #000000;}
.v {background-color: #cccccc; color: #000000;}
i {color: #666666; background-color: #cccccc;}
img {float: right; border: 0px;}
hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
//--></style>
</head>
<body>
<div align='center'>
	<a href="?command=logout">Log out</a> 
	&nbsp;|&nbsp;<a href="?command=manual">Manual</a>
	&nbsp;|&nbsp; <a href='?command=panel'>Control Panel</a>
	&nbsp;|&nbsp; <a href="?" target="_new">View Site</a>
</div>
<center>
<table border="0" cellpadding="3" width="600">
<tr class="h"><td><a href="http://whisper.cx/"><h1 class="p">Whisper</h1></a></td></tr></table>
<table border="0" cellpadding="3" width="600">
global_html_marker;
	echo "<tr><td class='e'>Version</td><td class='v'>".$GLOBALS['version']."</td></tr>\n";
	echo "<tr><td class='e'>Serial</td><td class='v'>".$GLOBALS['serial']."</td></tr>\n";
	echo "<tr><td class='e'>PATH_TRANSLATED</td><td class='v'>".$_SERVER['PATH_TRANSLATED']."</td></tr>\n";
	echo "<tr><td class='e'>xxx</td><td class='v'>".$GLOBALS['xxx']."</td></tr>\n";
	echo "</table></center><p><p>";
	phpinfo();
	exit;
}


// Generate files, either for a site rebuild or on-demand display
function display_content($pageName, $generateFile=false) {
	if ($pageName == "") { 
		$pageName = $GLOBALS['defaultPageName'];
		if ( $GLOBALS['fileGeneration'] == "on" && $GLOBALS['fileGenForwardDefault'] == "on") {
			$forwardTo = $GLOBALS['defaultPageName'].".".$GLOBALS['fileGenExtension'];
			Header ("Location: ".$forwardTo);
			exit;
		}
	}
	$outputContent = renderPage($pageName);

	if ($generateFile == true) {
		//render the content, output the file, and then retrun a link to the file
		$outputContent = preg_replace("/\[\[(.*?)\]\]/e", "'<a href=\"'.str_replace(' ','_','\\1').'.$fileGenExtension\">\\1</a>'", $outputContent);
		// $page is just a page name; $file is the page name with the file extension appended
		$file = "$pageName.".$GLOBALS['fileGenExtension'];
		$file = str_replace(" ", "$fileGenSpaceReplace", $file);
		if ( "/".$file != $_SERVER['PHP_SELF'] ) { 
			touch($file);
			$fp = fopen($file,'w') or die("The file ".$file." could not be opened for writing.  Please change its permissions.");
			fwrite($fp, $outputContent); fclose($fp);
		}
		return ($file);
	} else { 
		//final replace function and then display to browser
		$outputContent = preg_replace("/\[\[(.*?)\]\]/e", "'<a href=\"?'.str_replace(' ','_','\\1').'\">\\1</a>'", $outputContent); 
		set_Headers();
		echo $outputContent;
		exit;
	}
}
