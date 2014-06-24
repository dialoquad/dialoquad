Dialoquad Wordpress on OpenShift
======================

This git repository helps you get up and running quickly w/ a Wordpress installation
on OpenShift.  The backend database is MySQL and the database name is the
same as your application name (using getenv('OPENSHIFT_APP_NAME')).  You can name
your application whatever you want.  However, the name of the database will always
match the application so you might have to update .openshift/action_hooks/build.


Running on OpenShift
----------------------------

Create an account at https://www.openshift.com and install the client tools (run 'rhc setup' first)

Create a php-5.4 application (you can call your application whatever you want)

    rhc app create wordpress php-5.4 mysql-5.5 --from-code=https://github.com/openshift/wordpress-example

That's it, you can now checkout your application at:

    http://wordpress-$yournamespace.rhcloud.com

You'll be prompted to set an admin password and name your WordPress site the first time you visit this
page.

Note: When you upload plugins and themes, they'll get put into your OpenShift data directory
on the gear ($OPENSHIFT_DATA_DIR).  If you'd like to check these into source control, download the
plugins and themes directories and then check them directly into php/wp-content/themes, php/wp-content/plugins.

Notes
=====

GIT_ROOT/.openshift/action_hooks/deploy:
    This script is executed with every 'git push'.  Feel free to modify this script
    to learn how to use it to your advantage.  By default, this script will create
    the database tables that this example uses.

    If you need to modify the schema, you could create a file
    GIT_ROOT/.openshift/action_hooks/alter.sql and then use
    GIT_ROOT/.openshift/action_hooks/deploy to execute that script (make sure to
    back up your application + database w/ 'rhc app snapshot save' first :) )

Security Considerations
-----------------------
Consult the WordPress documentation for best practices regarding securing your wordpress installation.  OpenShift
automatically generates unique secret keys for your deployment into wp-config.php, but you may feel more
comfortable following the WordPress documentation directly.



===========Git Hook Settings============
#pre-push hook scripts

if rhc ssh dialoquad --command '[ -d app-root/repo/wp-content/uploads ]'
then	
	echo "Found uploads folder"
	if rhc ssh dialoquad --command '[ -d app-root/data/uploads ]'
	then
		echo "Error the app-root/data/ is not clean for moving folder"
		exit 1
	fi
	echo "Moving cache setting files"
	rhc ssh dialoquad --command mv app-root/repo/wp-content/wp-cache-config.php app-root/data/
	echo "Moving app-root/repo/wp-content/uploads to app-root/data/"
	rhc ssh dialoquad --command mv app-root/repo/wp-content/uploads app-root/data/

else
	echo "Error /repo/wp-content/uploads folder doesn't exist"
	exit 1
fi

exit 0


#git remote set-url --push origin ssh://539fc8cbe0b8cddb680000cd@dialoquad-four.rhcloud.com/~/git/dialoquad.git/
#git push
#git remote set-url --push origin no_push


#post-push hook scripts

echo "Cleaning app-root/repo/wp-content/uploads for moving folder"
rhc ssh dialoquad --command rm -rf app-root/repo/wp-content/uploads
echo "Restoring data/uploads/ for CDN"
rhc ssh dialoquad --command mv app-root/data/uploads app-root/repo/wp-content/
echo "Restoring cache setting files/"
rhc ssh dialoquad --command mv app-root/data/wp-cache-config.php app-root/repo/wp-content/
exit 0





===========Openshift Settings============


Feel free to change or remove this file, it is informational only.

Repo layout
===========
php/ - Externally exposed php code goes here
libs/ - Additional libraries
misc/ - For not-externally exposed php code
../data - For persistent data (full path in environment var: OPENSHIFT_DATA_DIR)
.openshift/pear.txt - list of pears to install
.openshift/action_hooks/pre_build - Script that gets run every git push before the build
.openshift/action_hooks/build - Script that gets run every git push as part of the build process (on the CI system if available)
.openshift/action_hooks/deploy - Script that gets run every git push after build but before the app is restarted
.openshift/action_hooks/post_deploy - Script that gets run every git push after the app is restarted


Notes about layout
==================
Please leave php, libs and data directories but feel free to create additional
directories if needed.

Note: Every time you push, everything in your remote repo dir gets recreated
please store long term items (like an sqlite database) in ../data which will
persist between pushes of your repo.


Environment Variables
=====================

OpenShift provides several environment variables to reference for ease
of use.  The following list are some common variables but far from exhaustive:

    getenv('OPENSHIFT_APP_NAME')  - Application name
    getenv('OPENSHIFT_DATA_DIR')  - For persistent storage (between pushes)
    getenv('OPENSHIFT_TMP_DIR')   - Temp storage (unmodified files deleted after 10 days)

When embedding a database using 'rhc cartridge add', you can reference environment
variables for username, host and password:

    getenv('OPENSHIFT_MYSQL_DB_HOST')      - DB host
    getenv('OPENSHIFT_MYSQL_DB_PORT')      - DB Port
    getenv('OPENSHIFT_MYSQL_DB_USERNAME')  - DB Username
    getenv('OPENSHIFT_MYSQL_DB_PASSWORD')  - DB Password

To get a full list of environment variables, simply add a line in your
.openshift/action_hooks/build script that says "export" and push.

pear.txt
===========

A list of pears to install, line by line on the server.  This will happen when
the user git pushes.
