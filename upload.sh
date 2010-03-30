#!/bin/bash

echo "Zipping Movies"
zip export/export_$(date +"%Y%m%d")/movie/movie.zip export/export_$(date +"%Y%m%d")/movie/*
md5sum /export/export_$(date +"%Y%m%d")/movie/movie.zip > export/export_$(date +"%Y%m%d")/movie/movie.zip.md5

echo "Zipping Pois"
zip export/export_$(date +"%Y%m%d")/poi/poi.zip export/export_$(date +"%Y%m%d")/poi/*
md5sum export/export_$(date +"%Y%m%d")/poi/poi.zip > export/export_$(date +"%Y%m%d")/poi/poi.zip.md5

echo "Zipping Events"
zip export/export_$(date +"%Y%m%d")/event/event.zip export/export_$(date +"%Y%m%d")/event/*
md5sum export/export_$(date +"%Y%m%d")/event/event.zip > export/export_$(date +"%Y%m%d")/event/event.zip.md5


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
