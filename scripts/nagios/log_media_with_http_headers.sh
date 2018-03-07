#!/bin/bash

# --------------------------------------------------------------------------------------------------------

# Check projectn media files in exports have 'HTTP/1.1' in the header.
# Author: Peter Johnson - peterjohnson@timeout.com -- 31-Aug-10

# --------------------------------------------------------------------------------------------------------

# Todays Export Folder.
EXPORT_DIRECTORY="/n/export/export_$(date '+%Y%m%d')/"

# --------------------------------------------------------------------------------------------------------

for S3_PUBLIC_URL in `find ${EXPORT_DIRECTORY} -name '*.xml' | xargs grep '.jpg' | sed 's/CDATA\[/~/g' | sed 's/]]/~/g' | cut -d'~' -f2`
do
	FILE_LOCATION=$( echo ${S3_PUBLIC_URL} | sed 's/http\:\/\/projectn.s3.amazonaws.com\///g' )
	FILE_LOCATION="/var/vhosts/projectn/import/${FILE_LOCATION}"

	if [ -r ${FILE_LOCATION} ]; then
		if [ $( grep -m 1 "HTTP\/1.1" ${FILE_LOCATION} | wc -l | bc ) -gt 0 ]; then
			echo ${FILE_LOCATION}
		fi
	fi
done
