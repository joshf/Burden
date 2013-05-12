Burden Readme
================

Why is lots of work always a burden?

Burden is a full featured task management app written in PHP. The script comes with an admin panel which provides an easy way to add, edit or delete tasks. Tasks are highlighted in different colours depending on their importance or whether they are overdue. Each task can also be marked as completed or incomplete. Full sorting and task search is also included.

N.B: Burden was called SHTask whilst in beta, I decided to start from fresh so created a new repo for Burden.

#### Current Version: 1.2 "DangerousDuck"

Features:
---------

* Tasks can be added via cURL
* Tasks can be marked as important to highlight critical tasks
* Tasks can have a blank due date
* Overdue tasks are highlighted clearly
* Tasks can be sorted into categories
* Themed by Twitter Bootstrap, extra themes available through BootSwatch
* Sort and search tasks using DataTables
* Works well on mobile devices due to a responsive layout

Screenshots:
------------

Screenshots of Burden can be found [here](http://imgur.com/a/mmqhA).

Downloads:
------------

[v1.2](http://sidhosting.co.uk/downloads/get.php?ref=github&id=burden&tag=1.2) (released 16/02/2013)

Installation:
-------------

1. Create a new database using your web hosts control panel (for instructions on how to do this please contact your web host)
2. Download and unzip Burden-xxxx.zip
3. Upload the Burden folder to your server via FTP or your hosts control panel
4. Open up http://yoursite.com/Burden/installer in your browser and enter your database/user details
5. Delete the "installer" folder from your server
6. Login to the admin panel using the username and password you set during the install process
7. Add your tasks
8. Burden should now be set up

Usage:
------

Login to the script and add your tasks. For each task you can set a due date, its category and whether it is of high importance. You can also easily edit, deleted or mark tasks as completed by using the admin panel.

Rather than save all of your categories, Burden will only keep categories for non-deleted tasks. This helps to stop the build up of unnecessary categories.

Removal:
--------

To remove Burden, simply delete the Burden folder from your server and delete the "Data" table from your database.

Support:
-------------

For help and support post an issue on [GitHub](https://github.com/joshf/Burden/issues).

Contributing:
-------------

Feel free to fork and make any changes you want to Burden. If you want them to be added to master then send a pull request.