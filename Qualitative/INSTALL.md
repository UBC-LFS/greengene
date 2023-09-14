# GreenGene 1.0

# Installation Manual

http://www.sf.net/projects/yellowleaf    
Licensed under the GNU General Public License.    
Copyright © 2005, YellowLeaf Project.

## Table of Contents
1. Before You Begin
  1. Overview
  1. Requirements
1. Installation
  1. Overview
  1. Preparing the File System
  1. Preparing the Database
  1. Running the Installation Script
1. Troubleshooting

## 1. Before You Begin

### 1.1 Overview

GreenGene is a web-based application to enable instructors to provide genetic breeding simulation to students.

This manual will describe the necessary pre-requisites and steps necessary to complete a successful installation.


### 1.2 Requirements

MySQL 8+
Apache web server (any version should work)
PHP 8+ (Apache must have PHP support – either via php_cgi or mod_php)
LDAP
```
sudo apt-get install php-ldap
```

The above should be pre-installed and working correctly prior to commencing installation.


## 2. Installation

### 2.1 Overview

Including with the installation package is an installation script, install.sh. This is a Bash shell installation script that will assist with the installation of GreenGene, creation of the database schema, etc.

### 2.2 Preparing the File System

First, choose a physical location in which you intend to install GreenGene.

An example of a good location might be /usr/share/greengene.

You must also create a link to a virtual path within Apache. Please see the Apache reference manual for instructions.

### 2.3 Preparing the Database

Create a new database and database user. If you are unable to do so, ask your system administrator to do this for you. Grant full access to this new database to the user.

### 2.4 Running the Installation Script

Run the provided install.sh installation script.

You will be guided through a process to create the database schema, create a default user, and check for appropriate file permissions. You will need to know the virtual, web file location (not include http:// or a server name – just a path), as well as the database host, name, and username and password.

## 3. Troubleshooting

Error logging, primarily used for tracking down database-related and SQL errors, are located in the file located at /logs/error.log (located directly below the root GreenGene folder).
