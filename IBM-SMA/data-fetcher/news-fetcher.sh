#!/bin/bash

output_folder=$1
query="$2"
start_date="$3"
end_date="$4"
tmp_folder="$5"
media_source="$6"
limit="$7"

BASE_URL="http://news.source.today/index/SmaWebService"
title="$(perl -MURI::Escape -e 'print uri_escape($ARGV[0]);' "$2")"

if [ -z "$tmp_folder" ]; then
        tmp_folder=/tmp
fi


q=""

if [ -n "$start_date" ]; then
        if [ -z "$q" ]; then
                q="?q_timestart=$start_date"
        else
                q="${q}&q_timestart=$start_date"
        fi
fi
if [ -n "$end_date" ]; then
        if [ -z "$q" ]; then
                q="?q_timeend=$end_date"
        else
                q="${q}&q_timeend=$end_date"
        fi
fi
if [ -n "$title" ]; then
        if [ -z "$q" ]; then
                q="?q_title=$title"
        else
                q="${q}&q_title=$title"
        fi
fi
if [ -n "$media_source" ]; then
        if [ -z "$q" ]; then
                q="?q_source=$media_source"
        else
                q="${q}&q_source=$media_source"
        fi
fi

if [ -n "$limit" ]; then
        if [ -z "$q" ]; then
                q="?q_limit=$limit"
        else
                q="${q}&q_limit=$limit"
        fi
fi


timestamp=$(date +%s-%N)
curl "$BASE_URL/$q" > $tmp_folder/data-$timestamp.json
cp -R -v "$tmp_folder"/data-$timestamp.json "$output_folder"
rm "$tmp_folder"/data-$timestamp.json

