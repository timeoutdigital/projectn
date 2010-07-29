#!/bin/bash

###############
# Get options #
###############
while getopts ':e:t:b:' flag
do
  case $flag in
    e)   ENV=$OPTARG;;
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


##################
# Clean releases #
##################

COUNTER=0
for f in `ls -t $CONFIG_DEPLOY_PATH/$CONFIG_APP_NAME/releases/`
do
    echo $COUNTER
    echo "Delete $f"
    if [[ -d $f ]]; then

        echo "x"
        if [[ "$COUNTER" -gt "$CONFIG_KEEP_OLD_RELEASES" ]]; then

        echo "Delete $f"

        fi
    fi
    let "COUNTER = $COUNTER + 1"
done

