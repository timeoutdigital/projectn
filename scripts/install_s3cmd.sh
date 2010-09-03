#!/bin/bash

cd /tmp
wget http://ftp.de.debian.org/debian/pool/main/s/s3cmd/s3cmd_0.9.9.91.orig.tar.gz

tar xvfz s3cmd_0.9.9.91.orig.tar.gz
rm s3cmd_0.9.9.91.orig.tar.gz

cd s3cmd-0.9.9.91

if [ -x "/usr/bin/sudo" ]; then
    sudo python setup.py install
else
    python setup.py install
fi

SETTINGS_FILE="/n/scripts/.s3cfg"
if ! [ -f ${SETTINGS_FILE} ]; then
    clear
    echo "Could Not Find s3cmd settings file. Please configure s3cmd manually."
else
    cp ${SETTINGS_FILE} ~/.s3cfg

    clear
    echo "Good to Go :-)"
    s3cmd ls s3://projectn
fi