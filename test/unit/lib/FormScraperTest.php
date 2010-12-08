<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../bootstrap.php';
require_once TO_TEST_MOCKS . '/curl.mock.php';

/**
 * Test class for Form scraper
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class FormScraperTest extends PHPUnit_Framework_TestCase
{
    private $scraper;
    /**
    * Sets up the fixture, for example, opens a network connection.
    * This method is called before a test is executed.
    */
    protected function setUp()
    {
        // URL to Send Request, This domain name and Folder will be replaced with TEST data Path in FormScraperCurlMock class
        $url = 'http://n.test.com/authentication/formscraper_login_screen.html';

        $this->scraper = new FormScraper( $url, 'FormScraperCurlMock' );
        $this->scraper->doFormPageRequest();
    }

    /**
    * Tears down the fixture, for example, closes a network connection.
    * This method is called after a test is executed.
    */
    protected function tearDown()
    {
    }

    public function testFormScraperForConstructorRequest()
    {
        // This response will return the Login FORM page data
        $response = $this->scraper->getResponse(); 

        // Assert that constructor do request curl and get response with login form fields.
        $this->assertNotNull( $response );
        $this->assertContains( '<form action="Login.aspx?ReturnUrl', $response );
        $this->assertContains( '<input type="hidden" value="" id="__EVENTTARGET" name="__EVENTTARGET">', $response );
        $this->assertContains( 'wEWBQKF0cbgAwKUvNLXDwL665/2DAKB0uuVDwKnz+iRApMkY1XisbvPK4tFDTGaQpu/a45b', $response );
        $this->assertContains( '<input type="text" class="text-input" id="Login2_UserName" name="Login2$UserName" value="test_username">', $response );
    }

    /*
     * FormScraper constructor should have initiated the first request and extracted Form fields at this point.
     */
    public function testGetFormFields()
    {
        // get the Form Fields
        $fields = $this->scraper->getFormFields();

        $this->assertEquals( 8, count($fields) );

        // extract the Keys
        $keys = array_keys($fields);
        $this->assertEquals( '__EVENTTARGET', $keys[0] );
        $this->assertEquals( '__EVENTARGUMENT', $keys[1] );
        $this->assertEquals( '__VIEWSTATE', $keys[2] );
        $this->assertEquals( '__EVENTVALIDATION', $keys[3] );
        $this->assertEquals( 'Login2$UserName', $keys[4] );
        $this->assertEquals( 'Login2$Password', $keys[5] );
        $this->assertEquals( 'Login2$RememberMe', $keys[6] );
        $this->assertEquals( 'Login2$LoginButton', $keys[7] );

        // check for values
        $this->assertEquals( '', $fields['__EVENTTARGET'] );
        $this->assertEquals( '', $fields['__EVENTARGUMENT'] );
        $this->assertEquals( '/wEPDwUKMTg2NjQyMDQ4NQ9kFgICAw9kFgICAQ9kFgJmD2QWAgIJDxAPFgIeB0NoZWNrZWRoZGRkZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAQURTG9naW4yJFJlbWVtYmVyTWV2FLADQ8xAPLE9X/YqiMco03mb7Q==', $fields['__VIEWSTATE'] );
        $this->assertEquals( '/wEWBQKF0cbgAwKUvNLXDwL665/2DAKB0uuVDwKnz+iRApMkY1XisbvPK4tFDTGaQpu/a45b', $fields['__EVENTVALIDATION'] );
        $this->assertEquals( 'test_username', $fields['Login2$UserName'] );
        $this->assertEquals( '', $fields['Login2$Password'] );
        $this->assertEquals( '', $fields['Login2$RememberMe'] );
        $this->assertEquals( '登陆', $fields['Login2$LoginButton'] );
    }

    public function testGetPostBackURL()
    {
        $url = $this->scraper->getPostBackURL();
        $this->assertEquals( 'http://n.test.com/authentication/Login.aspx?ReturnUrl=/Admin/ExportTOLondon/MoviesData.aspx/formscraper_login_response.html', $url);
    }

    public function testGetResponse()
    {
        // postback fields
        $fields = array( 'Login2$UserName' => 'username', 'Login2$Password' => 'testPass' );
        $this->scraper->doPostBack( $fields );

        // test the Fields
        $formFields = $this->scraper->getFormFields();

        $this->assertEquals( 'username' , $formFields['Login2$UserName'] );
        $this->assertEquals( 'testPass' , $formFields['Login2$Password'] );

        // get response
        $response = $this->scraper->getResponse();

        // Response should return everything from the returned
        $this->assertEquals( 'postback reponse', $response);
    }
 
}

class FormScraperCurlMock extends CurlMock
{
    public function __construct( $url, $parameters     = array(),
                                        $requestMethod  = 'GET')
    {
        // amend the domain with Testpath
        $pathArray = explode( '/', $url );

        // rebuild
        $url = TO_TEST_DATA_PATH . '/' . array_pop( $pathArray );
        
        parent::__construct( $url, $parameters, $requestMethod );
    }
}