#!/bin/bash

cd /var/vhosts/projectn/httpdocs

cp export/fakes/poi/bangalore.xml export/export_$(date +"%Y%m%d")/poi/bangalore.xml
cp export/fakes/poi/dehli.xml export/export_$(date +"%Y%m%d")/poi/dehli.xml
cp export/fakes/poi/mumbai.xml export/export_$(date +"%Y%m%d")/poi/mumbai.xml
cp export/fakes/poi/pune.xml export/export_$(date +"%Y%m%d")/poi/pune.xml
  
cp export/fakes/event/bangalore.xml export/export_$(date +"%Y%m%d")/event/bangalore.xml
cp export/fakes/event/dehli.xml export/export_$(date +"%Y%m%d")/event/dehli.xml
cp export/fakes/event/mumbai.xml export/export_$(date +"%Y%m%d")/event/mumbai.xml
cp export/fakes/event/pune.xml export/export_$(date +"%Y%m%d")/event/pune.xml

cp export/fakes/movie/bangalore.xml export/export_$(date +"%Y%m%d")/movie/bangalore.xml
cp export/fakes/movie/dehli.xml export/export_$(date +"%Y%m%d")/movie/dehli.xml
cp export/fakes/movie/mumbai.xml export/export_$(date +"%Y%m%d")/movie/mumbai.xml
cp export/fakes/movie/pune.xml export/export_$(date +"%Y%m%d")/movie/pune.xml

sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/poi/bangalore.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/poi/dehli.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/poi/mumbai.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/poi/pune.xml

sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/event/bangalore.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/event/dehli.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/event/mumbai.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/event/pune.xml

sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/movie/bangalore.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/movie/dehli.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/movie/mumbai.xml
sed -i "s/====/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/movie/pune.xml
