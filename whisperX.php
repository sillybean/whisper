<?php
/*
The purpose of this file is store keep the username and password 
outside of the publicly accessible web root in order to make the 
script a little more secure.
This file is only included if $SecurePass is set to "true" AND php can 
successfully read the file at the path specified by $SecurePassPath
*/
$GLOBALS['username'] = "username";   // Your user name
$GLOBALS['password'] = "password";   // Your password
?>