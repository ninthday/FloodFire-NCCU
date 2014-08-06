#!/bin/bash
# Program:
#   Use to combine csv files that download from eSocialHub (http://www.esocialhub.org/)
#   Before combining, please make sure all file encoding is utf8.
# Author:
#   ninthday (jeffy@ninthday.info)
# History:
# 2014-08-06    First release

# Enter output filename
read -p "Please enter your output filename: " of
OUTFILE=$of".csv"

# Get current directory
DIR="$(pwd)"

# Foreach csv file, the first file include column name, the others JUST data.
doFirst=true
for entry in "$DIR"/*.csv
do
    if [ $doFirst == true ]; then
        `cat $entry > $OUTFILE`
        doFirst=false
        echo "First:" $entry
    else
        `tail -n +2 $entry >> $OUTFILE`
        echo "After_1: " $entry
    fi
    echo "\n" >> $OUTFILE
done
