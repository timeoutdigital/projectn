#!/bin/bash

cd /tmp
crontab -l | grep "s3cmd sync" | cut -d'*' -f4 | sed 's/       //g' > s3commands.sh
chmod +x s3commands.sh
./s3commands.sh
rm s3commands.sh