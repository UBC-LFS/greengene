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
echo "Examples:"
echo "   https://dev-greengene.landfood.ubc.ca"
echo "   https://192.168.0.18"
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
echo "Please enter your CWL Username for the site administrator account."
echo "This CWL User will be allowed to enter the application as a site Administrator."
echo
echo "Enter administrator CWL:"
read USERNAME
echo
echo "Attempting to create default user..."

echo "INSERT INTO User (UserId, PrivilegeLvl, FirstName, LastName) VALUES ('$USERNAME', 10, 'Site', 'Administrator')" | $MYSQL -h $DBHOST -u $DBUSER --password=$DBPWD $DBNAME

if [ $? -ne 0 ]; then
	echo "Error creating default user."
	exit
fi

echo "Default user created."
echo
echo

echo "STEP 6. LDAP service account"
echo "Please enter the details for the LDAP account used to import the class list"
echo "Example LDAP HOST:"
echo "   ldaps://eldapcons.id.ubc.ca"
echo 
echo "Enter the LDAP HOST for the Service Account:"
read LDAP_HOST
echo "LDAP HOST entered: $LDAP_HOST"
echo
echo "Example LDAP DN(Distinguished Name):"
echo "   uid=lfs-svc-greengene,ou=forestry,dc=ubc,dc=ca"
echo 
echo "Enter LDAP DN for the Service Account: "
read LDAP_DN 
echo "LDAP DN entered: $LDAP_DN"
echo 
echo "Enter the LDAP Password for the Service Account:"
read LDAP_PW
echo "LDAP Password entered: $LDAP_PW"
echo
echo "Attempting to connect to $LDAP_HOST ..."

echo "ldapsearch -x -H $LDAP_HOST -D \"$LDAP_DN\" -w $LDAP_PW"

if [ $? -ne 0 ]; then
	echo "Error Connecting to $LDAP_HOST "
	exit
fi

echo "LDAP details successfully added to config file"
echo 
echo

# TODO: STEP 7 LDAP Configuration for ldap login 

echo "STEP 8. Writing configuration."
echo "<?php
// GLOBAL CONFIGURATION FILE
define('DBHOST', '$DBHOST');
define('DBUSER', '$DBUSER');
define('DBPWD', '$DBPWD');
define('DBNAME', '$DBNAME');
define('URLROOT', '$URLROOT');
define('REALROOT', '`pwd`');
define('LOGPATH', REALROOT.'/logs/error.log');
define('LDAP_HOST', '$LDAP_HOST');
define('LDAP_DN', '$LDAP_DN');
define('LDAP_PW', '$LDAP_PW');

// SSL
define('SSL_KEY_PATH', '');
define('SSL_CERTIFICATE_PATH', '');
define('SSL_CA_CERTIFICATE_PATH', '');

?>" > includes/config.php

if [ $? -ne 0 ]; then
	echo "Error writing configuration file: `pwd`/includes/config.php"
	exit
fi

echo "Configuration file written successfully!"

echo
echo
echo

echo "STEP 9. Verify permissions."

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

echo "Installation complete! Please include your SSL certificates into config.php. Afterwards, you can begin using GreenGene."
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
