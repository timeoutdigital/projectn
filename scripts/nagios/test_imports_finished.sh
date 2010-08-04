#!/bin/bash

# --------------------------------------------------------------------------------------------------------

# Check projectn import logs.
# Author: Peter Johnson - peterjohnson@timeout.com -- 04-Aug-10

# --------------------------------------------------------------------------------------------------------

# exit status will be:
# 0 if the ftp transactions work correctly
# 1 n/a
# 2 if the ftp transactions do not work correctly
# 3 if the number of arguments is incorrect or the executable cannot be found

# --------------------------------------------------------------------------------------------------------

# Folder which contains logs files.
LOG_DIRECTORY="/n/log"

# Todays date.
TODAYS_DATE=$(/bin/date +%Y-%m-%d)

# --------------------------------------------------------------------------------------------------------

function f_ok {
    echo ${1}
    exit 0
}

function f_war {
    echo ${1}
    exit 1
}

function f_cri {
    echo ${1}
    exit 2
}

function f_unk {
    echo ${1}
    exit 3
}

# --------------------------------------------------------------------------------------------------------

# Check imports all finished.

FAILURES="";
FAILED_CITIES="";

for LOG_FILE in $LOG_DIRECTORY/import/*
do
    # Skip Certain Files, Feel Free to Add More.
    if      [ `echo "${LOG_FILE}" | grep "disabled" | wc -l` -gt 0 ];       then continue
    elif    [ "${LOG_FILE}" == "${LOG_DIRECTORY}/import/common.log" ];      then continue
    elif    [ "${LOG_FILE}" == "${LOG_DIRECTORY}/import/data-entry.log" ];  then continue

    else
        # Count How Many Times Todays Date Appears on the Same Line as 'end import'
        if [ `grep $TODAYS_DATE ${LOG_FILE} | grep "end import" | wc -l` -lt 1 ]; then

           # Found at Least One Failure
           FAILURES="true";

           # Get City Name from File Name
           LOG_CITY=`basename ${LOG_FILE} | sed 's/.log//g'`;

           # Add Failed City to List
           FAILED_CITIES="${FAILED_CITIES} ${LOG_CITY}"

        fi
    fi
done

if [ "${FAILURES}" == "true" ]; then
    f_cri "FAILED IMPORT:${FAILED_CITIES}"
fi

# --------------------------------------------------------------------------------------------------------

f_ok "OK: FTP fully checked, all tests successful"