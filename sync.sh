#!/bin/bash




if [ $# != 2 ]
then
 echo "Please enter your username:"
 read username

 echo "is this a dry run:"
 read run

 if [ $run == "yes" ]
 then
   dry=" --dry-run "
 else
   dry=""
 fi
fi

rsync -rlhvzCcp   $dry --delete --progress --force --exclude "sync.sh" --exclude "/test/" --exclude "/web/uploads/" --exclude "/doc/" --exclude "/cache/" --exclude ".*" --exclude "/nbproject/" --exclude "/log/" --exclude "/config/"  . $username@192.9.215.2:/var/www/vhosts/projectn/httpdocs

