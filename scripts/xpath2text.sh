#!/bin/bash

XPATH_BINARY="/usr/bin/4xpath"

if ! [ -x ${XPATH_BINARY} ]; then
    echo "Executable ${XPATH_BINARY} is not accessible"
    exit
fi

if [ -z $1 ]; then
    echo "Xpath not specified" && exit
elif [ -z $2 ]; then
    echo "File name not specified" && exit
elif ! [ -r $2 ]; then
    echo "File not readable ${2}" && exit
fi

TOTALRECORDS=`${XPATH_BINARY} $2 "count($1)" 2> /dev/null | bc | sed 's/.0//g' 2> /dev/null`

echo ${TOTALRECORDS}

for (( x=1; x<=${TOTALRECORDS}; x++ ))
do
    ${XPATH_BINARY} --string $2 "$1[$x]"
done