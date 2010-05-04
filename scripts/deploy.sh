#!/bin/bash

set -e
GIT_USER=git
GIT_ROOT=timeout.unfuddle.com:timeout
APP_REPO=$GIT_ROOT/projectn.git
CURRENT_DIR=`pwd`

if (($# == 1)); then

 # Include .sh from the deploy folder
 DEPLOY_ENV=$1
 DEPLOY_FILE=$DEPLOY_ENV.sh


 if [ -f $CURRENT_DIR/scripts/$DEPLOY_FILE ]; then
   source $CURRENT_DIR/scripts/$DEPLOY_FILE
 else
   echo "Could not find deploy file for $DEPLOY_ENV environment, it should be located in $DEPLOY_FILE"
   exit 1
 fi

 echo "Deploying $APP_NAME to $DEPLOY_ENV environment."

else
 echo "Usage: deploy.sh <environment-name>"
 exit 1
fi

CURRENT_DIR=$DEPLOY_PATH/$APP_NAME/httpdocs
RELEASE_NAME=`date +"%Y-%m-%d-%H%M%S"`
CURRENT_RELEASE=$DEPLOY_PATH/$APP_NAME/releases/$RELEASE_NAME

# From local machine, get hash of the head of the desired branch
# Required to checkout the branch - is there a better way to do this?
APP_HASH=`git ls-remote $APP_REPO $BRANCH | awk -F "\t" '{print $1}'`


for SERVER in ${DEPLOY_SERVER[@]}
do
 echo "Deploying on $SERVER"

            ssh -t $DEPLOY_USER@$SERVER "cd $DEPLOY_PATH/$APP_NAME/releases &&
                              git clone -q $GIT_USER@$APP_REPO $RELEASE_NAME &&
                              cd $RELEASE_NAME &&
                              git checkout -q -b deploy $APP_HASH &&
                              ln -nfs $CURRENT_RELEASE $CURRENT_DIR
                              ./symfony doctrine:build --all --and-load --no-confirmation"




 #declare -a FILEARRAY
 #let count=0

 #for file in `ssh $DEPLOY_USER@$SERVER "ls -t $DEPLOY_PATH/$APP_NAME/releases"`
 #do

 #  FILEARRAY[$count] = $file
 #  (($count++))
 #done

 #echo ${ARRAY[*]}


done


echo "Finished successfully"
