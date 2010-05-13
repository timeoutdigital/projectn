#!/bin/bash

set -e
GIT_USER=git
GIT_ROOT=timeout.unfuddle.com:timeout
APP_REPO=$GIT_ROOT/projectn.git
CURRENT_DIR=`pwd`

if (($# < 3)); then         #check if less than 3 parameters

 # Include .sh from the deploy folder
 DEPLOY_ENV=$1
 DEPLOY_FILE=$DEPLOY_ENV.config

 if [ -f $CURRENT_DIR/scripts/$DEPLOY_FILE ]; then
   source $CURRENT_DIR/scripts/$DEPLOY_FILE
 else
   echo "Could not find deploy file for $DEPLOY_ENV environment, it should be located in $DEPLOY_FILE"
   exit 1
 fi

fi


if (($# == 1)); then        #check if 1 param (env)
 
 echo "Deploying $APP_NAME (tag: $REPO_TAG ) to $DEPLOY_ENV environment."

elif (($# == 2)); then      #check if 2 param (env, tag)
 
 REPO_TAG=$2

 if [ -z `git tag -l "$REPO_TAG"` ]; then
   echo "Error: The specified tag: '$REPO_TAG' is not valid"
   exit 1
 fi
 
 echo "Deploying $APP_NAME (tag: $REPO_TAG ) to $DEPLOY_ENV environment."

else                        #check throw error if not 1 or 2 params
 
 echo "Usage: deploy.config <environment-name> [<tag>]"
 exit 1

fi

CURRENT_DIR=$DEPLOY_PATH/$APP_NAME/httpdocs
RELEASE_NAME=`date +"%Y-%m-%d-%H%M%S"`
CURRENT_RELEASE=$DEPLOY_PATH/$APP_NAME/releases/$RELEASE_NAME

if [ $DEPLOY_ENV == "prod" ]; then

 echo -n "You are about to deploy $APP_NAME (tag: $REPO_TAG ) to $DEPLOY_ENV environment do you really want to do this? (yes/no)"
 read -e CONFIRMATION

 if [ $CONFIRMATION != "yes" ]; then
  echo "deployment aborted"
  exit 1
 fi

fi

DEPLOY_COMMAND="cd $DEPLOY_PATH/$APP_NAME/releases &&
                              git clone $GIT_USER@$APP_REPO $RELEASE_NAME &&
                              cd $RELEASE_NAME &&
                              git checkout -b $REPO_TAG &&
                              rm -rf export/ import/ log/ config/databases.yml &&
                              ln -ns $DEPLOY_PATH/$APP_NAME/export export &&
                              ln -ns $DEPLOY_PATH/$APP_NAME/import import &&
                              ln -ns $DEPLOY_PATH/$APP_NAME/log log &&
                              ln -ns $DEPLOY_PATH/$APP_NAME/config/databases.yml config/databases.yml &&
                              rm $CURRENT_DIR &&
                              ln -ns $CURRENT_RELEASE $CURRENT_DIR &&
                              ./symfony project:permissions &&
                              ./symfony doctrine:build-model &&
                              ./symfony doctrine:build-filters &&
                              ./symfony doctrine:build-forms &&
                              ./symfony cc"



for SERVER in ${DEPLOY_SERVER[@]}
do
 echo "Deploying on $SERVER"
 ssh -t $DEPLOY_USER@$SERVER "$DEPLOY_COMMAND"

done       


echo "Finished successfully"
