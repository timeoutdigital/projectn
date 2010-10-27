#!/bin/bash

ARCHIVE_DATE=`date +"%Y-%m-%d-%H%M%S"`

cd /n/
tar -cjvf vendor_feeds-${ARCHIVE_DATE}.tbz vendor_feeds
