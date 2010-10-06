<?php
/**
 * Beijing Import Base Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class BeijingFeedBaseMapper extends DataMapper
{
    /**
    * @var Vendor
    */
    protected $vendor;

    /**
     * @var xmlNodes
     */
    protected $xmlNodes;

    /**
     * @var params
     */
    protected $params;

    /**
     * Create Base Mapper
     * @param PDO $pdoDB (Connected object)
     * @param geocoder $geocoderr
     */
    public function  __construct( Vendor $vendor, $params ) {

        if( !isset( $vendor ) || !$vendor )
        {
            throw new Exception( 'Vendor not found.' );
        }

        // Validate Params
        if( is_null($params) || !is_array($params) || count($params) <= 0 )
        {
            throw new Exception( 'Parameter Required' );
        }

        // @todo: create Exception Base an throw all error in one Exception
        // validate datasource
        if( !isset( $params['datasource'] ) || !is_array( $params['datasource'] ) || count( $params['datasource'] ) <= 0 )
        {
            throw new Exception( 'Parameter missing datasource configuration' );
        }

        if( !isset( $params['datasource']['classname'] )  || empty( $params['datasource']['classname'] ) )
        {
            throw new Exception( 'Parameter missing datasource ClassName' );
        }

        if( !isset( $params['datasource']['url'] )  || empty( $params['datasource']['url'] ) )
        {
            throw new Exception( 'Parameter missing datasource URL' );
        }

        if( !isset( $params['datasource']['username'] )  || empty( $params['datasource']['username'] ) )
        {
            throw new Exception( 'Parameter missing datasource username' );
        }
        
        if( !isset( $params['datasource']['password'] )  || empty( $params['datasource']['password'] ) )
        {
            throw new Exception( 'Parameter missing datasource password' );
        }

        $this->vendor       = $vendor; // Set Vendor
        $this->params       = $params;

        // Fetch the Data
        $this->getXMLFeed();
    }

    protected function fixHtmlEntities( $string )
    {
        $string = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );

        return $string;
    }

    protected function roundNumberOrReturnNull( $string )
    {
        return is_numeric( (string) $string ) ? round( (string) $string ) : null;
    }

    protected function extractTimeOrNull( $string )
    {
        $date = DateTime::createFromFormat( 'H:i', $string );

        return ( $date === false ) ? null : $string;
    }

    protected function clean( $string , $chars = '' )
    {
        return stringTransform::mb_trim( $string, $chars );
    }

    /**
     * Beijing chinese feed require authentication before you can download the Feed,
     * authentication is done through screen Scraping login page and submiting with Login value.
     * curl class is utilized to do this and the feed download page checking authentication by cookie,
     * it is important to pass a cookie file
     */
    protected function getXMLFeed()
    {
        // Set temporary Cookie File
        $cookieFile = tempnam ("/tmp", "CURLCOOKIE");

        // Get the Login Form with its view state and ect other asp.net hidden fields
        $curl = new $this->params['datasource']['classname']( $this->params['datasource']['url'] ); // "http://www.timeoutcn.com/Account/Login.aspx?ReturnUrl=/admin/n/london/Default.aspx"
        $curl->setCurlOption(CURLOPT_COOKIEJAR, $cookieFile);
        $curl->exec();

        // Get the Login page HTML and extract all input fields
        $loginPageHTML = $curl->getResponse();

        // Remove all news lines, as it's easier to Preg match
        $newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
        $loginPageHTML = str_replace($newlines, "", html_entity_decode($loginPageHTML));

        // Extract all the Inputs
        preg_match_all("|<input.*/>|U",$loginPageHTML, $Loginfields);

        if( !is_array( $Loginfields ) || empty( $Loginfields ) )
        {
            throw new Exception( 'Failed to Extract Login fields from login form at ' . $this->params['datasource']['url'] );
        }

        // Set default Fields Expected
        $fields = array( '__EVENTTARGET' => '', '__EVENTARGUMENT' => '', '__EVENTVALIDATION' => '', 'ctl00$CM$Login1$UserName' => '', 'ctl00$CM$Login1$Password' => '', 'ctl00$CM$Login1$LoginButton' => '', 'ctl00$CM$Login1$RememberMe' => '' );

        // Extract the name & value of the Fields
        foreach( $Loginfields[0] as $input )
        {
            preg_match( '/<input.*?name\\s*=\\s*"?([^\\s>"]*).*?value\\s*=\\s*"?([^\\s"]*).*?/i', $input, $match);
            if( count($match) == 3 )
            {
                $fields[ $match[1] ] = $match[2];
            }
        }

        // Set username / Password
        $fields[ 'ctl00$CM$Login1$UserName' ] = $this->params['datasource']['username'];
        $fields[ 'ctl00$CM$Login1$Password' ] = $this->params['datasource']['password'];

        // Overriders / Simulate Javascript Click event
        $fields[ '__EVENTTARGET' ] = 'ctl00$CM$Login1$LoginButton';
        $fields[ '__EVENTARGUMENT' ] = '';

        echo 'Downloading XML' . PHP_EOL;

        // Send the request as POST to authenticate and download the Feed
        $curl = new $this->params['datasource']['classname']( $this->params['datasource']['url'], $fields, "POST");
        $curl->setCurlOption(CURLOPT_COOKIEFILE, $cookieFile); // Give the Cookie File that already written in previous request
        $curl->exec();

        // get the Response
        $response = $curl->getResponse();
        
        // Validate XML
        if( !$this->isValidResponse( str_replace($newlines , "", $response ) ) )
        {
            throw new Exception( 'Failed to download the feed, response to pass valid feed test...' );
            var_dump( $response );
        }

        // Parse as XML
        $this->xmlNodes = simplexml_load_string( $response );
        
        // [Safe] Delete the Cookie file
        @unlink($cookieFile);
    }

    /**
     * Validate the Response after authentication, Require to Override,
     * default allways return fasle!
     * @param string $response
     * @return boolean
     */
    protected function isValidResponse( $response )
    {
        return false;
    }
}
?>