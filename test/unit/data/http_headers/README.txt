# Curl Options:
# L - Follow Redirects
# I - Return Only Headers

# To get a normal header.
curl -L -I http://www.google.co.uk/intl/en_com/images/srpr/logo1w.png

# To get a 403 you need to send a last modified date.
curl -L -I http://www.google.com/favicon.ico -H "If-Modified-Since:Thu, 25 Mar 2010 09:42:43 GMT"
