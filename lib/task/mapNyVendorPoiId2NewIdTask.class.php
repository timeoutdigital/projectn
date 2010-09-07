<?php
/*
 *
 */
class mapNyVendorPoiId2NewId extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
        ));

        $this->namespace        = 'projectn';
        $this->name             = 'mapNYVendorPoiId-NewId';
        $this->briefDescription = 'Check Database and Update Vendor POI id to new XML feed Id using name and street name';
        $this->detailedDescription = '';
    }

    protected function execute($arguments = array(), $options = array())
    {
        //Connect to the database.
        $databaseManager = new sfDatabaseManager($this->configuration);
        Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
        $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

        // Get XML File
        $arrayXML           = $this->getNYXmlFileData();

        if( $arrayXML === false || empty($arrayXML))
        {
            throw new Exception( 'NY::Poi Unable to Get XMl File' );
        }

        // Vendor ID from NY  = 1
        // Loop XML nodes and update DB
        foreach( $arrayXML as $poiNode )
        {
            $poiName                    = stringTransform::mb_trim( (string)$poiNode->identifier );
            $streetName                 = stringTransform::mb_trim( (string)$poiNode->street );
            // Query database to find Existing records based on Name & Street
            $existingPois               = Doctrine::getTable( 'Poi' )->findByVendorIdAndPoiNameAndStreet( 1, $poiName, $streetName );

            if( $existingPois->count() != 1 )
            {
                if( $existingPois->count() > 1 )
                {
                    echo "Multiple: \t" . (string)$poiNode['id'] ." \t" . $poiName . "\t" . $streetName . "\t" .  PHP_EOL;

                } else if( $existingPois->count() == 0 )
                {
                    echo "New Poi: \t" . (string)$poiNode['id'] ." \t" . $poiName . "\t" . $streetName . "\t" .  PHP_EOL;
                }
                continue; // Do NOT update Multiple records! and skip new once!
            }

            $poi                        = $existingPois[0];
            // Put it in a try catch to catch any exception found!
            try{

                // If same ID found, Mark SKIP and continue to next one
                if( $poi['vendor_poi_id'] == (string)$poiNode['id'] )
                {
                    echo "SKIP: \t" . $poi['vendor_poi_id'] ." \t" . (string)$poiNode['id'] . "\t" . $poi['id'] . PHP_EOL;
                    continue;
                }

                $poi['vendor_poi_id']       = (string)$poiNode['id'] ;
                $poi->save();
                echo "Changing: \t" . $poi['vendor_poi_id'] ." \t" . (string)$poiNode['id'] . "\t" . $poi['id'] . PHP_EOL;

            } catch ( Exception $e )
            {
                echo 'Exception Found: ' . $e->getMessage() . PHP_EOL;
            }

        } // venue node

    }


    private function getNYXmlFileData()
    {
        // Set FTP
        $ftpClientObj = new FTPClient( 'ftp.timeoutny.com', 'london', 'timeout', 'ny' );
        $ftpClientObj->setSourcePath( '/NOKIA/' );

        echo "Downloading NY's Event's feed \n";
        $fileNameString = $ftpClientObj->fetchLatestFileByPattern( 'tony_leo.xml' );

        // Load XML file
        $xmlString      = file_get_contents( $fileNameString );
        $xmlDataFixer   = new xmlDataFixer( $xmlString );
        //$xmlDataFixer->addRootElement( 'body' );
        $xmlDataFixer->removeHtmlEntiryEncoding();
        $xmlDataFixer->encodeUTF8();

        $processXmlObj = new processNyXml( '' );
        $processXmlObj->xmlObj  = $xmlDataFixer->getSimpleXML();
        $processXmlObj->setEvents('/leo_export/event')->setVenues('/leo_export/address');

        // return Venue Array() XML
        return $processXmlObj->getVenues();
    }
}

?>
