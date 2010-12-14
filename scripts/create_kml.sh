#!/bin/bash

# --------------------------------------------------------------------------------------------------------

# Create KML files from export files for todays date.
# Author: Peter Johnson - peterjohnson@timeout.com -- 13-Dec-10

# --------------------------------------------------------------------------------------------------------

# Todays Export Folder.
EXPORT_DIRECTORY="/n/export/export_$(date '+%Y%m%d')/poi/"

# --------------------------------------------------------------------------------------------------------

# XSLT Executable
XSLT="/usr/bin/4xslt"

if ! [ -x ${XSLT} ]; then
    f_unk "Executable ${XSLT} is not accessible"
fi

# --------------------------------------------------------------------------------------------------------

for EXPORT_POI_FILE in `ls -1 ${EXPORT_DIRECTORY}`
do
    XSL_FILE=`echo ${EXPORT_POI_FILE} | sed 's/.xml/.kml/g'`
    ${XSLT} ${EXPORT_DIRECTORY}${EXPORT_POI_FILE} /n/data/xslt/poiExport2Kml.xsl > /tmp/kml/${XSL_FILE}
done