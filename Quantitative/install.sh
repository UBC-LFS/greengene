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

if [ ! -e 'xgene360/includes/global.inc.php' ]; then
	echo "Missing files. Please correct installation path."
	echo
	exit
fi

echo "OKAY."

echo "STEP 3. Relative URL to installation."
echo "Do not include http:// or hostnames. Do include trailing slashes."
echo "Examples:"
echo "   /xgene360/"
echo "   /greengene/Quantitative/xgene360/"
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

echo "STEP 7. LDAP service account"
echo "Please enter the details for the LDAP account used to import the class list"
echo "Example LDAP HOST:"
echo "   ldaps://eldapcons.id.ubc.ca"
echo 
echo "Enter the LDAP HOST for the Service Account:"
read LDAP_HOST
echo "LDAP HOST entered: $LDAP_HOST"
echo
echo "Example LDAP DN(Distinguished Name):"
echo "   uid=lfs-svc-greengene,ou=forestry.ubc.ca,dc=id,dc=ubc,dc=ca"
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

if [$? -ne 0]; then 
	echo "Error Connecting to $LDAP_HOST"
	exit
fi

echo  "LDAP details successfully added to config file"
echo 
echo

echo "Step 8. LDAP login"
echo "Please enter the details LDAP used for students to login"
echo "Example LDAP HOST:"
echo "   ldaps://eldapcons.id.ubc.ca" 
echo
echo "Enter the LDAP HOST for the Student Login to connect to:"
read LDAP_LOGIN_HOST
echo "LDAP LOGIN HOST entered: $LDAP_LOGIN_HOST"

echo
echo 

echo "STEP 9. Writing configuration."
echo "<?php
// GLOBAL CONFIGURATION FILE
define('DB_SERVER', '$DBHOST');
define('DB_USERNAME', '$DBUSER');
define('DB_PASSWORD', '$DBPWD');
define('DB_NAME', '$DBNAME');
define('URLROOT', '"$URLROOT"xgene360/');
define( 'GREENGENEURLROOT', '"$URLROOT"greengene/' );
define( 'USER_TIME_ZONE', '$TIMEZONE' );
define( 'LDAP_HOST', '$LDAP_HOST' );
define( 'LDAP_DN', '$LDAP_DN' );
define( 'LDAP_PW', '$LDAP_PW' );
define( 'LDAP_LOGIN_HOST', '$LDAP_LOGIN_HOST' );
?>" > xgene360/includes/config.inc.php

if [ $? -ne 0 ]; then
	echo "Error writing configuration file: `pwd`/xgene360/includes/config.inc.php"
	exit
fi

echo "Configuration file written successfully!"

echo
echo
echo

echo "STEP 10. Verify permissions."

mkdir xgene360/log
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
