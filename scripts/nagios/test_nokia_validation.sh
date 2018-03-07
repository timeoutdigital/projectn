#!/bin/bash

# --------------------------------------------------------------------------------------------------------

# Check exports pass Nokia validation.
# Author: Peter Johnson - peterjohnson@timeout.com -- 10-Sep-10

# --------------------------------------------------------------------------------------------------------

# exit status will be:
# 0 if the ftp transactions work correctly
# 1 n/a
# 2 if the ftp transactions do not work correctly
# 3 if the number of arguments is incorrect or the executable cannot be found

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

TODAYS_DATE=$(/bin/date +%Y%m%d)

IFS=$"\n"

# Nokia Validation Digest File Contents.
NOKIA_ERRORS=$( cat /n/export/validation_${TODAYS_DATE}/digest.txt )

IFS=$""

# Output
f_war "Nokia Validation Report\n\n${NOKIA_ERRORS}"

f_ok "OK: All exports passed Nokia validation."

# --------------------------------------------------------------------------------------------------------