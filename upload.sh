#!/bin/bash

cd /var/vhosts/projectn/httpdocs

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

N_SERVER_USER = "timeout"
N_SERVER_PASS = "t1M0T#8"
N_SERVER_HOST = "pictis.msudev.noklab.net"

echo "Uploading lock"
lftp -c 'open -e "put export/export_'$(date +"%Y%m%d")'/upload.lock" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'

echo " ------------------------------------------------ "

echo -e "Removing remote movies\n"
lftp -c 'open -e "rm movie/movie.zip" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'
lftp -c 'open -e "rm movie/movie.zip.md5" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'

echo -e "Removing remote pois\n"
lftp -c 'open -e "rm poi/poi.zip" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'
lftp -c 'open -e "rm poi/poi.zip.md5" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'


echo -e "Removing remote events\n"
lftp -c 'open -e "rm event/event.zip" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'
lftp -c 'open -e "rm event/event.zip.md5" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'

echo -e " ------------------------------------------------ \n"

echo -e "Syncing files\n"
lftp -c 'open -e "mirror -R -x xml export/export_'$(date +"%Y%m%d")' ." -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'

echo -e "Removing lock file\n"
lftp -c 'open -e "rm upload.lock" -u $N_SERVER_USER,$N_SERVER_PASS $N_SERVER_HOST'

echo "Job Done!!"