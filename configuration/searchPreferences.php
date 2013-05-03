<?
$s_skip = array("..","."); // File Types // Which files/dirs do you like to skip?
$s_files = "$"; // File Types // Which files types should be searched? Example: "html$|htm$|php4$"
$min_chars = "3"; // Minimum Term // Min. chars that must be entered to perform the search
$max_chars = "30"; // Maximum Term // Max. chars that can be submited to perform the search
$default_val = "";  // Sample Search // Default value in searchfield
$limit_hits = array("9999"); // Limit Hits // How many hits should be displayed, to suppress the select-menu simply use one value in the array --> array("100")
$msg_invalid_search = "Invalid Search term!"; // Messages // Invalid searchterm
$msg_invalid_search_long = "Please enter at least '$min_chars', and no more than '$max_chars' characters."; // Messages // Invalid searchterm long ($min_chars/$max_chars)
$msg_results_heading = "Your search results for:"; // Messages // Headline searchresults
$msg_no_hits = "Sorry, we couldn't find anything that matched."; // Messages // No hits
$msg_hits = "results"; // Messages // Hits
$msg_match_case = "Match case"; // Messages // Match case
$no_title = "Untitled"; // Messages // This should be displayed if no title or empty title is found in file
$limit_extracts_extracts = ""; // Excerpts // How many excerpts per file do you like to display. Default: "" --> every extract, alternative: 'integer' e.g. "3"
$byte_size = "51200"; // Byte Limit // How many bytes per file should be searched? Reduce to increase speed
?>.