<?php


$file = file_get_contents('sydney.csv');


$out = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.2">
  <Document>
    <name>Timeout</name>
EOF;


foreach ( explode(PHP_EOL, $file) as $latlong )
{
$out .= <<<EOF
    <Placemark>
      <name xmlns=""><![CDATA[Catalonia]]></name>
      <description xmlns=""></description>
      <Point>
        <coordinates>$latlong</coordinates>
      </Point>
    </Placemark>
EOF;
}


$out .= <<<EOF
  </Document>
</kml>
EOF;

file_put_contents( 'sydney.kml' , $out);