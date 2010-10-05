<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of beijing Venue Mapper
 *
 * @package test
 * @subpackage beijing.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */

class beijingZHFeedPoiMapperTest extends PHPUnit_Framework_TestCase
{

    protected $vendor;
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    ProjectN_Test_Unit_Factory::createDatabases();

    Doctrine::loadData('data/fixtures');

    $this->vendor = Doctrine::getTable( 'Vendor' )->findOneByCity('beijing_zh');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
      $this->pdoDB = null;
      ProjectN_Test_Unit_Factory::destroyDatabases();
  }

  public function testFeedDownload()
  {
        $cookieFile = tempnam ("/tmp", "CURLCOOKIE");

      // Get the FORM
        $curl = new Curl( "http://www.timeoutcn.com/Account/Login.aspx?ReturnUrl=/admin/n/london/Default.aspx" );
        $curl->setCurlOption(CURLOPT_COOKIEJAR, $cookieFile);
        $curl->exec();
        $loginPageHTML = $curl->getResponse();

        // Remove all news lines, as it's easier to Preg match
        $newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
        $loginPageHTML = str_replace($newlines, "", html_entity_decode($loginPageHTML));

        // Get the Inputs
        preg_match_all("|<input.*/>|U",$loginPageHTML, $Loginfields);

        // Extract the name & value of the Fields
        $fields = array( '__EVENTTARGET' => '', '__EVENTARGUMENT' => '', '__EVENTVALIDATION' => '', 'ctl00$CM$Login1$UserName' => '', 'ctl00$CM$Login1$Password' => '', 'ctl00$CM$Login1$LoginButton' => '', 'ctl00$CM$Login1$RememberMe' => '' );
        foreach( $Loginfields[0] as $input )
        {
            preg_match( '/<input.*?name\\s*=\\s*"?([^\\s>"]*).*?value\\s*=\\s*"?([^\\s"]*).*?/i', $input, $match);
            if( count($match) == 3 )
            {
                $fields[ $match[1] ] = $match[2];
            }
        }

        // Set username / Password
        $fields[ 'ctl00$CM$Login1$UserName' ] = 'tolondon';
        $fields[ 'ctl00$CM$Login1$Password' ] = 'to3rjk&e*8dsfj9';

        // Overriders
        $fields[ '__EVENTTARGET' ] = 'ctl00$CM$Login1$LoginButton';
        $fields[ '__EVENTARGUMENT' ] = '';
        
        echo 'Downloading XML' . PHP_EOL;
        // Send POST request
        $curl = new Curl( "http://www.timeoutcn.com/Account/Login.aspx?ReturnUrl=/admin/n/london/Default.aspx", $fields, "POST");
        $curl->setCurlOption(CURLOPT_COOKIEFILE, $cookieFile);
        $curl->exec();

        file_put_contents( '/n/beijing_zh.xml', $curl->getResponse());

        var_dump(file_get_contents( $cookieFile ));
        unlink($cookieFile);
  }
}