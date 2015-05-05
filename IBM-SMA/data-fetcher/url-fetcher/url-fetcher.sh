#!/bin/bash

output_folder=$1
query="$2"
tmp_folder="$3"


if [ -z "$tmp_folder" ]; then
        tmp_folder=/tmp
fi

timestamp=$(date +%s-%N)
url=$query
# TODO check
# SECURITY command injection


curl "$url" > $tmp_folder/data-$timestamp.json
cp -v "$tmp_folder"/data-$timestamp.json "$output_folder"
rm "$tmp_folder"/data-$timestamp.json

