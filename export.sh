#!/bin/bash

mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")
mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie
mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi
mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event
touch /var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/upload.lock

echo "Exporting Movies"
#echo "Exporting Movies for abu dhabi"
#/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=movie --city="abu dhabi" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/abudhabi.xml

#echo "Exporting Movies for Dubai"
#/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=movie --city="dubai" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/dubai.xml

echo "Exporting Movies for Lisbon"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=movie --city="lisbon" --language=pt --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/lisbon.xml

echo "Exporting Movies for London"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=movie --city="london" --language=en-GB --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/london.xml

echo "Exporting Movies for Singapore"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=movie --city="singapore" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/singapore.xml

echo "Exporting Movies for NY"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=movie --city="ny" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/ny.xml

echo "Exporting Movies for Chicago"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=movie --city="chicago" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/movie/chicago.xml


echo "========================================================================="

echo "Exporting Pois"
#echo "Exporting Pois for abu dhabi"
#/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=poi --city="abu dhabi" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi/abudhabi.xml

#echo "Exporting Pois for Dubai"
#/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=poi --city="dubai" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi/dubai.xml

echo "Exporting Pois for Lisbon"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=poi --city="lisbon" --language=pt --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi/lisbon.xml

echo "Exporting Pois for London"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=poi --city="london" --language=en-GB --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi/london.xml

echo "Exporting Movies for Singapore"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=poi --city="singapore" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi/singapore.xml

echo "Exporting Pois for NY"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=poi --city="ny" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi/ny.xml

echo "Exporting Pois for Chicago"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=poi --city="chicago" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/poi/chicago.xml


echo "========================================================================="

echo "Exporting Events"
#echo "Exporting Events for abu dhabi"
#/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=event --city="abu dhabi" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event/abudhabi.xml

#echo "Exporting Events for Dubai"
#/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=event --city="dubai" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event/dubai.xml

echo "Exporting Events for Lisbon"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=event --city="lisbon" --language=pt --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event/lisbon.xml

echo "Exporting Events for London"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=event --city="london" --language=en-GB --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event/london.xml

echo "Exporting Events for Singapore"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=event --city="singapore" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event/singapore.xml

echo "Exporting Events for NY"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=event --city="ny" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event/ny.xml

echo "Exporting Events for Chicago"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --type=event --city="chicago" --language=en-US --destination=/var/vhosts/projectn/httpdocs/export/export_$(date +"%Y%m%d")/event/chicago.xml
