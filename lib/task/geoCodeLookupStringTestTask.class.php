<?php

class geoCodeLookupStringTestTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('total', null, sfCommandOption::PARAMETER_REQUIRED, 'The amount of POIs to sample', 10),
      new sfCommandOption('sleep', null, sfCommandOption::PARAMETER_REQUIRED, 'Pause between requests in 10ths of a second', 5),
      new sfCommandOption('vendor', null, sfCommandOption::PARAMETER_REQUIRED, 'Only use POIs from this vendor id', false),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'test-geocode-lookup-string';
    $this->briefDescription = 'Test the efficiency of several google lookup strings.';
    $this->detailedDescription = <<<EOF
The [same-geocodes|INFO] task does things.
Call it with:

  [php symfony test-geocode-lookup-string|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $totalPois = $options['total'];

    $accuracyValues = array();
    $accuracyValues[0] = "Unknown location.";
    $accuracyValues[1] = "Country level accuracy.";
    $accuracyValues[2] = "Region (state, province, prefecture, etc.) level accuracy.";
    $accuracyValues[3] = "Sub-region (county, municipality, etc.) level accuracy.";
    $accuracyValues[4] = "Town (city, village) level accuracy.";
    $accuracyValues[5] = "Post code (zip code) level accuracy.";
    $accuracyValues[6] = "Street level accuracy.";
    $accuracyValues[7] = "Intersection level accuracy.";
    $accuracyValues[8] = "Address level accuracy.";
    $accuracyValues[9] = "Premise (building name, property name, shopping center, etc.) level accuracy.";
    
    echo PHP_EOL;
    echo "Getting Random Sample of $totalPois POIs";
    $poiQuery = Doctrine::getTable('Poi')->createQuery('p')
            ->orderBy("RAND()")
            ->limit( $totalPois );

    if( is_numeric( $options['vendor'] ) ) $poiQuery->addWhere("p.vendor_id = ?",  $options['vendor']);
    
    $poiCollection = $poiQuery->execute();
    
    echo " [done]" . PHP_EOL;
    echo PHP_EOL;

    $query_strings = array();
    $query_strings[] = array( 'street', 'additional_address_details', 'city', 'country' );
    $query_strings[] = array( 'house_no', 'street', 'zips', 'city' );
    //$query_strings[] = array( 'street', 'zips', 'city' );

    $accuracy_log = array();
    $results = array();

    echo "Trying String Formats:". PHP_EOL . PHP_EOL;
    for( $x=0; $x<count( $query_strings ); $x++ )
        echo "\t#" . ( $x + 1 ) . " -- " . stringTransform::concatNonBlankStrings( ', ', $query_strings[$x]) . PHP_EOL;

    echo PHP_EOL;

    echo "Google Accuracy Definitions:" . PHP_EOL . PHP_EOL;
    foreach( $accuracyValues as $k => $v )
        echo "\t" . $k . " - " . $v . PHP_EOL;

    echo PHP_EOL;

    echo "Querying Google, Showing Accuracy:" . PHP_EOL . PHP_EOL;

    for( $x=0; $x<count( $query_strings ); $x++ )
    {
        echo "\t#" . ( $x + 1 ) . " -- ";
        
        foreach( $poiCollection as $poiObj )
        {
            $fields = array();
            foreach( $query_strings[$x] as $field )
               $fields[] = trim( $poiObj[ $field ], " ," );

            $address = stringTransform::concatNonBlankStrings( ', ', $fields );
            $g = new geoEncode();
            $g->setAddress( $address, $poiObj->Vendor );
            $g->getGeoCode( "ABQIAAAADCvbfZ1Y339Rd16PKF4k6BT2yXp_ZAY8_ufC3CFXhHIE1NvwkxQj6XSAiXD_sPB9VI5lnTE0m8bWvQ" );

            $res = array( $g->getLatitude(), $g->getLongitude(), $g->getAccuracy(), $address, $g->getRawResponse(), $poiObj['id'] );
            $results[$x][] = $res;

            $acc = $g->getAccuracy();
            echo $acc;
            
            $accuracy_log[$x][] = $acc;
            time_nanosleep(0, (int) $options['sleep'] * 100000000 );
        }
        echo " -- Average Accuracy: ". ( array_sum( $accuracy_log[$x] ) / count( $accuracy_log[$x] ) );

        $equal_or_above_eight = 0;
        foreach( $accuracy_log[$x] as $acc )
            if( $acc >= 8 ) $equal_or_above_eight++;
        echo " -- Acceptable " . $equal_or_above_eight . "/" . $totalPois;
        
        echo PHP_EOL;
    }

    echo PHP_EOL;

    echo "-- Different LAT/LONGS: " . PHP_EOL;
    for( $x=0; $x<count( $results[0] ); $x++ )
    {
        if( round( $results[0][$x][0], 4 ) !== round( $results[1][$x][0], 4 ) ||
            round( $results[0][$x][1], 4 ) !== round( $results[1][$x][1], 4 ) )
        {
            echo PHP_EOL;
            echo( "\tid: ".$results[0][$x][5].'. "'. $results[0][$x][3] . '"' . " = " . $results[0][$x][0] . "," . $results[0][$x][1] . " -- RAW: " . $results[0][$x][4] . PHP_EOL );
            echo( "\tid: ".$results[0][$x][5].'. "'. $results[1][$x][3] . '"' . " = " . $results[1][$x][0] . "," . $results[1][$x][1] . " -- RAW: " . $results[1][$x][4] . PHP_EOL );
        }
    }

    echo PHP_EOL;
    
    echo "-- Different Accuracy: " . PHP_EOL;
    for( $x=0; $x<count( $results[0] ); $x++ )
    {
        if( $results[0][$x][2] !== $results[1][$x][2] )
        {
            echo PHP_EOL;
            echo( "\tid: ".$results[0][$x][5].'. "'. $results[0][$x][3] . '"' . " = " . $results[0][$x][0] . "," . $results[0][$x][1] . " -- RAW: " . $results[0][$x][4] . PHP_EOL );
            echo( "\tid: ".$results[0][$x][5].'. "'. $results[1][$x][3] . '"' . " = " . $results[1][$x][0] . "," . $results[1][$x][1] . " -- RAW: " . $results[1][$x][4] . PHP_EOL );
        }
    }

    echo PHP_EOL;

    echo "-- Low Accuracy: " . PHP_EOL;
    for( $x=0; $x<count( $results[0] ); $x++ )
    {
        if( $results[0][$x][2] < 8 || $results[1][$x][2] < 8 )
        {
            echo PHP_EOL;
            echo( "\tid: ".$results[0][$x][5].'. "'. $results[0][$x][3] . '"' . " = " . $results[0][$x][0] . "," . $results[0][$x][1] . " -- RAW: " . $results[0][$x][4] . PHP_EOL );
            echo( "\tid: ".$results[0][$x][5].'. "'. $results[1][$x][3] . '"' . " = " . $results[1][$x][0] . "," . $results[1][$x][1] . " -- RAW: " . $results[1][$x][4] . PHP_EOL );
        }
    }

    echo PHP_EOL;
  }
}
