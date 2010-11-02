<?php

/**
 * Mock the London API Crawler
 */
class LondonAPICrawlerMock extends LondonAPICrawler
{

    public function crawlApi()
    {
        for( $i=1; $i<=11; $i++ )
        {
            $fileContents = file_get_contents( $this->getDetailsUrl() );
            $fileContents = str_replace( '{id}', $i, $fileContents );
            $fileContents = str_replace( '{name}', "Poi $i", $fileContents );

            $xml = simplexml_load_string( $fileContents );
            $this->mapper->doMapping( $xml->response->row );
        }
    }
}