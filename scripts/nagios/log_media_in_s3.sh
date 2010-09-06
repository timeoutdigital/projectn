#!/bin/bash

# --------------------------------------------------------------------------------------------------------

# Check projectn media files in exports are available on s3.
# Author: Peter Johnson - peterjohnson@timeout.com -- 27-Aug-10

# --------------------------------------------------------------------------------------------------------

# Todays Export Folder.
EXPORT_DIRECTORY="/n/export/export_$(date '+%Y%m%d')/"

# S3 File List Temp File
TMPFILE="/tmp/s3"

# --------------------------------------------------------------------------------------------------------

# S3 Executable
S3CMD="/usr/bin/s3cmd"

if ! [ -x ${S3CMD} ]; then
    f_unk "Executable ${S3CMD} is not accessible"
fi

# --------------------------------------------------------------------------------------------------------

# Get list of images in s3.
${S3CMD} ls s3://projectn/ --recursive > ${TMPFILE}

# --------------------------------------------------------------------------------------------------------

# Cross check exports with amazon.

for S3_PUBLIC_URL in `find ${EXPORT_DIRECTORY} -name '*.xml' | xargs grep '.jpg' | sed 's/CDATA\[/~/g' | sed 's/]]/~/g' | cut -d'~' -f2`
do
    S3_PRIVATE_URL=$( echo ${S3_PUBLIC_URL} | sed 's/http/s3/g' | sed 's/.s3.amazonaws.com//g' )

    if [ $( grep -m 1 ${S3_PRIVATE_URL} ${TMPFILE} | wc -l | bc ) -eq 0 ]; then
        echo ${S3_PUBLIC_URL}
    fi
done

rm ${TMPFILE}
