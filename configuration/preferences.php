<?
$defaultPageName = "Welcome to Whisper"; // Front Page // The front page is what people see when they access your web site (it is the default page).
$defaultShowHide = "show"; // Show or hide pages // This determines whether new pages are shown or hdden by default (affects all pages when database is rebuilt). Set to "hide" for hidden, or "show" to show.
$defaultMarkup = "textile"; // Preferred Text Markup // This specifies what the default text markup parser will be for new pages. You can set it to the name of a plugin markup parser ("textile" and "markdown" are currently available), or "none".
$fileGeneration = "off"; // File generation // If set to <b>on</b>, your pages will be served to visitors as files (html, php, or whatever extension is set in the following section).  If set to <b>off</b>, your pages will be created and displayed dynamically.
$fileGenPath = "./content/"; // File Generation - Path // This is the _relative_ path to use for file generation. If you want files to be generated in the same directory as this script, then set to   ./  if you want to use the content directory, then it would be ./content/ -- the path must end with a slash.
$fileGenExtension = "html"; // File Generation - extension // If <b>File Generation</b> is set to <b>on</b>, the specified extension will be used for each generated file.
$fileGenForwardDefault = "off"; // File Genreration - Forward Default // If <b>File Generation</b> is set to <b>on</b>, this is useful if you are generating php or shtml files. Rather than have Whisper render the default page when someone arrives at your site without an explicit page request, Whisper will instead forward the browser to the previously generated default page. This has no effect if file generation is not on.
$viewPageTable = "off"; // View Database Table in Control Panel // If set to <b>on</b>, the databse table will be displayed in the main control panel.  If set to <b>off</b>, the database controls will be presented as a separate screen.
$viewPageList = "30"; // View Page List in Control Panel // If View Database Table (above) is set to <strong>off</strong>, this is the number of pages that will be displayed in the main control panel.
$viewTemplates = "off"; // View Templates in Control Panel // If set to <b>on</b>, the Templates control will be displayed in the main control panel.  If set to <b>off</b>, the templates controls will be presented as a separate screen.
$formatDate = "j F Y"; // Date Format // Specify your preferred date display format (php time format).
$formatTime = "g:i A"; // Time Format // Specify your preferred time display format (php time format).
$formatDateTime = "j F Y g:i A"; // DateTime Format // Specify your preferred date + time display format (php time format).
$timezoneOffset = "0"; // Timezone Offset // The number of hours difference between your server time and the time you wish to have displayed (not necessarily offset from GMT).
$inactivityTimeout = "180"; // Inactivitiy Timeout // The number of idle minutes before requiring user to log in again.
$rows = "20"; // Editor - Rows // The number of rows to display in the editor.
$cols = "80"; // Editor - Columns // The number of columns to display in the editor.
?>