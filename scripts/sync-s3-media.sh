#!/bin/bash

cd /n/import/

for VENDOR_DIR in `ls -l | awk '/^d/ { print $NF }'`
do
    cd $VENDOR_DIR

    for MODEL_DIR in `ls -l | awk '/^d/ { print $NF }'`
    do
        cd $MODEL_DIR
        CURRENT_DIR="`pwd`/"

        # Sync Media Files.
        # s3cmd sync --acl-public --guess-mime-type $CURRENT_DIR s3://projectn/$VENDOR_DIR/$MODEL_DIR/

        # Pull a List of Media on s3 and Delete Matching Images on the Hard Drive
        s3cmd ls s3://projectn/$VENDOR_DIR/$MODEL_DIR/media/ | cut -d'/' -f7 | xargs -I {} find /n/import/ -name {} | xargs -I {} rm {} 2> /dev/null

        cd ..
    done

    cd ..
done