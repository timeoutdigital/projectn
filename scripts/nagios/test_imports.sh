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
    echo -e ${1}
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

WARNING="";
WARNING_CITIES="";
NOTICE="";
NOTICE_CITIES="";
ERROR="";
ERROR_CITIES="";

for LOG_FILE in $LOG_DIRECTORY/import/*
do
    # Skip Certain Files, Feel Free to Add More.
    if      [ `echo "${LOG_FILE}" | grep "disabled" | wc -l` -gt 0 ];       then continue
    elif    [ "${LOG_FILE}" == "${LOG_DIRECTORY}/import/common.log" ];      then continue
    elif    [ "${LOG_FILE}" == "${LOG_DIRECTORY}/import/data-entry.log" ];  then continue

    else

        # Get City Name from File Name
        LOG_CITY=`basename ${LOG_FILE} | sed 's/.log//g'`;

        # Check if Import Not Started
        if [ `grep $TODAYS_DATE ${LOG_FILE} | grep "start import" | wc -l` -lt 1 ]; then

           # Found at Least One Failure
           FAILURES="true";

           # Add Failed City to List
           FAILED_CITIES="${FAILED_CITIES} ${LOG_CITY}"

        else

            IFS=$'\n'
            for IMPORT_START_LINE in $( grep $TODAYS_DATE ${LOG_FILE} | grep "start import" )
            do

               IMPORT_TYPE=`echo "${IMPORT_START_LINE}" | cut -d"(" -f2 | cut -d"," -f1 | cut -d" " -f2`

               # Check if Import Type Didn't Finish
               if [ `grep $TODAYS_DATE ${LOG_FILE} | grep "end import" | grep ${IMPORT_TYPE} | wc -l` -lt 1 ]; then

                    # Found at Least One Failure
                    FAILURES="true";

                    # Add Failed City to List
                    FAILED_CITIES="${FAILED_CITIES}\n\t${LOG_CITY}:${IMPORT_TYPE}"

               else

                   START_LINE_NUMBER=`grep -n ${IMPORT_START_LINE} ${LOG_FILE} | grep ${IMPORT_TYPE} | cut -d":" -f1`
                   END_LINE_NUMBER=`grep -n $TODAYS_DATE ${LOG_FILE} | grep "end import" | grep ${IMPORT_TYPE} | cut -d":" -f1`

                   TOTAL_LINES=$(($END_LINE_NUMBER-$START_LINE_NUMBER))

                   OUTPUT_SECTION=`cat ${LOG_FILE} | head -n ${END_LINE_NUMBER} | tail -n ${TOTAL_LINES}`

                   # Check if Import Type Had Errors
                   if [ `echo ${OUTPUT_SECTION} | grep "Error" | wc -l` -gt 0 ]; then

                        # Found at Least One Failure
                        ERROR="true";

                        # Add Failed City to List
                        ERROR_CITIES="${ERROR_CITIES}\n\t${LOG_CITY}:${IMPORT_TYPE}"

                   fi

                   # Check if Import Type Had Warnings
                   if [ `echo ${OUTPUT_SECTION} | grep "Warning" | wc -l` -gt 0 ]; then

                        # Found at Least One Failure
                        WARNING="true";

                        # Add Failed City to List
                        WARNING_CITIES="${WARNING_CITIES}\n\t${LOG_CITY}:${IMPORT_TYPE}"

                   fi

                   # Check if Import Type Had Notices
                   if [ `echo ${OUTPUT_SECTION} | grep "Notice" | wc -l` -gt 0 ]; then

                        # Found at Least One Failure
                        NOTICE="true";

                        # Add Failed City to List
                        NOTICE_CITIES="${NOTICE_CITIES}\n\t${LOG_CITY}:${IMPORT_TYPE}"

                   fi

               fi

            done
        fi
    fi
done

if [ "${FAILURES}" == "true" ] || [ "${ERROR}" == "true" ] || [ "${WARNING}" == "true" ] || [ "${NOTICE}" == "true" ]; then
    f_cri "FAILED:${FAILED_CITIES}\nERROR:${ERROR_CITIES}\nWARNING:${WARNING_CITIES}\nNOTICE:${NOTICE_CITIES}"
fi

# --------------------------------------------------------------------------------------------------------

f_ok "OK: All imports finished"