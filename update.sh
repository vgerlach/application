#!/bin/bash
#
# LICENCE
# This code is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This code is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# @author      Susanne Gottwald <gottwald@zib.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

# TODO add generic function for YES/NO questions?
# TODO add function for abort?
# TODO add batch mode (no questions asked)?

set -o errexit

# =============================================================================
# Define constants
# =============================================================================

# Default installation path for OPUS4
BASEDIR_DEFAULT=/var/local/opus4

# =============================================================================
# Define functions
# =============================================================================

# Determines installation directory for existing OPUS4
function getBasedir() {
    ABORT='n'
    while [ -z $BASEDIR ] || [ ! -d $BASEDIR ] && [ $ABORT != 'y' ]; do 
        echo -e "Please specify OPUS4 installation directory ($BASEDIR_DEFAULT): \c "
        read BASEDIR_NEW
        if [ -z "$BASEDIR_NEW" ]; then
            BASEDIR_NEW=$BASEDIR_DEFAULT
        fi
        # Verify BASEDIR_NEW
        if [ ! -d $BASEDIR_NEW ]; then 
            echo "OPUS4 could not be found at $BASEDIR_NEW"
            echo -e "Would you like to abort the update (y/N)? \c ";
            read ABORT
            if [ -z $ABORT ]; then 
                ABORT='n'
            else 
                # TODO better way of doing the two steps below
                # TODO removing whitespace (trim) does not seem necessary
                ABORT=${ABORT,,} # convert to lowercase
                ABORT=${ABORT:0:1} # get first letter
            fi
        else
            BASEDIR=$BASEDIR_NEW
        fi
        unset BASEDIR_NEW
    done
    if [ $ABORT == 'y' ]; then 
        echo "OPUS4 update aborted"
        exit 1
    fi
    unset ABORT
}

# Determines current version of installed OPUS4
function getOldVersion() {
    if [ ! -f $BASEDIR/VERSION.txt ]; then 
        echo -e "What version of OPUS4 is installed? \c "
        read VERSION_OLD
        # TODO verify version (OPUSVIER-1375), but how?
    else 
        # Read content of VERSION into VERSION_OLD
	VERSION_OLD=$(sed -n '1p' $BASEDIR/VERSION.txt)		
    fi
}

# Determines version of new OPUS4
# TODO Ways to improve, make more robust?
function getNewVersion() {
    VERSION_NEW=$(sed -n '1p' ../VERSION.txt)
}

# Find MD5SUMS for installed OPUS4
# Use file MD5SUMS if it exists, otherwise 
# TODO Ways to make it more robust?
function getMd5Sums() {
    if [ ! -f $BASEDIR/MD5SUMS ]; then
        # TODO use SCRIPTPATH?
        MD5_OLD=../releases/$VERSION_OLD.MD5SUMS
    else
        MD5_OLD=$BASEDIR/MD5SUMS
    fi
}

source update-common.sh

# Advice user to backup old installation before update
# TODO perform backup to user specified or default location? Ask first.
function backup() {
    echo -e "IMPORTANT: You should backup your OPUS $VERSION_OLD installation\c"
    echo " before running the update. Files will be overwritten!"
    echo -e "Start the update to OPUS $VERSION_NEW now [y/N]? \c "
    read UPDATE_NOW
    if [ -z $UPDATE_NOW ]; then 
        UPDATE_NOW='n'
    else
        UPDATE_NOW=${UPDATE_NOW,,}
        UPDATE_NOW=${UPDATE_NOW:0:1}
    fi
    if [ $UPDATE_NOW != 'y' ]; then 
        echo "OPUS4 update aborted"
        exit 1
    fi
}

DEBUG "Debug output enabled"

# Get name and path for update script
SCRIPTNAME=`basename $0`
SCRIPTPATH=$(cd `dirname $0` && pwd)

DEBUG "SCRIPTNAME = $SCRIPTNAME"
DEBUG "SCRIPTPATH = $SCRIPTPATH"

# TODO Is there a way to find the MySQL client?
MYSQL_CLIENT=/usr/bin/mysql
MD5_NEW=../MD5SUMS

# Switch to folder containing update script
# TODO Is that a problem? Can we do without?
cd $SCRIPTPATH

# Determine BASEDIR for old OPUS4 installation
getBasedir

DEBUG "BASEDIR = $BASEDIR"

# Determine version of old OPUS4 installation
getOldVersion

# Determine version of new OPUS4
getNewVersion 

DEBUG "VERSION_OLD = $VERSION_OLD"
DEBUG "VERSION_NEW = $VERSION_NEW"

# TODO Verify that update from that version is supported?

# getMd5Sums

backup

# =============================================================================
# Run update scripts
# =============================================================================

# Update configuration
# TODO $SCRIPTPATH/update-config.sh $BASEDIR

# Update database
# TODO $SCRIPTPATH/update-db.sh $BASEDIR

# Update *import* folder
# TODO $SCRIPTPATH/update-import.sh $BASEDIR

# Update *library* folder
# TODO $SCRIPTPATH/update-library.sh $BASEDIR

# Update modules
# TODO $SCRIPTPATH/update-modules.sh $BASEDIR

# Update *public* folder
# TODO $SCRIPTPATH/update-public.sh $BASEDIR

# Update *scripts* folders
# TODO $SCRIPTPATH/update-scripts.sh $BASEDIR

# Update SOLR index
# TODO $SCRIPTPATH/update-solr.sh $BASEDIR

# =============================================================================
# Finish update
# =============================================================================

# TODO Verify successful update somehow?