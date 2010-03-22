#!/bin/bash

mkdir export/export_$(date +"%Y%m%d")
mkdir export/export_$(date +"%Y%m%d")/movies
mkdir export/export_$(date +"%Y%m%d")/pois
mkdir export/export_$(date +"%Y%m%d")/events
touch export/export_$(date +"%Y%m%d")/upload.lock

echo "Exporting Movies"
echo "Exporting Movies for abu dhabi"
./symfony projectn:export --type=movie --city="abu dhabi" --language=en-US --destination=export/export_20100322/movies/abudhabi.xml

echo "Exporting Movies for Dubai"
./symfony projectn:export --type=movie --city="dubai" --language=en-US --destination=export/export_20100322/movies/dubai.xml

echo "Exporting Movies for Lisbon"
./symfony projectn:export --type=movie --city="lisbon" --language=pt --destination=export/export_20100322/movies/lisbon.xml

echo "Exporting Movies for London"
./symfony projectn:export --type=movie --city="london" --language=en-GB --destination=export/export_20100322/movies/london.xml

echo "Exporting Movies for Singapore"
./symfony projectn:export --type=movie --city="singapore" --language=en-US --destination=export/export_20100322/movies/singapore.xml

echo "Exporting Movies for NY"
./symfony projectn:export --type=movie --city="ny" --language=en-US --destination=export/export_20100322/movies/ny.xml

echo "Exporting Movies for Chicago"
./symfony projectn:export --type=movie --city="chicago" --language=en-US --destination=export/export_20100322/movies/chicago.xml


echo "========================================================================="

echo "Exporting Pois"
echo "Exporting Pois for abu dhabi"
./symfony projectn:export --type=poi --city="abu dhabi" --language=en-US --destination=export/export_20100322/pois/abudhabi.xml

echo "Exporting Pois for Dubai"
./symfony projectn:export --type=poi --city="dubai" --language=en-US --destination=export/export_20100322/pois/dubai.xml

echo "Exporting Pois for Lisbon"
./symfony projectn:export --type=poi --city="lisbon" --language=pt --destination=export/export_20100322/pois/lisbon.xml

echo "Exporting Pois for London"
./symfony projectn:export --type=poi --city="london" --language=en-GB --destination=export/export_20100322/pois/london.xml

echo "Exporting Movies for Singapore"
./symfony projectn:export --type=poi --city="singapore" --language=en-US --destination=export/export_20100322/pois/singapore.xml

echo "Exporting Pois for NY"
./symfony projectn:export --type=poi --city="ny" --language=en-US --destination=export/export_20100322/pois/ny.xml

echo "Exporting Pois for Chicago"
./symfony projectn:export --type=poi --city="chicago" --language=en-US --destination=export/export_20100322/pois/chicago.xml


echo "========================================================================="

echo "Exporting Events"
echo "Exporting Events for abu dhabi"
./symfony projectn:export --type=event --city="abu dhabi" --language=en-US --destination=export/export_20100322/events/abudhabi.xml

echo "Exporting Events for Dubai"
./symfony projectn:export --type=event --city="dubai" --language=en-US --destination=export/export_20100322/events/dubai.xml

echo "Exporting Events for Lisbon"
./symfony projectn:export --type=event --city="lisbon" --language=pt --destination=export/export_20100322/events/lisbon.xml

echo "Exporting Events for London"
./symfony projectn:export --type=event --city="london" --language=en-GB --destination=export/export_20100322/events/london.xml

echo "Exporting Events for Singapore"
./symfony projectn:export --type=event --city="singapore" --language=en-US --destination=export/export_20100322/events/singapore.xml

echo "Exporting Events for NY"
./symfony projectn:export --type=event --city="ny" --language=en-US --destination=export/export_20100322/events/ny.xml

echo "Exporting Events for Chicago"
./symfony projectn:export --type=event --city="chicago" --language=en-US --destination=export/export_20100322/events/chicago.xml