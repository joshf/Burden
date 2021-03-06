Burden Readme
================

Why is lots of work always a burden?

Burden is a full featured task management app written in PHP. The script provides an easy way to add, edit or delete tasks. Tasks are highlighted in different colours depending on their importance and whether or not they are overdue. Each task can also be marked as completed or incomplete. Full sorting and task searching is also included. Please note Burden uses the UK date format of DD/MM/YYYY.

Note: Burden is no longer actively maintained, for a similar product check out chore

Features:
---------

* Tasks can be added via cURL
* Tasks can be marked as important to highlight critical tasks
* Overdue tasks are highlighted clearly
* Tasks can be sorted into categories
* Variety of sorting options
* Works well on mobile devices due to a responsive layout
* Beautiful notifications system thanks to Bootstrap Notify

Donations:
------------

If you like Burden and appreciate my hard work a [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UYWJXFX6M4ADW) (no matter how small) would be appreciated. I code in my spare time and make no money formally from my scripts.

Thanks to:

* Rutger Mensen

Releases:
------------

Releases of Burden can be found on the the [releases page](https://github.com/joshf/Burden/releases).

Installation:
-------------

1. Create a new database using your web hosts control panel (for instructions on how to do this please contact your web host)
2. Download and unzip Burden-xxxx.zip
3. Upload the Burden folder to your server via FTP or your hosts control panel
4. Open up http://yoursite.com/Burden/install in your browser and enter your database/user details
5. Delete the "installer" folder from your server
6. Login to Burden using the username and password you set during the install process
7. Add your tasks
8. Burden should now be set up

Usage:
------

Login to the script and add your tasks. For each task you can set a due date, a category, add extra details and whether or not it is of high importance. You can also easily edit, delete or mark tasks as completed by using the script.

Rather than save all of your categories, Burden will only keep categories for non-deleted tasks. This helps to stop the build up of unnecessary categories.

Updating:
---------

1. Before performing an update please make sure you backup your database
2. Download your config.php file (in the Burden folder) via FTP or your hosts control panel
3. Delete the Burden folder off your server
4. Download the latest version of Burden from [here](https://github.com/joshf/Burden/releases)
5. Unzip the file
6. Upload the unzipped Burden folder to your server via FTP or your hosts control panel
7. Upload your config.php file into the Burden folder
4. Open up http://yoursite.com/Burden/install/upgrade.php in your browser and the upgrade process will start
9. You should now have the latest version of Burden

N.B: The upgrade will only upgrade from the previous version of Burden (e.g 1.4 to 1.5), it cannot be used to upgrade from a historic version.

Removal:
--------

To remove Burden, simply delete the Burden folder from your server and delete the "Data" table from your database.

Support:
-------------

For help and support post an issue on [GitHub](https://github.com/joshf/Burden/issues).

Contributing:
-------------

Feel free to fork and make any changes you want to Burden. If you want them to be added to master then send a pull request via GitHub.
