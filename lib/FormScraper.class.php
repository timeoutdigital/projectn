<?php
/**
 * Class for Form Scraping, Built in for two level scraping only (Login form and resonse)
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 * @todo support for Multiple Forms in page, this will enable multi level of scraping
 *
 *
 */

class FormScraper
{
    private $response;
    private $requestURL;
    private $postBackURL;
    private $postBackMethod;
    private $formFields;
    private $curlClass;
    private $cookieFilePath;
    private $curlObject;
    
    public function  __construct( $formURL, $curlClass = 'Curl' ) {

        if( !is_string($formURL) )
        {
            throw new FormScraperException( "Invalid request URL: {$formURL}" );
        }

        if( !is_subclass_of($curlClass, 'Curl') && $curlClass !== 'Curl' )
        {
            throw new FormScraperException( "Given CurlClass name was not of Curl child class: {$curlClass}" );
        }

        // Set variable defaults
        $this->curlClass = $curlClass;
        $this->requestURL = $formURL;
        $this->formFields = array(); // Empty array fields
        $this->postBackMethod = 'GET';
        
        // Get Form Data
        $this->curlFormData();
        
        //extract the Form Data
        // Get the Postback URL        // Get post Method
        $this->generatePostbackURLAndSetPostbackMethod();

        // Get form inputs
        $this->extractFormInputFields();
    }

    /**
     * fetch data using Curl
     * @param string $url
     */
    private function curlFormData( $url = null)
    {
        if( !is_string($url) )
        {
            $url = $this->requestURL;
        }
        // Create CURL class and send the request URL
        $this->curlObject = new $this->curlClass( $url, $this->formFields, $this->postBackMethod );

        if( $this->cookieFilePath == null )
        {
            $this->cookieFilePath = tempnam( '/tmp', 'COOKIE');
            $this->curlObject->setCurlOption(CURLOPT_COOKIEJAR, $this->cookieFilePath );
        } else {
            $this->curlObject->setCurlOption(CURLOPT_COOKIEFILE, $this->cookieFilePath);
        }
        
        $this->curlObject->exec(); // get page data
        $this->response = $this->curlObject->getResponse();
    }

    /**
     * Extract Postback URL and Method fromt the FORM tag.
     */
    private function generatePostbackURLAndSetPostbackMethod()
    {
        // get the Form Tag
        preg_match('/<form.*?>/i', $this->response, $matchedForm );

        if( !is_array( $matchedForm ) || count( $matchedForm ) != 1 )
        {
            throw new FormScraperException( "No FORM tag found in requested URL: {$this->requestURL}" );
        }

        // Extract action URL
        preg_match('/action=[\'"]([^\'"]*?)[\'"]/i', $matchedForm[0], $matchedURL);
        if( is_array( $matchedURL ) && count( $matchedURL ) == 2 )
        {
            // set default postback URL
            $this->postBackURL = urldecode( $matchedURL[1] );
        }

        // Filter postback URL
        $this->filterPostBackURL( );

        // default POSTBACK METHOD
        $this->postBackMethod = 'POST';
        
        // Get Postback Method
        preg_match('/method=[\'"]([^\'"]*?)[\'"]/i', $matchedForm[0], $matchedMethod);
        if( is_array( $matchedMethod) && count( $matchedMethod ) == 2 )
        {
            $this->postBackMethod = ( strtolower( $matchedMethod[1] ) == 'post' ) ? 'POST' : 'GET';
        }
    }

    /**
     * Manipulate postback URL acording to it's relative path etc...
     * @return none
     */
    private function filterPostBackURL( )
    {
         // If PostbackURL not found or Empty, use Request url as POSTBACK URL
        if( $this->postBackURL == null || trim( $this->postBackURL ) == '' )
        {
            return $this->postBackURL = $this->requestURL;
        }

        // Check to see postback url Begins with FULL http schema
        if( strlen( $this->postBackURL ) > 4 &&
            in_array( strtolower( substr( $this->postBackURL, 0, 5 ) ), array( 'http:', 'https' ) ) )
        {
            return; // postback URL already have FULL URL
        }

        // Parse_URL both URL
        $requestParse = parse_url( $this->requestURL );

        // In this case, we are receiving the POSTBACK url with RELATIVE_PATH (/folder/file.php) link,
        // hence, it's require to amend domain and current request folder name with this link to generate valid URL
        // @todo: modify this as required
        $domain = $requestParse['scheme'] . '://' . $requestParse['host'];

        // require to split, becasue this path contains File name from original request
        $requestPathSplit = explode( '/', isset($requestParse['path']) ? $requestParse['path'] : '' );

        array_pop($requestPathSplit); // remove the LAST array

        // amend the Path to domain
        $domain .= implode('/', $requestPathSplit) ;

        // amend the Postback filename and query strings to the end of domain
        $this->postBackURL = $domain . '/' . trim($this->postBackURL);
    }
    
    /**
     * Extract the INPUT fields and their default values
     * @todo: This extract INPUT fields only, this should be extended to capture
     * textarea, select and input[checkbox/radio] buttons "selected" values
     */
    private function extractFormInputFields()
    {
        // Match all the Input Fields
        $regEx = '/<.*?input.*?>/i';
        
        preg_match_all( $regEx, $this->response, $matchingFields );
        foreach( $matchingFields[0] as $input )
        {
            $regEx = '/name=[\'"]([^\'"]*?)[\'"]/i';
            preg_match( $regEx, $input, $nameMatch );

            if( !is_array( $nameMatch ) || count( $nameMatch ) != 2 )
            {
                continue;
            }
            // Set the Input Field Name
            $this->formFields[ $nameMatch[1] ] = '';

            // Extract the Value
            $regEx = '/value=[\'"]([^\'"]*?)[\'"]/i';
            preg_match( $regEx, $input, $valueMatch );

            if( !is_array( $valueMatch ) || count( $valueMatch ) != 2 )
            {
                continue;
            }
            // Set Input Default Value
            $this->formFields[ $nameMatch[1] ] = $valueMatch[1];
        }
    }

    /**
     * get last request response
     * @return string
     */
    public function getResponse( )
    {
        
        return $this->response;
    }

    /**
     * Get the Last request Header Information
     * @return string
     */
    public function getHeader()
    {
        if ( $this->curlObject == null )
        {
            return null;
        }

        return $this->curlObject->getHeader();
    }

    /**
     * Get the Last request Header information by field
     * @param string $field
     * @return string
     */
    public function getHeaderField( $field )
    {
        if ( $this->curlObject == null )
        {
            return null;
        }
        
        return $this->curlObject->getHeaderField( $field );
    }

    /**
     * Get the fields found in the Form, returned as array( 'field_name' => 'field_value' )
     * @return string
     */
    public function getFormFields()
    {
        return $this->formFields;
    }

    /**
     * Get postback URL
     * @return string
     */
    public function getPostBackURL()
    {
        return $this->postBackURL;
    }

    /**
     * Postback the Form and
     */
    public function doPostBack( array $fields )
    {
        // merge/override fields value
        $this->formFields = array_merge( $this->formFields, $fields );
        
        $this->curlFormData( $this->postBackURL );
    }
    
}

class FormScraperException extends Exception {}