<?php
// Curl Mockup Class
class CurlMock extends Curl
{
    private $fileStringData;

    public function __construct( $url, $parameters     = array(),
                                        $requestMethod  = 'GET')
    {
        parent::__construct( $url, $parameters, $requestMethod );
    }

    // Override function, This will Read file from Local Disk insted of URL
    public function  exec() {

        if( !file_exists( $this->getUrl() ) )
        {
            throw new Exception( 'File not found : ' . $this->getUrl());
        }

        $this->fileStringData = file_get_contents( $this->getUrl() );
    }

    // override returning contents to local contents
    public function  getResponse() {

        return $this->fileStringData;
    }
}
?>
