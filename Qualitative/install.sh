#!/bin/bash

echo "GreenGene Installation Script."
echo "http://yellowleaf.sf.net"
echo
echo "Before proceeding, please have the following information ready:"
echo " * The path to the MySQL binaries"
echo " * MySQL database host, database name, username and password"
echo " * Installation path on web server"
echo
echo "Press <ENTER> when ready, or CTRL-C to cancel."

read

echo "Starting..."

echo "STEP 1. MySQL."
mysql --version > /dev/null
if [ $? -ne 0 ]; then
	echo "MySQL not found in path."
	echo "Please enter the full path to mysql."
	echo "Example:"
	echo "  /usr/local/bin"
	echo "(Do not enter trailing slashes or the mysql binary filename)"
	read MYSQL
	MYSQL="$MYSQL/mysql"
else
	MYSQL=`which mysql`
	echo "MySQL found at: $MYSQL"
fi

echo
echo
echo

echo "STEP 2. Physical (file system) installation path."
echo "Current directory is: `pwd`"

if [ ! -e 'includes/global.php' ]; then
	echo "Missing files. Please correct installation path."
	echo
	exit
fi

echo "OKAY."

echo "STEP 3. Relative URL to installation."
echo "Do not include http:// or hostnames. Do not include trailing slashes."
echo "Examples:"
echo "   /greengene"
echo "   /~joeprof/greengene"
echo
echo "Enter relative URL:"
read URLROOT
echo "Relative URL Entered: $URLROOT"

echo
echo
echo

echo "STEP 4. Database."
echo "This script will verify the database is accessible and create the database schema."
echo "Enter database host:"
read DBHOST
echo "Enter database name:"
read DBNAME
echo "Enter database username:"
read DBUSER
echo "Enter database user password:"
read DBPWD
echo "Attempting to connect to database..."

$MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME < schema.sql
if [ $? -ne 0 ]; then
	echo "Database errors encountered. Please verify your credentials."
	exit
fi

echo "Database schema created successfully!"
echo
echo

echo "STEP 5. Initial user account."
#echo "You must now pick an initial password for the site administrator account."
#echo "The username of this account is: admin"
#echo
#echo "Enter initial password:"
#read USERPWD
#echo
#echo "Attempting to create default user..."

# echo "INSERT INTO User (UserId, PrivilegeLvl, FirstName, LastName, Pwd) VALUES ('admin', 10, 'Site', 'Administrator', password('$USERPWD'))" | $MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME
echo "You must now enter your CWL username for the site administrator account"
read CWLUSERNAME

echo 
echo "Attempting to create default user..."
echo "INSERT INTO User (UserId, FirstName, LastName ) VALUES ($CWLUSERNAME, 'Site', 'Administrator')" | $MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME
echo "INSERT INTO Course (CourseId, Name, Description) VALUES (0, 'AdminCourse', 'Used for creating admin account')" | $MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME
echo "INSERT INTO User_Course (id, uid, cid, PrivilegeLvl) VALUES (0, $CWLUSERNAME, 0, 10)" | $MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME

if [ $? -ne 0 ]; then
	echo "Error creating default user."
	exit
fi

echo "Default user created."
echo
echo

echo "STEP 6. Writing configuration."
echo "<?php
// GLOBAL CONFIGURATION FILE
define('DBHOST', '$DBHOST');
define('DBUSER', '$DBUSER');
define('DBPWD', '$DBPWD');
define('DBNAME', '$DBNAME');
define('URLROOT', '$URLROOT');
define('REALROOT', '`pwd`');
define('LOGPATH', REALROOT.'/logs/error.log');
?>" > includes/config.php

if [ $? -ne 0 ]; then
	echo "Error writing configuration file: `pwd`/includes/config.php"
	exit
fi

echo "Configuration file written successfully!"

echo
echo
echo

echo "STEP 7. Verify permissions."

mkdir logs
touch logs/error.log

chmod a+w logs/error.log
if [ $? -ne 0 ]; then
	echo "Error setting file permissions."
	exit
fi

echo "File permissions okay."

echo
echo
echo

echo "Installation complete! You are now ready to begin using GreenGene."
echo "A few things to remember:"
echo "  * all of the settings you entered (except the admin user password) may be modified in the file: "
echo "     `pwd`/includes/config.php"
echo "  * the error log file exists at `pwd`/logs/error.log"
echo
echo "As a security caution, it's best to delete this installation script."
echo "Type: rm -f install.sh"
echo
echo "Log into GreenGene using the username: admin and password: $USERPWD"
echo
echo
echo
