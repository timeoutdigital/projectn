<?php
/**
 * NY Feed Base mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class nyFeedBaseMapper extends DataMapper
{

    protected $vendor;
    protected $xmlNodes;
    protected $params;
    
    public function  __construct( Vendor $vendor, $params ) {

        $this->vendor   = $vendor;
        $this->params   = $params;

    }

    /**
     * Download File from FTP and Return File path...
     * @return string File Location
     */
    protected function ftpGetXMLFile()
    {
        $ftpClientObj = new $this->params['ftp']['classname']( $this->params['ftp']['ftp'], $this->params['ftp']['username'], $this->params['ftp']['password'] , $this->vendor[ 'city' ] );
        $ftpClientObj->setSourcePath( $this->params['ftp']['dir'] );

        // Downloading File
        echo 'Downloading File' . PHP_EOL;
        return $ftpClientObj->fetchLatestFileByPattern( $this->params['ftp']['file'] );
    }

    /**
     * Get Nodes of given Xpath in $XML
     * @param string $nodePath
     * @param SimpleXMLElement $xml
     * @return Array
     */
    protected function getXMLNodesByPath( $nodePath, SimpleXMLElement $xml )
    {
        if( !$xml)
        {
            throw new Exception(' No SimpleXMLElement Provided' );
        }

        return $xml->xpath( $nodePath );
    }
}

?>
