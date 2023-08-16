#!/bin/bash

echo "XGene 360 Installation Script."
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

if [ ! -e 'xgene360/includes/global.inc.php' ] || [ ! -e 'greengene/includes/global.php' ]; then
	echo "Missing files. Please correct installation path."
	echo
	exit
fi

echo "OKAY."

echo "STEP 3. Relative URL to installation."
echo "Do not include http:// or hostnames. Do include trailing slashes."
echo "Examples:"
echo "   /xgene360/"
echo "   /~joeprof/xgene360/"
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
# echo "Attempting to connect to database..."
# 
# $MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME < schema.sql
# if [ $? -ne 0 ]; then
# 	echo "Database errors encountered. Please verify your credentials."
# 	exit
# fi
# 
# echo "Database schema created successfully!"
# echo
# echo

# echo "STEP 5. Initial user account."
# echo "You must now pick an initial password for the site administrator account."
# echo "The username of this account is: admin"
# echo
# echo "Enter initial password:"
# read USERPWD
# echo
# echo "Attempting to create default user..."
# 
# echo "INSERT INTO User (UserId, PrivilegeLvl, FirstName, LastName, Pwd) VALUES ('admin', 10, 'Site', 'Administrator', password('$USERPWD'))" | $MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME
# 
# if [ $? -ne 0 ]; then
# 	echo "Error creating default user."
# 	exit
# fi
# 
# echo "Default user created."
# echo
# echo

echo "STEP 6. System Time Zone."
echo "You must specify the numeric time zone where users of XGene 360 will reside."
echo "Do not enter the bracketed abbreviation.  For example only."
echo "Examples:"
echo "   -8        (PST)"
echo "   0         (GMT)"
echo "   +4        (GST)"
echo
echo "Enter system time zone:"
read TIMEZONE

echo
echo
echo


echo "STEP 7. Writing configuration."
echo "<?php
// GLOBAL CONFIGURATION FILE
define('DB_SERVER', '$DBHOST');
define('DB_USERNAME', '$DBUSER');
define('DB_PASSWORD', '$DBPWD');
define('DB_NAME', '$DBNAME');
define('URLROOT', '"$URLROOT"xgene360/');
define( 'GREENGENEURLROOT', '"$URLROOT"greengene/' );
define( 'USER_TIME_ZONE', '$TIMEZONE' );
?>" > xgene360/includes/config.inc.php

if [ $? -ne 0 ]; then
	echo "Error writing configuration file: `pwd`/xgene360/includes/config.inc.php"
	exit
fi

echo "<?php
// GLOBAL CONFIGURATION FILE
define('DBHOST', '$DBHOST');
define('DBUSER', '$DBUSER');
define('DBPWD', '$DBPWD');
define('DBNAME', '$DBNAME');

define('URLROOT', '$URLROOTgreengene/');
define('REALROOT', '`pwd`/greengene');
define('LOGPATH', REALROOT.'/greengene/logs/error.log');
?>" > greengene/includes/config.php

if [ $? -ne 0 ]; then
	echo "Error writing configuration file: `pwd`/greengene/includes/config.php"
	exit
fi

echo "Configuration file written successfully!"

echo
echo
echo

echo "STEP 8. Verify permissions."

touch xgene360/log/sql.log

chmod a+w xgene360/log/sql.log
if [ $? -ne 0 ]; then
	echo "Error setting file permissions."
	exit
fi

touch xgene360/log/transaction.log

chmod a+w xgene360/log/transaction.log
if [ $? -ne 0 ]; then
	echo "Error setting file permissions."
	exit
fi

mkdir greengene/logs/

cat /dev/null > greengene/logs/error.log

touch greengene/logs/error.log

chmod a+w greengene/logs/error.log
if [ $? -ne 0 ]; then
	echo "Error setting file permissions."
	exit
fi

echo "File permissions okay."

echo
echo
echo

echo "Installation complete! You are now ready to begin using XGene 360."
echo "A few things to remember:"
echo "  * all of the settings you entered (except the admin user password) may be modified in the file: "
echo "     `pwd`xgene360/includes/config.inc.php AND greengene/includes/config.php"
echo "  * the error log file exists at `pwd`/xgene360/log/transaction.log, `pwd`/xgene360/log/sql.log and `pwd`/xgene360/log/sql.log"
echo
echo "As a security caution, it's best to delete this installation script."
echo "Type: rm -f install.sh"
echo
echo "Log into XGene 360 using the username: admin and password: $USERPWD"
echo
echo
echo
