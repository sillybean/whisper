Whisper - Quiet content managment
Copyright (c) 2004 by Adam Newbold.

README - Revised May 11, 2004

______________________________________________________________________

Whisper is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.

Whisper is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Whisper; if not, write to the Free Software Foundation, Inc.,
59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

______________________________________________________________________

Whisper uses Textile.

Copyright (c) 2003-2004, Dean Allen <dean@textism.com>
All rights reserved.

______________________________________________________________________


REQUIREMENTS
------------

Whisper has been developed and tested using PHP version 4.3.2, though it should work with earlier versions as well.


UPGRADING
---------


To upgrade from any version of Whisper to a newer version, simply replace the old core index.php file with the new one (after changing the user name and password within).

To upgrade an existing version of Pages (beta versions 6.x) to Whisper, follow these steps:

1. Make a backup your database file and store it elsewhere. 
2. Upload whisper_database_update.php to the same directory that houses your existing Pages index.php file. 
3. Run the database update tool from your browser (http://yoursite/pages/whisper_database_update.php). 
4. After you receive confirmation that the update is complete, delete the whisper_database_update.php file. 
5. Open the new Whisper index.php and set your user name and password. 
6. Upload the new index.php file, overwriting your existing one on the server. 
7. Upload whisper.css or things will look ugly. 

It is strongly recommended that you also overwrite your existing configuration file with the one that comes with Whisper. Be sure to backup your old configuration file first (so that you don’t lose any data).


NEW INSTALLATION
----------------

Open index.php and change the two variables $username and $password to something that you can easily remember.  Those are the only two variables that you'll need to edit within the script itself; the others can all be changed from within the script's control panel.

Next, upload the contents of the Whisper archive (all of the files, along with the "plugins" directory and its contents) to your web server.  Change the permissions on the directory containing the Whisper files to 777.  Then change the permissions on all uploaded Whisper files to 777 as well.

Now, if you access the Whisper script in your browser (http://yoursite/whisper/index.php), you should see the default page that is included with Whisper.  A link to the control panel is provided on this page (or you can access it by browsing to http://yoursite/whisper/?panel).

After logging into the control panel, you may begin to customize your site.


TROUBLESHOOTING
---------------

If you run into any problems, please let me know. 

Adam Newbold
whisper@whisper.cx
