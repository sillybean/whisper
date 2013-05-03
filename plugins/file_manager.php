<?php 
/*------------------------------------------------------------------*
File Manager 2.0 -- A plugin for Whisper
(c) 2004 Adam Newbold [http://www.neatnik.net/]
This plugin allows for simple file management through any web browser.
REQUIRES:
	Whisper .2
*------------------------------------------------------------------*/

// The following code ensures that the plugin is not accessed directly (for security).
if ($_SERVER['PHP_SELF'] == substr(__FILE__, -strlen($_SERVER['PHP_SELF']))) { 
	echo "This file cannot be accessed directly."; 
	exit; 
}

plugin_registerPanel('FileManager');
plugin_registerCommand('FileManager');
plugin_registerCommand('FM_UploadFile');
plugin_registerCommand('FM_DeleteFile');



function com_FileManager() {
	authenticate();
	$title = "Whisper";
	$subtitle = "File Manager";
	$body = pluginPanel_FileManager();
	display_interface($title, $head, $subtitle, $body);
}


function com_FM_UploadFile() {
	authenticate();
	$tmpname = $_FILES['userfile']['tmp_name'];
	$file_name = $_FILES['userfile']['name'];
	if ( $file_name != '') {
		$file_data = implode(file($tmpname), '');
		$file_to_write = fopen($_SESSION['FM_explorePath']."/".$file_name, "w");
		fwrite($file_to_write, $file_data);
		fclose($file_to_write);
		$_SESSION['message'] .= "$file_name has been uploaded successfully.";
	} else { 
		$_SESSION['message'] .= "Please specify a file name/path to upload a file."; 
	}
	com_FileManager();
}

function com_FM_DeleteFile() {
	authenticate();
	$killFile = $_SESSION['FM_explorePath']."/".$_GET['delete'];
	if(is_dir($killFile)) {
		$_SESSION['message'] .= "$killFile could not be deleted because it is a directory.";
	} else {
		unlink($killFile);
		$_SESSION['message'] .= "$killFile  has been deleted.";
	}
	com_FileManager();
}

function pluginPanel_FileManager() {
	authenticate();
	if (!isset($_SESSION['FM_explorePath'])) {
		$_SESSION['FM_explorePath'] = $dirUploads; // begin at whisper uploads directory
	}
	if (isset($_GET['subDir']) == true ) {
		$_SESSION['FM_explorePath'] = $_SESSION['FM_explorePath']."/".$_GET['subDir'];
	} elseif (isset($_GET['upOne']) == true ) {
		$_SESSION['FM_explorePath'] = dirname($_SESSION['FM_explorePath']);
	}
	if (!is_dir($_SESSION['FM_explorePath'])) {
		$_SESSION['FM_explorePath'] = ".";
	}

	define('S_IFMT',0170000);   // mask for all types 
	define('S_IFSOCK',0140000); // type: socket 
	define('S_IFLNK',0120000);  // type: symbolic link 
	define('S_IFREG',0100000);  // type: regular file 
	define('S_IFBLK',0060000);  // type: block device 
	define('S_IFDIR',0040000);  // type: directory 
	define('S_IFCHR',0020000);  // type: character device 
	define('S_IFIFO',0010000);  // type: fifo 
	define('S_ISUID',0004000);  // set-uid bit 
	define('S_ISGID',0002000);  // set-gid bit 
	define('S_ISVTX',0001000);  // sticky bit 
	define('S_IRWXU',00700);    // mask for owner permissions 
	define('S_IRUSR',00400);    // owner: read permission 
	define('S_IWUSR',00200);    // owner: write permission 
	define('S_IXUSR',00100);    // owner: execute permission 
	define('S_IRWXG',00070);    // mask for group permissions 
	define('S_IRGRP',00040);    // group: read permission 
	define('S_IWGRP',00020);    // group: write permission 
	define('S_IXGRP',00010);    // group: execute permission 
	define('S_IRWXO',00007);    // mask for others permissions 
	define('S_IROTH',00004);    // others: read permission 
	define('S_IWOTH',00002);    // others: write permission 
	define('S_IXOTH',00001);    // others: execute permission 

	// open the specified directory 
	$explorePath = $_SESSION['FM_explorePath'];
	if ($_SESSION['FM_explorePath'] == ".") {
		$body .= '<p class="message">You are in the Whisper root directory. Altering these files could be disastrous. Proceed with caution.</p>';
	}
	$dirObj = dir($explorePath) or die("can't open $explorePath -- $php_errormsg");
	$body .= '<p>Browsing: ' . $_SESSION['FM_explorePath'] . '</p>';
	$body .= '<table cellspacing="0">';
	$body .= '<tr>';
	$body .= '<th>Permissions</th>';
	$body .= '<th>User</th>';
	$body .= '<th>Group</th>';
	$body .= '<th>Size</th>';
	$body .= '<th>Modified</th>';
	$body .= '<th>Name</th>';
	$body .= '<th class="pageTableAlert">Del</th>';
	$body .= '</tr>';
	$count = 0;
	// read each entry in the directory 
	while (($curFileObj = $dirObj->read()) !== false ) {
		$fileInfo = lstat($dirObj->path.'/'.$curFileObj);    // get information about this file 
		$user_info = posix_getpwuid($fileInfo['uid']);       // translate uid into user name 
		$group_info = posix_getgrgid($fileInfo['gid']);      // translate gid into group name 
		$date = strftime('%b %e %H:%M',$fileInfo['mtime']);  // format the date for readability 
		$mode = mode_string($fileInfo['mode']);              // translate the octal mode into a readable string 
		$mode_type = substr($mode,0,1);
		if (($mode_type == 'c') || ($mode_type == 'b')) {
			/* if it's a block or character device, print out the major and
			 * minor device type instead of the file size */
			$major = ($fileInfo['rdev'] >> 8) & 0xff;
			$minor = $fileInfo['rdev'] & 0xff;
			$size = sprintf('%3u, %3u',$major,$minor);
		} else {
			
			$size = size_readable($fileInfo['size']);
		}
		// create appropriate link around the filename
		if (is_dir(realpath($dirObj->path.'/'.$curFileObj))) {
			switch ($curFileObj) {
				case ".":
					$href = ".";
					break;
				case "..":
					$href = sprintf('<a href="?command=FileManager&upOne='.$dirObj->path.'">'.$curFileObj.'</a>',$_SERVER['PHP_SELF'],$href,$curFileObj);
					break;
				default:
					// browse other directories with web-ls
					$href = sprintf('<a href="?command=FileManager&subDir='.$curFileObj.'">'.$curFileObj.'</a>',$_SERVER['PHP_SELF'],$href,$curFileObj);
			}
		} else { // simple link to files to download them
			$href = $explorePath.'/'.urlencode($curFileObj);
			$href = str_replace('%2F','/',$href);
			$href= sprintf('<a href="%s" target="_blank">%s</a>',$href,$curFileObj);
		}
		// if it's a link, show the link target, too
		if ($mode_type=='l') {
			$href .= ' -&gt; ' . readlink($dirObj->path.'/'.$curFileObj);
		}
		if($count & 1) { 
			$class = "dark"; 
		} else { 
			$class = "light"; 
		}
		$body .= '<tr class="'.$class.'">';
		$body .= '<td>'.$mode.'</td>';
		$body .= '<td>'.$user_info['name'].'</td>';
		$body .= '<td>'.$group_info['name'].'</td>';
		$body .= '<td>'.$size.'</td>';
		$body .= '<td>'.$date.'</td>';
		$body .= '<td>'.$href.'</td>';
		$body .= '<td class="pageTableAlert">';
		$body .= '<a href="#" onclick="toggle(\'span'.$count.'\'); return false;" title="Delete '.$curFileObj.'">x</a>';
		$body .= '<span id="span'.$count.'" style="display: none"> &nbsp;<strong><a href="?command=FM_DeleteFile&delete='.$curFileObj.'" title="Confirm deletion of '.$curFileObj.'">Confirm</a></strong></span>';
		$body .= '</td>';
		$body .= '</tr>';
		$count++;
	}//end while
	$body .= '</table>';
	$title = 'Whisper';
	$subtitle = 'File Manager';
	$body .= "<br><hr><br>";
	$body .= '<form enctype="multipart/form-data" action="?command=FM_UploadFile" method="post">';
	$body .= 'Upload a file <input name="userfile" type="file">';
	$body .= '<input type="submit" value="Send File">';
	$body .= '</form>';
	$head = '
	<script type="text/javascript">
	<!-- 
	// toggle visibility 
	function toggle( targetId ){ 
	  if (document.getElementById){ 
			target = document.getElementById( targetId ); 
			   if (target.style.display == "none"){ 
				  target.style.display = ""; 
			   } else { 
				  target.style.display = "none"; 
			   } 
		 } 
	} 
	-->
	</script>';
	display_interface($title, $head, $subtitle, $body);
}

function size_readable ($size, $retstring = null) {  
	// from comment at http://us2.php.net/filesize
	// adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
	$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	if ($retstring === null) { $retstring = '%01.2f %s'; }
	$lastsizestring = end($sizes);
	foreach ($sizes as $sizestring) {
			if ($size < 1024) { break; }
			if ($sizestring != $lastsizestring) { $size /= 1024; }
	}
	if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; } // Bytes aren't normally fractional
	return sprintf($retstring, $size, $sizestring);
}


/* mode_string() is a helper function that takes an octal mode and returns
 * a ten character string representing the file type and permissions that
 * correspond to the octal mode. This is a PHP version of the mode_string()
 * function in the GNU fileutils package.
 */
function mode_string($mode) {
	$fileInfo = array();

	// set type letter 
	if (($mode & S_IFMT) == S_IFBLK) {
		$fileInfo[0] = 'b';
	} elseif (($mode & S_IFMT) == S_IFCHR) {
		$fileInfo[0] = 'c';
	} elseif (($mode & S_IFMT) == S_IFDIR) {
		$fileInfo[0] = 'd';
	} elseif (($mode & S_IFMT) ==  S_IFREG) {
		$fileInfo[0] = '-';
	} elseif (($mode & S_IFMT) ==  S_IFIFO) {
		$fileInfo[0] = 'p';
	} elseif (($mode & S_IFMT) == S_IFLNK) {
		$fileInfo[0] = 'l';
	} elseif (($mode & S_IFMT) == S_IFSOCK) {
		$fileInfo[0] = 's';
	}

	// set user permissions 
	$fileInfo[1] = $mode & S_IRUSR ? 'r' : '-';
	$fileInfo[2] = $mode & S_IWUSR ? 'w' : '-';
	$fileInfo[3] = $mode & S_IXUSR ? 'x' : '-';

	// set group permissions 
	$fileInfo[4] = $mode & S_IRGRP ? 'r' : '-';
	$fileInfo[5] = $mode & S_IWGRP ? 'w' : '-';
	$fileInfo[6] = $mode & S_IXGRP ? 'x' : '-';

	// set other permissions 
	$fileInfo[7] = $mode & S_IROTH ? 'r' : '-';
	$fileInfo[8] = $mode & S_IWOTH ? 'w' : '-';
	$fileInfo[9] = $mode & S_IXOTH ? 'x' : '-';

	// adjust execute letters for set-uid, set-gid, and sticky 
	if ($mode & S_ISUID) {
		if ($fileInfo[3] != 'x') {
			// set-uid but not executable by owner 
			$fileInfo[3] = 'S';
		} else {
			$fileInfo[3] = 's';
		}
	}
	if ($mode & S_ISGID) {
		if ($fileInfo[6] != 'x') {
			// set-gid but not executable by group 
			$fileInfo[6] = 'S';
		} else {
			$fileInfo[6] = 's';
		}
	}
	if ($mode & S_ISVTX) {
		if ($fileInfo[9] != 'x') {
			// sticky but not executable by others 
			$fileInfo[9] = 'T';
		} else {
			$fileInfo[9] = 't';
		}
	}
	// return formatted string 
	return join('',$fileInfo);
}//end mode_string()

?>
