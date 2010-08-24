<?php
class baseDataSource
{
    protected $type;

    protected $xml;

    const TYPE_POI   = 'poi';
    const TYPE_EVENT = 'event';
    const TYPE_MOVIE = 'poi';

    public function __construct( $type )
    {
        $this->type = $type;
        $this->validateTypeParemeter();
    }

    protected function validateTypeParemeter()
    {
        $validTypes = array(
            self::TYPE_POI,
            self::TYPE_EVENT,
            self::TYPE_MOVIE );

        if( !in_array( $this->type , $validTypes ) )
        {
            throw new Exception( 'Invalid type given for dataSource' );
        }
    }

    public function getXML()
    {
        return $this->xml;
    }

}