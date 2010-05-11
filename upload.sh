#!/bin/bash

cd /var/vhosts/projectn/httpdocs

cp export/fakes/kl_event.xml export/export_$(date +"%Y%m%d")/event/kuala_lumpur.xml
cp export/fakes/kl_poi.xml export/export_$(date +"%Y%m%d")/poi/kuala_lumpur.xml
cp export/fakes/kl_movie.xml export/export_$(date +"%Y%m%d")/movie/kuala_lumpur.xml

cp export/fakes/syd_event.xml export/export_$(date +"%Y%m%d")/event/sydney.xml
cp export/fakes/syd_poi.xml export/export_$(date +"%Y%m%d")/poi/sydney.xml
cp export/fakes/syd_movie.xml export/export_$(date +"%Y%m%d")/movie/sydney.xml

sed -i "s/2010-05-07T22:07:11/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/event/kuala_lumpur.xml
sed -i "s/2010-05-07T22:03:13/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/poi/kuala_lumpur.xml
sed -i "s/2010-05-07T22:00:34/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/movie/kuala_lumpur.xml

sed -i "s/2010-05-07T21:59:14/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/event/sydney.xml
sed -i "s/2010-05-07T21:42:03/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/poi/sydney.xml
sed -i "s/2010-05-07T22:00:34/$(date +'%Y-%m-%dT%T')/g" export/export_$(date +"%Y%m%d")/movie/sydney.xml

echo "Zipping Movies"

cd export/export_$(date +"%Y%m%d")/movie/
zip movie.zip ./*
md5sum movie.zip > movie.zip.md5

echo "Zipping Pois"
cd ../poi/
zip poi.zip ./*
md5sum poi.zip > poi.zip.md5

echo "Zipping Events"
cd ../event/
zip event.zip ./*
md5sum event.zip > event.zip.md5

cd ../../../


echo "Uploading lock"
lftp -c 'open -e "put export/export_'$(date +"%Y%m%d")'/upload.lock" -u timeout,ot1M0T#8 pictis.msudev.noklab.net '

echo " ------------------------------------------------ "

echo -e "Removing remote movies\n"
lftp -c 'open -e "rm movie/movie.zip" -u timeout,t1M0T#8 pictis.msudev.noklab.net '
lftp -c 'open -e "rm movie/movie.zip.md5" -u timeout,t1M0T#8 pictis.msudev.noklab.net '

echo -e "Removing remote pois\n"
lftp -c 'open -e "rm poi/poi.zip" -u timeout,t1M0T#8 pictis.msudev.noklab.net '
lftp -c 'open -e "rm poi/poi.zip.md5" -u timeout,t1M0T#8 pictis.msudev.noklab.net '


echo -e "Removing remote events\n"
lftp -c 'open -e "rm event/event.zip" -u timeout,t1M0T#8 pictis.msudev.noklab.net '
lftp -c 'open -e "rm event/event.zip.md5" -u timeout,t1M0T#8 pictis.msudev.noklab.net '

echo -e " ------------------------------------------------ \n"

echo -e "Syncing files\n"
lftp -c 'open -e "mirror -R -x xml export/export_'$(date +"%Y%m%d")' ." -u timeout,t1M0T#8 pictis.msudev.noklab.net '

echo -e "Removing lock file\n"
lftp -c 'open -e "rm upload.lock" -u timeout,t1M0T#8 pictis.msudev.noklab.net '

echo "Job Done!!"
