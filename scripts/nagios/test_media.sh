#!/bin/bash

# --------------------------------------------------------------------------------------------------------

# Check projectn media files in exports are available on s3 and don't contain HTTP headers.
# Author: Peter Johnson - peterjohnson@timeout.com -- 27-Aug-10

# --------------------------------------------------------------------------------------------------------

# exit status will be:
# 0 if the ftp transactions work correctly
# 1 n/a
# 2 if the ftp transactions do not work correctly
# 3 if the number of arguments is incorrect or the executable cannot be found

# --------------------------------------------------------------------------------------------------------

# S3 Log File Location.
S3_LOG="/n/log/media_missing_from_s3.log"

# Total S3 Errors.
S3_ERRORS=$( cat ${S3_LOG} | wc -l | bc )

# HTTP Header Log File Location.
HTTP_LOG="/n/log/media_with_http_headers.log"

# Total HTTP Errors.
HTTP_ERRORS=$( cat ${HTTP_LOG} | wc -l | bc )

# Images Left On Prod Server (not Sync'd)
TOTAL_IMAGES_LOCAL=$(find /n/import/ -name *.jpg | wc -l | bc)

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

# Output
if [ ${S3_ERRORS} -gt 0 ] || [ ${HTTP_ERRORS} -gt 0 ] || [ ${TOTAL_IMAGES_LOCAL} -gt 0 ]; then
    f_war "Media Report\n\nA total of ${S3_ERRORS} images from last night exports were not found on s3.\nA log has been stored in ${S3_LOG} on projectn.live.\n\nA total of ${HTTP_ERRORS} images from last night exports contained HTTP headers.\nA log has been stored in ${HTTP_LOG} on projectn.live.\n\nA total of ${TOTAL_IMAGES_LOCAL} images are currently being stored locally."
fi

f_ok "OK: All export media fine."

# --------------------------------------------------------------------------------------------------------


