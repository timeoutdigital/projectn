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
      new sfCommandOption('destination', null, sfCommandOption::PARAMETER_REQUIRED, 'The destination file where the output is written into'),
      new sfCommandOption('xml', null, sfCommandOption::PARAMETER_REQUIRED, 'Location of poi xml to check this export against', 'poop'),
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

    $itemXml = $options[ 'xml' ];

    $xml =  simplexml_load_file( $itemXml );

    foreach ($xml as $element)
    {
        switch ( $options[ 'type' ] )
        {
        	case 'poi':
                $metaClass  = 'PoiMeta';
                $lookup     = 'vendor_poi_id';
                $id         = 'vpid';
        		break;

            case 'event':
                 $metaClass = 'EventMeta';
                 $lookup    = 'vendor_event_id';
                 $id        = 'id';
        		break;

            case 'movie':
                 $metaClass = 'MovieMeta';
                 $lookup    = 'vendor_movie_id';
                 $id        = 'id';
        		break;

        	default:
        	   throw new Exception("Invalid type given");
        	   break;
        }


        $recordId = (int) substr( (string) $element[ $id ], 5);

        $vendorItemId = null;

        $record = Doctrine::getTable( $options[ 'type' ] )->find( $recordId );

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

        $element[ $id ] = $vendorItemId;

    }
    file_put_contents( $options['destination' ] , $xml->asXML() );
  }
}
