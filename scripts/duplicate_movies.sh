#!/bin/bash

cd /var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/

# Indian Cities
sed 's/\(<movie id="\)BOM\([0-9]\+" .*>\)/\1DEL\2/g' mumbai.xml > delhi.xml
sed 's/\(<movie id="\)BOM\([0-9]\+" .*>\)/\1BLR\2/g' mumbai.xml > bangalore.xml
sed 's/\(<movie id="\)BOM\([0-9]\+" .*>\)/\1PNQ\2/g' mumbai.xml > pune.xml

# Russian Cities
sed 's/\(<movie id="\)MOW\([0-9]\+" .*>\)/\1LED\2/g' moscow.xml > saint_petersburg.xml
sed 's/\(<movie id="\)MOW\([0-9]\+" .*>\)/\1KJA\2/g' moscow.xml > krasnoyarsk.xml
sed 's/\(<movie id="\)MOW\([0-9]\+" .*>\)/\1OMS\2/g' moscow.xml > omsk.xml
sed 's/\(<movie id="\)MOW\([0-9]\+" .*>\)/\1ALA\2/g' moscow.xml > almaty.xml

# Chinese Cities (chinese language)
sed 's/\(<movie id="\)TSN\([0-9]\+" .*>\)/\1PVG\2/g' beijing_zh.xml > shanghai_zh.xml

cd /var/vhosts/projectn/httpdocs/export/
tar zcf exports_$(date +"%Y%m%d").tgz export_$(date +"%Y%m%d")/*

