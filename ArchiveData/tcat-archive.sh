#!/bin/bash

# This shell script is ff-tcat archive process.
# Author: ninthday <jeffy@ninthday.info>
# Since: 2015-05-05
# Version: v2

# declare variable
source_db="appTwitterCapture"
target_db="tcat_backup"

read -p "Please input MySQL user name: " username
read -s -p "Please input password: " userpasswd
echo ""
read -p "Please input bin name in tcat: " binname

# Database connection string
dbconn="mysql -u $username -p$userpasswd"

# Get bin id by bin name from user input
bin_id=$($dbconn -se "SELECT id FROM $source_db.tcat_query_bins WHERE querybin = '$binname'")

# Copy bin's base infomation to target dabase
# If copy process is success, execute dump function
function copyBin(){
    echo -n "Copy bin's:${binname} base-info to backup database..."
    $dbconn << QUERY
    INSERT INTO $target_db.tcat_query_bins
    SELECT * FROM $source_db.tcat_query_bins WHERE id = $bin_id;
    INSERT INTO $target_db.tcat_query_bins_periods
    SELECT * FROM $source_db.tcat_query_bins_periods WHERE querybin_id = $bin_id;
    INSERT INTO $target_db.tcat_query_bins_phrases
    SELECT * FROM $source_db.tcat_query_bins_phrases WHERE querybin_id = $bin_id;
    INSERT INTO $target_db.tcat_query_phrases
    SELECT $source_db.tcat_query_phrases.* FROM $source_db.tcat_query_bins_phrases
    INNER JOIN $source_db.tcat_query_phrases ON $source_db.tcat_query_phrases.id=$source_db.tcat_query_bins_phrases.phrase_id WHERE querybin_id = $bin_id;
    INSERT INTO $target_db.tcat_search_saved_archive
    SELECT *, NOW() FROM $source_db.tcat_search_saved_archive WHERE querybin_id = $bin_id;
QUERY

    if [ "$?" -eq 0 ]; then
        echo "Success"
        dumpBin
    else
        echo "Error"
    fi
}

# Delete bin's base information in source database
# If delete bin success, execute drop bin table function
function delBin(){
    echo -n "Delete bin's:${binname} base-info..."
    $dbconn << DELQUERY
    DELETE FROM $source_db.tcat_search_saved_archive WHERE querybin_id = $bin_id;
    DELETE $source_db.tcat_query_phrases.* FROM $source_db.tcat_query_phrases
    INNER JOIN $source_db.tcat_query_bins_phrases ON $source_db.tcat_query_bins_phrases.phrase_id=$source_db.tcat_query_phrases.id WHERE querybin_id = $bin_id;
    DELETE FROM $source_db.tcat_query_bins_phrases WHERE querybin_id = $bin_id;
    DELETE FROM $source_db.tcat_query_bins_periods WHERE querybin_id = $bin_id;
    DELETE FROM $source_db.tcat_query_bins WHERE id = $bin_id;
DELQUERY
    if [ "$?" -eq 0 ]; then
        echo "Success"
        dropBintable
    else
        echo "Error"
    fi
}

# Dump table to sql file. After dumping, package and compress all .sql to
# .tar.gz file format.This function will check bin name's directory. If not
# exist, creating directory first. All dump file will put in this directory.
#
# If dump process is success, execute delete bin function
function dumpBin(){
    dir_path="$PWD/$binname"
    [ -d $dir_path ] || mkdir $dir_path
    echo -n "Processing dump tables (Bin name: $binname)..."
    mysqldump -u $username -p$userpasswd $source_db ${binname}_tweets > $dir_path/${binname}_tweets.sql
    mysqldump -u $username -p$userpasswd $source_db ${binname}_hashtags > $dir_path/${binname}_hashtags.sql
    mysqldump -u $username -p$userpasswd $source_db ${binname}_mentions > $dir_path/${binname}_mentions.sql
    mysqldump -u $username -p$userpasswd $source_db ${binname}_urls > $dir_path/${binname}_urls.sql
    if [ "$?" -eq 0 ]; then
        echo "Success"
    else
        echo "Fail"
    fi

    echo -n "Tar and gzip all dump files..."
    cd ${dir_path};tar -zcvf ${binname}.tar.gz ${binname}_*
    if [ "$?" -eq 0 ]; then
        echo "Success"
        delBin
    else
        echo "Fail"
    fi

}

# Drop bin table
function dropBintable(){
    echo -n "Drop bin's:${binname} tables..."
    $dbconn << DROPTABLE
    DROP TABLE IF EXISTS $source_db.${binname}_tweets, $source_db.${binname}_hashtags, $source_db.${binname}_mentions, $source_db.${binname}_urls
DROPTABLE
    if [ "$?" -eq 0 ]; then
        echo "Success"
        echo "Archive mission is completed. Please copy the tar.gz file in $binname directory to NAS."
    else
        echo "Fail"
    fi
}

copyBin

