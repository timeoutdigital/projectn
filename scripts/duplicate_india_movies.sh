#!/bin/bash

cd /var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/

sed 's/\(<movie id="\)BOM\([0-9]\+" .*>\)/\1DEL\2/g' mumbai.xml > delhi.xml
sed 's/\(<movie id="\)BOM\([0-9]\+" .*>\)/\1BLR\2/g' mumbai.xml > bangalore.xml
sed 's/\(<movie id="\)BOM\([0-9]\+" .*>\)/\1PNQ\2/g' mumbai.xml > pune.xml
