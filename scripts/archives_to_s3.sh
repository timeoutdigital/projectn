#!/bin/bash

ARCHIVE_DATE=`date +"%Y-%m-%d-%H%M%S"`

cd /n/

tar -chjvf vendor_feeds-${ARCHIVE_DATE}.tbz vendor_feeds
s3cmd put vendor_feeds-${ARCHIVE_DATE}.tbz s3://timeout-projectn-backups/vendor_feeds/

if [ `s3cmd ls s3://timeout-projectn-backups/vendor_feeds/vendor_feeds-${ARCHIVE_DATE}.tbz | wc -l | bc` -eq 1 ]; then
    rm vendor_feeds-${ARCHIVE_DATE}.tbz
    rm vendor_feeds/*
fi
