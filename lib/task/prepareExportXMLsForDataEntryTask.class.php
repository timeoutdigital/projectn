<?php

class prepareExportXMLsForDataEntryTask extends sfBaseTask
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
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_REQUIRED, 'The city which we want to export'),
      new sfCommandOption('destination', null, sfCommandOption::PARAMETER_REQUIRED, 'The destination file where the output is written into'),
      new sfCommandOption('xml', null, sfCommandOption::PARAMETER_REQUIRED, 'Location of poi xml to check this export against', 'poop'),
      new sfCommandOption('language', null, sfCommandOption::PARAMETER_REQUIRED, 'The language of the city we want to export', 'en-GB'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type of data we want to export (e.g. poi, event, movies'),


      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'prepareExportXMLsForDataEntry';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    date_default_timezone_set( 'Europe/London' );

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    //$this->_vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( $options['city'], $options['language'] );
    $itemXml = $options[ 'xml' ];

    $xml =  simplexml_load_file( $itemXml );

    foreach ($xml as $element)
    {
        foreach ($element->attributes() as $attribute => $value)
        {
            if( $attribute == 'vpid' )
            {
                $recordId = (int) substr( (string) $value,5) ;
            }
        }

        $vendorItemId = null;

        $record = Doctrine::getTable( $options[ 'type' ] )->find( $recordId );

        switch ( $options[ 'type' ] )
        {
        	case 'poi':
                $metaClass = 'PoiMeta';
                $lookup = 'vendor_poi_id';
        		break;
            case 'event':
                 $metaClass = 'EventMeta';
                 $lookup = 'vendor_event_id';
        		break;
            case 'movie':
                 $metaClass = 'MovieMeta';
                 $lookup = 'vendor_movie_id';
        		break;

        	default:
        		break;
        }
        foreach ( $record[ $metaClass ] as $meta)
        {
            if( $meta[ 'lookup' ] == $lookup )
            {
                $vendorItemId = $meta[ 'value' ];
            }
        }
        if( is_null( $vendorItemId ) )
        {
            continue;
        }

        $element['vpid' ] = $vendorItemId;

    }
    file_put_contents( $options['destination' ] , $xml->asXML() );
  }
}
