#!/bin/bash

# --------------------------------------------------------------------------------------------------------

# Check projectn export numbers.
# Author: Peter Johnson - peterjohnson@timeout.com -- 04-Aug-10

# --------------------------------------------------------------------------------------------------------

# exit status will be:
# 0 if the ftp transactions work correctly
# 1 n/a
# 2 if the ftp transactions do not work correctly
# 3 if the number of arguments is incorrect or the executable cannot be found

# --------------------------------------------------------------------------------------------------------

# Folder which contains logs files.
EXPORT_DIRECTORY="/n/export"

# Todays date.
TODAYS_DATE=$(/bin/date +%Y%m%d)
YESTERDAYS_DATE=$(/bin/date +%Y%m%d -d "yesterday")

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

XPATH="/usr/bin/4xpath"

if ! [ -x ${XPATH} ]; then
    f_unk "Executable ${XPATH} is not accessible"
fi

# --------------------------------------------------------------------------------------------------------

# Cycle through exports.

TODAY_DIR=${EXPORT_DIRECTORY}/export_${TODAYS_DATE}
YESTERDAY_DIR=${EXPORT_DIRECTORY}/export_${YESTERDAYS_DATE}

# ------------------------------- POI -------------------------------

XPATH_POI_COUNT="count(/vendor-pois/entry)"
POI_LOSSES="";

for POI_FILE in ${EXPORT_DIRECTORY}/export_${TODAYS_DATE}/poi/*
do
    EXPORT_CITY=`basename ${POI_FILE} | sed 's/.xml//g'`;

    TODAY_XPATH_RESULT=$( ${XPATH} ${POI_FILE} ${XPATH_POI_COUNT} 2> /dev/null )
    YESTERDAY_XPATH_RESULT=$( ${XPATH} ${YESTERDAY_DIR}/poi/${EXPORT_CITY}.xml ${XPATH_POI_COUNT} 2> /dev/null )

    #Round Results
    TODAY_XPATH_RESULT=$( echo "${TODAY_XPATH_RESULT}" | bc | cut -d'.' -f1 )
    YESTERDAY_XPATH_RESULT=$( echo "${YESTERDAY_XPATH_RESULT}" | bc | cut -d'.' -f1 )

    if [ ${YESTERDAY_XPATH_RESULT} -gt ${TODAY_XPATH_RESULT} ]; then
        POI_LOSSES="${POI_LOSSES}\n\t${EXPORT_CITY}:$((${TODAY_XPATH_RESULT}-${YESTERDAY_XPATH_RESULT}))"
    fi
done

# ------------------------------- EVENT -------------------------------

XPATH_EVENT_COUNT="count(/vendor-events/event)"
EVENT_LOSSES="";

for EVENT_FILE in ${EXPORT_DIRECTORY}/export_${TODAYS_DATE}/event/*
do
    EXPORT_CITY=`basename ${EVENT_FILE} | sed 's/.xml//g'`;

    TODAY_XPATH_RESULT=$( ${XPATH} ${EVENT_FILE} ${XPATH_EVENT_COUNT} 2> /dev/null )
    YESTERDAY_XPATH_RESULT=$( ${XPATH} ${YESTERDAY_DIR}/event/${EXPORT_CITY}.xml ${XPATH_EVENT_COUNT} 2> /dev/null )

    #Round Results
    TODAY_XPATH_RESULT=$( echo "${TODAY_XPATH_RESULT}" | bc | cut -d'.' -f1 )
    YESTERDAY_XPATH_RESULT=$( echo "${YESTERDAY_XPATH_RESULT}" | bc | cut -d'.' -f1 )

    if [ ${YESTERDAY_XPATH_RESULT} -gt ${TODAY_XPATH_RESULT} ]; then
        EVENT_LOSSES="${EVENT_LOSSES}\n\t${EXPORT_CITY}:$((${TODAY_XPATH_RESULT}-${YESTERDAY_XPATH_RESULT}))"
    fi
done

# ------------------------------- MOVIE -------------------------------

XPATH_MOVIE_COUNT="count(/vendor-movies/movie)"
MOVIE_LOSSES="";

for MOVIE_FILE in ${EXPORT_DIRECTORY}/export_${TODAYS_DATE}/movie/*
do
    EXPORT_CITY=`basename ${MOVIE_FILE} | sed 's/.xml//g'`;

    TODAY_XPATH_RESULT=$( ${XPATH} ${MOVIE_FILE} ${XPATH_MOVIE_COUNT} 2> /dev/null )
    YESTERDAY_XPATH_RESULT=$( ${XPATH} ${YESTERDAY_DIR}/movie/${EXPORT_CITY}.xml ${XPATH_MOVIE_COUNT} 2> /dev/null )

    #Round Results
    TODAY_XPATH_RESULT=$( echo "${TODAY_XPATH_RESULT}" | bc | cut -d'.' -f1 )
    YESTERDAY_XPATH_RESULT=$( echo "${YESTERDAY_XPATH_RESULT}" | bc | cut -d'.' -f1 )

    if [ ${YESTERDAY_XPATH_RESULT} -gt ${TODAY_XPATH_RESULT} ]; then
        MOVIE_LOSSES="${MOVIE_LOSSES}\n\t${EXPORT_CITY}:$((${TODAY_XPATH_RESULT}-${YESTERDAY_XPATH_RESULT}))"
    fi
done


# ------------------------------- OUTPUT -------------------------------

if [ "${POI_LOSSES}" != "" ] || [ "${EVENT_LOSSES}" != "" ] || [ "${MOVIE_LOSSES}" != "" ]; then
    f_war "EXPORT REPORT:\n\nPOI:${POI_LOSSES}\nEVENT:${EVENT_LOSSES}\nMOVIE:${MOVIE_LOSSES}"
fi

# --------------------------------------------------------------------------------------------------------

f_ok "OK: No exports declined"