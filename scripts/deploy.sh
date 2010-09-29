#!/bin/bash

###############
# Get options #
###############
while getopts ':e:t:b:' flag
do
  case $flag in
    e)   ENV=$OPTARG;;
    b)   BRANCH=$OPTARG ;;
    t)   TAG=$OPTARG ;;
    [?]) echo "$OPTARG is not a recognised option."; exit 1 ;;
    [:]) if [[ $OPTARG == 'b' ]]; then
           echo 'A branch name is required with the -b option.'
           exit
         elif [[ $OPTARG == 't' ]]; then
           TAG=`git tag | sort -n | tail -1`
         fi;
  esac
done

#####################################
# Make sure enviroment is specified #
#####################################
if [[ -z $ENV ]]; then
  echo 'No enviroment defined. This should be specified after the -e flag. Example:'
  echo 'deploy -e dev'
  exit 1
fi

###############
# Load config #
###############

CURRENT_DIR=`pwd`
CONFIG=$CURRENT_DIR/scripts/$ENV.config
if [[ -f $CONFIG ]]; then
  source $CONFIG
else
  echo "Could not find deploy file for '$ENV' environment, it should be located in $CONFIG"
  exit 1
fi

############################
# Set defaults from config #
############################

#we use defaults only if not tag or branch is specified in the command call
if [[ -z $TAG && -z $BRANCH ]]; then
  TAG=$CONFIG_DEFAULT_TAG
  BRANCH=$CONFIG_DEFAULT_BRANCH
fi

##################################
# Tag, branch or config default? #
##################################

#tag
if [[ -n $TAG && -z $BRANCH ]]; then
  #make sure it exists
  HAS_TAG=0
  if [[ -z `git tag -l "$TAG"` ]]; then
    echo "Error: The specified tag: '$TAG' does not exist."
    for available_tag in `git tag -l`
      do
        echo $available_tag
    done
    exit 1
  fi
  GIT_CHECKOUT="git checkout $TAG &&"
  REF_TYPE='tag'
  REF=$TAG

#branch
elif [[ -z $TAG && -n $BRANCH ]]; then

  #check branch exists in origin (unfuddle)
  HAS_BRANCH=0
  for available_branch in `git branch -r`
    do
    if [[ $available_branch == "origin/$BRANCH"  ]]; then
      HAS_BRANCH=1
    fi
  done

  #if no branch exists in origin, list what branches we do have
  if [[ $HAS_BRANCH == 0 ]]; then
    echo ''
    echo "Branch '$BRANCH' not found. Available branches are:"
    for remote_branch_ref in `git branch -r`
      do
        #refs come back as 'origin/branch_name'. Don't need to show 'origin/'
        remote_branch=${remote_branch_ref//origin\//}

        #don't care for 'HEAD', 'master' or '->'
        if [[ $remote_branch == 'HEAD' || $remote_branch == '->' || $remote_branch == 'master' ]]; then
          continue
        fi

        echo $remote_branch
    done
    exit 1
  fi

  GIT_CHECKOUT="git checkout origin/$BRANCH &&"
  REF_TYPE='branch'
  REF=$BRANCH

#both (causes error)
elif [[ -n $TAG && -n $BRANCH ]]; then
  echo 'Cannot deploy a tag and a branch at the same time.'
  exit 1

#or nothing (will default to master)
else
  GIT_CHECKOUT=""
  REF_TYPE='HEAD'
  REF='master'
fi


###############
# Set details #
###############
GIT_USER=git
GIT_ROOT=timeout.unfuddle.com:timeout
APP_REPO=$GIT_ROOT/projectn.git

RELEASE_NAME=`date +"%Y-%m-%d-%H%M%S"`


#####################
# Confirm with user #
#####################

echo -n "You are about to deploy ${CONFIG_APP_NAME[*]} ($REF_TYPE:$REF) to $ENV environment do you really want to do this? (yes/no)"
read -e CONFIRMATION

if [ $CONFIRMATION != "yes" ]; then
 echo "deployment aborted"
 exit 1
fi


##########
# Deploy #
##########

for APP_NAME in ${CONFIG_APP_NAME[*]}
do

    ############################
    # Set APP specific details #
    ############################

    DEPLOY_DIR=$CONFIG_DEPLOY_PATH/$APP_NAME/httpdocs
    CURRENT_RELEASE=$CONFIG_DEPLOY_PATH/$APP_NAME/releases/$RELEASE_NAME

    #########################
    # Create deploy command #
    #########################

    DEPLOY_COMMAND="cd $CONFIG_DEPLOY_PATH/$APP_NAME/releases &&
                                  git clone $GIT_USER@$APP_REPO $RELEASE_NAME &&
                                  cd $RELEASE_NAME &&
                                  $GIT_CHECKOUT
                                  rm -rf $CONFIG_DEPLOY_PATH/$APP_NAME/vendor && mv lib/vendor/ $CONFIG_DEPLOY_PATH/$APP_NAME/vendor/ &&
                                  rm -rf export/ import/ log/ web/uploads/ config/databases.yml &&
                                  ln -ns $CONFIG_DEPLOY_PATH/$APP_NAME/export export &&
                                  ln -ns $CONFIG_DEPLOY_PATH/$APP_NAME/import import &&
                                  ln -ns $CONFIG_DEPLOY_PATH/$APP_NAME/log log &&
                                  ln -ns $CONFIG_DEPLOY_PATH/$APP_NAME/vendor lib/vendor &&
                                  ln -ns $CONFIG_DEPLOY_PATH/$APP_NAME/uploads web/uploads &&
                                  ln -ns $CONFIG_DEPLOY_PATH/$APP_NAME/config/databases.yml config/databases.yml &&
                                  ln -ns $CONFIG_DEPLOY_PATH/$APP_NAME/data/geocache.sqlite data/geocache.sqlite &&
                                  rm $DEPLOY_DIR &&
                                  ln -ns $CURRENT_RELEASE $DEPLOY_DIR &&
                                  ./symfony project:permissions &&
                                  ./symfony doctrine:build-model &&
                                  ./symfony doctrine:build-filters &&
                                  ./symfony doctrine:build-forms &&
                                  ./symfony cc &&
                                  ./scripts/clean_releases.sh -e $ENV"
    ###########
    # Execute #
    ###########

    echo "Deploying on $CONFIG_DEPLOY_SERVER, app: $APP_NAME"
    ssh -t $CONFIG_DEPLOY_USER@$CONFIG_DEPLOY_SERVER "$DEPLOY_COMMAND"

done


#######
# fin #
#######
echo "Finished successfully"

exit
