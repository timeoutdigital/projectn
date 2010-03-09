#!/bin/bash




if [ $# != 2 ]
then
 echo "Please enter your username:"
 read username

 echo "Host"
 read host

 echo "is this a dry run:"
 read run

 if [ $run == "yes" ]
 then
   dry=" --dry-run "
 else
   dry=""
 fi
fi

rsync -rlhvzCcp   $dry --delete --progress --force --exclude "sync.sh" --exclude "/export/" --exclude "/import/"  --exclude "/test/" --exclude "/web/uploads/" --exclude "/doc/" --exclude "/cache/" --exclude ".*" --exclude "/nbproject/" --exclude "/log/" --exclude "/config/"  . $username@$host:/var/vhosts/projectn/httpdocs

