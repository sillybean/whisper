<?php 
/*------------------------------------------------------------------*
Search - A plugin for Whisper
This plugin generates lists of the pages on your site.
Based on the search script formerly available at terraserver.de
Modified for Whisper by Stephanie Leary (http://stephanieleary.net) 
REQUIRES:
	Whisper .3
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerPanel('Search');
plugin_registerTag('search_form');
plugin_registerTag('search_results');

function com_Search() {
	authenticate();
	$title = "Whisper";
	$subtitle = "Search";
	$body = pluginPanel_Search();
	display_interface($title, $head, $subtitle, $body);
}

function pluginPanel_Search() {
	$panel .= panelPart_StartBlock("Search");
	$panel .= "<p>This plugin allows people to search your site. To display the search form in a page, use the ". htmlspecialchars($GLOBALS['tagOpen'])."search_form".htmlspecialchars($GLOBALS['tagClose'])." tag. </p><p>An invisible page containing the ".htmlspecialchars($GLOBALS['tagOpen'])."search_results".htmlspecialchars($GLOBALS['tagClose'])." tag has been included with Whisper in order to display the results. If you have deleted it, create a new page called 'Search' and include the ".htmlspecialchars($GLOBALS['tagOpen'])."search_results".htmlspecialchars($GLOBALS['tagClose'])." tag.</p>";
	$panel .= "<h3>Search your site:</h3>\n";
	$panel .= print_search_form(true);
	if (!empty($_POST['keyword'])) 
		$panel .= search_pages(true);
	$panel .= panelPart_EndBlock();
	return $panel;
}

function tagReplace_search_form($pageData, $return) {
	$form .= print_search_form(false);
	$return = $form;
}

function tagReplace_search_results($pageData, $return) {
	$results = search_pages(false);
	$return = $results;
}

function print_search_form($internal) {
	@$keyword=$_POST['keyword'];
	@$case=$_POST['case'];
	@$limit=$_POST['limit'];
	$key = str_replace("&amp;","&",htmlentities($keyword));
	$action="";
	if ($internal) $action = "command=";
	$form =	"<form action=\"?".$action."Search\" method=\"POST\">\n	<input type=\"text\" name=\"keyword\" class=\"text\" size=\"$cols\"  maxlength=\"30\" 
		value=\"".$key."\" onFocus=\" if (value == '".$key."') {value=''}\" onBlur=\"if (value == '') {value='".$key."'}\">\n ";
	$form .= "<input type=\"hidden\" value=\"60\" name=\"limit\">\n";
	$form .= "<input type=\"submit\" value=\"go\" class=\"button\">\n <br> \n<label for=\"checkbox\">Match case</label> <input type=\"checkbox\" name=\"case\" 
		value=\"true\" class=\"checkbox\"";
	if($case) $form .= " checked=\"checked\"";
	$form .= ">\n</form>\n";
	return $form;
}

function search_pages($internal) {
	global $dbRecordsArray;
	@$keyword=$_POST['keyword'];
	@$limit=$_POST['limit'];
	@$case=$_POST['case'];
	$result = '';
	if (strlen($keyword) < 3) 
	{	$result = "<p class=\"error\">Please enter at least three characters.</p>\n"; }
	else {
		foreach ($dbRecordsArray as $dbRecord) {
			//if (get_ShowHide($dbRecord['showhide'])!='hide') { 	
			if ($dbRecord['visible'] == "yes") {
				$title = stripslashes($dbRecord['name']);
				$text = render_page_body($dbRecord, "");
				$keyword_html = htmlentities($keyword);
				// from here to 94, this isn't working quite right
				// see if the keyword appears in the title
				if ($case)
					$do = strstr($title, $keyword);
				else
					$do = stristr($title, $keyword);
				if (!$do) {
					// see if the keyword appears in either the content or the source code
					if ($case)  
						$do = strstr($text, $keyword)||strstr($text, $keyword_html);  // case-sensitive
					else 
						$do = stristr($text, $keyword)||stristr($text, $keyword_html);  // case-insensitive
				}
				// if so, add a match, create the link to the file, and grab the surrounding text for the excerpt
				if ($do) {	
					$result .= "<li>";
					if ($internal) // admins get a link to edit individual page
						$result .= '<a href="?command=edit&mode=page&file='.$title.'" title="Edit '.$title.'">'.$title.'</a>'; 
					else  
						if (((strcmp($dbRecord['parent'], "none") == 0) || (empty($dbRecord['parent']))) && (is_array(explode(";", $dbrecord['sections']))))  // no parent
							$result .= get_pageLink($dbRecord['name']); // we need the slashes here 
						else // this is an included section, and we want visitors to see it as part of its parent rather than a standalone page
							$result .= get_pageLink(stripslashes($dbRecord['parent'])); 
					// we don't want to show source code in our excerpt
					$bare_content = strip_tags($text);
					$keyword = addslashes(preg_quote($keyword));
					$keyword_html = addslashes(preg_quote($keyword_html));
					$result .= "<br><span class=\"extract\">";
					if(preg_match_all("/((\s\S*){0,3})($keyword|$keyword_html)((\s?\S*){0,3})/i", $bare_content, $match, PREG_SET_ORDER)) {
						// print an excerpt for each match found
						for ($h=0;$h<count($match);$h++) {
							if (!empty($match[$h][3]))
								$result .= sprintf("<em><strong>...</strong> %s<strong>%s</strong>%s <strong>...</strong></em>", $match[$h][1], $match[$h][3], $match[$h][4]);
						}
					}
					else // the match was only in the title, so we'll show the first 40 characters of the file's content
						$result .= "<em>".substr($bare_content, 0, 40)."...</em>";
					$result .= "</span></li>\n";
				} // if ($do)
			} // if hide
		} // foreach
		if (empty($result)) $result = "<p class=\"error\">No matches were found.</p>\n";
		else $result = "<ol class=\"results\">".$result."</ol>\n";
	}
	return $result;  
}

// almost a copy of function tagReplace_page_body; only the return and fileGeneration check are added
function render_page_body($pageData, $replaceVar) {
	//load page source file
	$pageFile = $GLOBALS['dirContent'].$pageData['name'];
	if ($GLOBALS['fileGeneration'] == "on") { 
		$pageFile = str_replace(" ", $GLOBALS['fileGenSpaceReplace'], $pageFile); 
		$pageFile = $pageFile.".".$GLOBALS['fileGenExtension'];
	}
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
		$pageBody = "<b>No content available for page:</b> '".stripslashes($pageName)."'";
	}
//	$pageBody = core_renderPHP ($pageBody);
	$replaceVar = $pageBody;
	return $replaceVar;
}
?>