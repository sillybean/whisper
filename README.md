# Whisper CMS

The web is noisy. Whisper is quiet.

Whisper is a small site engine, ideal for people seeking to maintain static web content in a simple, straightforward, and convenient manner. It lets you manage your web site — whether it’s simple or sophisticated — without the fuss of complex tools or blog-centric bloatware.

Whisper builds your site dynamically, generating it from templates with a small (but highly expandable) set of custom tags. The result is a clean, consistent site that’s easy to manage. Its control panel lets you modify your templates and stylesheet, create and edit pages, and more. Whisper includes [Textile](http://textile.thresholdstate.com/) and [Markdown](http://daringfireball.net/projects/markdown/), so you don’t have to fuss with HTML in order to add content. Whisper stays out of your way.

Whisper is as flexible as it is simple. Your web pages can be generated as HTML, PHP, or any other type of file that you’d like. Whisper is lightweight and efficient, and does not require a database. It sports a simple yet powerful plugin system, allowing for endless customization and expansion. And it’s 100% free to everyone, and always will be.

## Features

* Flat file system — no database required
* Two modes: dynamic or static file
* Flexible template system that allows…
	* any scripting language (PHP, ASP, etc.)
	* page- and section-specific templates
* Reusable content
* Automatic navigation menus
* Rudimentary news/blog
* Search
* Hidden pages
* Textile and Markdown formatting
* File uploading and management
* Random quotes

### Whisper does not (yet) feature…

* Multiple users
* WYSIWYG (or WYSIWYM) editing
* Advanced search options (boolean logic, etc.)
* Full-featured blogging
* Photo galleries

Comments and trackbacks are noisy. Whisper probably won’t ever have them.

## Requirements

Whisper has been developed and tested using PHP version 4.3.2, though it should work with earlier versions as well.

## Upgrading

To upgrade from any version of Whisper to a newer version, simply replace the old core index.php file with the new one (after changing the user name and password within).

To upgrade an existing version of Pages (beta versions 6.x) to Whisper, follow these steps:

1. Make a backup your database file and store it elsewhere. 
2. Upload whisper_database_update.php to the same directory that houses your existing Pages index.php file. 
3. Run the database update tool from your browser (http://yoursite/pages/whisper_database_update.php). 
4. After you receive confirmation that the update is complete, delete the whisper_database_update.php file. 
5. Open the new Whisper index.php and set your user name and password. 
6. Upload the new index.php file, overwriting your existing one on the server. 
7. Upload whisper.css or things will look ugly. 

It is strongly recommended that you also overwrite your existing configuration file with the one that comes with Whisper. Be sure to backup your old configuration file first (so that you don't lose any data).

## Installing

Open index.php and change the two variables $username and $password to something that you can easily remember.  Those are the only two variables that you'll need to edit within the script itself; the others can all be changed from within the script's control panel.

Next, upload the contents of the Whisper archive (all of the files, along with the "plugins" directory and its contents) to your web server.  Change the permissions on the directory containing the Whisper files to 777.  Then change the permissions on all uploaded Whisper files to 777 as well.

Now, if you access the Whisper script in your browser (http://yoursite/whisper/index.php), you should see the default page that is included with Whisper.  A link to the control panel is provided on this page (or you can access it by browsing to http://yoursite/whisper/?panel).

After logging into the control panel, you may begin to customize your site.