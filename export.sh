mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')
mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois
mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies
mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events
touch mkdir /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/upload.lock


#Export UAE
echo "Exporting UAE";
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="dubai" --type="movie" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/dubai.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="abu dhabi" --type="movie" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/abudhabi.xml"

/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="dubai" --type="poi" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/dubai.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="abu dhabi" --type="poi" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/abudhabi.xml"

/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="dubai" --type="event" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/dubai.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="abu dhabi" --type="event" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/abudhabi.xml"


#Export Singapore
echo "";
echo "Exporting Singapore"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="singapore" --type="movie" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/singapore.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="singapore" --type="poi" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/singapore.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="singapore" --type="event" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/singapore.xml"

#Export Ny
echo "";
echo "Exporting NY"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="ny" --type="movie" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/ny.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="ny" --type="poi" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/ny.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="ny" --type="event" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/ny.xml"

#Export Chicago
echo "";
echo "Exporting Chicago"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="chicago" --type="movie" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/chicago.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="chicago" --type="poi" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/chicago.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="chicago" --type="event" --language="en-US" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/chicago.xml"


#Export London
echo "";
echo "Exporting London"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="london" --type="movie" --language="en-GB" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/london.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="london" --type="poi" --language="en-GB" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/london.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="london" --type="event" --language="en-GB" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/london.xml"

#Export Lisbon
echo "";
echo "Exporting Lisbon"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="lisbon" --type="movie" --language="pt" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/lisbon.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="lisbon" --type="poi" --language="pt" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/lisbon.xml"
/var/vhosts/projectn/httpdocs/./symfony projectn:export --city="lisbon" --type="event" --language="pt" --destination="/var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/lisbon.xml"



echo "Zipping Pois";
zip /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/pois.zip /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/*
echo "Creating MD5";
md5sum /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/pois.zip >> /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/pois/pois.zip.md5sum

echo "Zipping Events";
zip /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/events.zip /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/*
echo "Creating MD5";
md5sum /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/events.zip >> /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/events/events.zip.md5sum

echo "Zipping Movies";
zip /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/movies.zip /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/*
echo "Creating MD5";
md5sum /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/movies.zip >> /var/vhosts/projectn/httpdocs/export/export_$(date +'%Y%m%d')/movies/movies.zip.md5sum

