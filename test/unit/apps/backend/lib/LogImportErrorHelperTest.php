<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'../../../../../../test/bootstrap/unit.php';
require_once dirname(__FILE__).'../../../../bootstrap.php';

/*
 * 
 * LogImportErrorHelper
 *
 * @package projectn
 * @subpackage logger.lib
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version l.o.l
 *
 */

class LogImportErrorHelperTest extends PHPUnit_Framework_TestCase
{
    private $vendor;
    private $sfAction;
    private $sfRequest;
    private $logImport;

    protected function setUp()
    {
        require_once sfConfig::get( 'sf_root_dir' ) . '/apps/backend/lib/LogImportErrorHelper.class.php';

        ProjectN_Test_Unit_Factory::createDatabases();
        Doctrine::loadData('data/fixtures');
        
        $this->vendor       = Doctrine::getTable( "Vendor" )->findOneByCity( "london" );
        $this->sfAction     = new MockSfAction;
        $this->sfRequest    = new MockSfRequest;

        $this->_addImport();
    }

    protected function tearDown()
    {
        ProjectN_Test_Unit_Factory::destroyDatabases();
        $this->sfAction->getUser()->clearFlash();
    }

    private function _addImport()
    {
        $this->logImport = new LogImport;
        $this->logImport['Vendor'] = $this->vendor;
        $this->logImport['status'] = 'success';
        $this->logImport['total_time'] = '00:13:37';
        $this->logImport->save();
    }

    private function _addImportError( $record, $message = 'An Error Occurred During Import.' )
    {
        $exception = new MockException( $message );

        $logImportError = new LogImportError;
        $logImportError['model'] = get_class( $record );
        $logImportError['exception_class'] = get_class( $exception );
        $logImportError['trace'] = $exception->getMessage();
        $logImportError['message'] = $exception->getMessage();
        $logImportError['log'] = 'Developer specified log message.';
        $logImportError['serialized_object'] = serialize( $record );
        $logImportError['log_import_id'] = $this->logImport['id'];
        $logImportError->save();

        return $logImportError;
    }

    public function testNoErrorIdPassedInQueryString()
    {
        $recordInfo  = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_INVALID_REQUEST, $this->sfAction->getUser()->getFlash( 'error' ) );
        $this->assertNull( $recordInfo['record'] );
    }

    public function testNonNumericIdPassed()
    {
        $this->sfRequest->setParam( 'import_error_id', false );
        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_INVALID_REQUEST, $this->sfAction->getUser()->getFlash( 'error' ) );
    }

    public function testImportErrorNotFound()
    {
        $this->sfRequest->setParam( 'import_error_id', 99999 );
        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_INVALID_IMPORT_ERROR, $this->sfAction->getUser()->getFlash( 'error' ) );
    }

    public function testFailedToDeserialize()
    {
        $logImportError = $this->_addImportError( new stdclass(), 'Class Does Not Extend from Doctrine_Record' );
        $this->sfRequest->setParam( 'import_error_id', $logImportError['id'] );

        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_DESERIALIZE_ERR, $this->sfAction->getUser()->getFlash( 'error' ) );
    }

    public function testNonMatchingVendor()
    {
        $databaseRecord = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $unknownVendor = Doctrine::getTable( "Vendor" )->findOneByCity( "unknown" );
        $feedRecord['vendor_id'] = $unknownVendor['id'];

        $logImportError = $this->_addImportError( $feedRecord, 'Poi With A Different Vendor ID' );
        $this->sfRequest->setParam( 'import_error_id', $logImportError['id'] );

        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_NO_MATCHING_DB_RECORD, $this->sfAction->getUser()->getFlash( 'notice' ) );
    }

    public function testNonMatchingVendorReference()
    {
        $databaseRecord = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $feedRecord['vendor_poi_id'] = 99999;

        $logImportError = $this->_addImportError( $feedRecord, 'Poi With A Different vendor_poi_id' );
        $this->sfRequest->setParam( 'import_error_id', $logImportError['id'] );

        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_NO_MATCHING_DB_RECORD, $this->sfAction->getUser()->getFlash( 'notice' ) );
    }

    public function testInvalidVendorId()
    {
        $databaseRecord = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $feedRecord['vendor_id'] = false;

        $logImportError = $this->_addImportError( $feedRecord, 'Poi With An Invalid Vendor ID' );
        $this->sfRequest->setParam( 'import_error_id', $logImportError['id'] );

        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_INVALID_VENDOR, $this->sfAction->getUser()->getFlash( 'error' ) );
    }

    public function testInvalidVendorReference()
    {
        $databaseRecord = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $feedRecord['vendor_poi_id'] = false;

        $logImportError = $this->_addImportError( $feedRecord, 'Poi With An Invalid vendor_poi_id' );
        $this->sfRequest->setParam( 'import_error_id', $logImportError['id'] );

        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );
        $this->assertEquals( LogImportErrorHelper::MSG_INVALID_VENDOR, $this->sfAction->getUser()->getFlash( 'error' ) );
    }

    public function testExistingRecord()
    {
        $databaseRecord = ProjectN_Test_Unit_Factory::add( 'Poi' );
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $feedRecord['poi_name']     = 'Timeout Magazine';
        $feedRecord['house_no']     = '251';
        $feedRecord['street']       = 'Tottenham Court Road';
        $feedRecord['city']         = 'London';

        $logImportError = $this->_addImportError( $feedRecord, 'Generic Error Message' );
        $this->sfRequest->setParam( 'import_error_id', $logImportError['id'] );
        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );



        // Test class & vendor do not change, and that not error was presented to the user.
        $this->assertEquals( get_class( $databaseRecord ) , get_class( $recordInfo['record'] ), 'Class should not change.' );
        $this->assertEquals( $databaseRecord['vendor_id'], $recordInfo['record']['vendor_id'], 'Vendor should not change' );
        $this->assertEquals( $databaseRecord['vendor_poi_id'], $recordInfo['record']['vendor_poi_id'], 'Vendor reference should not change' );
        $this->assertFalse( $this->sfAction->getUser()->getFlash( 'error' ), 'Should not show user flash.' );

        // Test successful deserialize and merge.
        $this->assertEquals( $feedRecord['poi_name'],   $recordInfo['record']['poi_name'] );
        $this->assertEquals( $feedRecord['house_no'],   $recordInfo['record']['house_no'] );
        $this->assertEquals( $feedRecord['street'],     $recordInfo['record']['street'] );
        $this->assertEquals( $feedRecord['city'],       $recordInfo['record']['city'] );

        // Test some un-merged values.
        $this->assertEquals( $databaseRecord['additional_address_details'],     $recordInfo['record']['additional_address_details'] );
        $this->assertEquals( $databaseRecord['zips'],                           $recordInfo['record']['zips'] );
        $this->assertEquals( $databaseRecord['district'],                       $recordInfo['record']['district'] );
        $this->assertEquals( $databaseRecord['phone'],                          $recordInfo['record']['phone'] );

        // Test previous values
        $this->assertEquals( $databaseRecord['poi_name'],                       $recordInfo['previousValues']['poi_name'] );
        $this->assertEquals( $databaseRecord['house_no'],                       $recordInfo['previousValues']['house_no'] );
        $this->assertEquals( $databaseRecord['street'],                         $recordInfo['previousValues']['street'] );
        $this->assertEquals( $databaseRecord['city'],                           $recordInfo['previousValues']['city'] );
        $this->assertEquals( $databaseRecord['additional_address_details'],     $recordInfo['previousValues']['additional_address_details'] );
        $this->assertEquals( $databaseRecord['zips'],                           $recordInfo['previousValues']['zips'] );
        $this->assertEquals( $databaseRecord['district'],                       $recordInfo['previousValues']['district'] );
        $this->assertEquals( $databaseRecord['phone'],                          $recordInfo['previousValues']['phone'] );
    }

    public function testNewRecord()
    {
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $feedRecord['poi_name']     = 'Timeout Magazine';
        $feedRecord['house_no']     = '251';
        $feedRecord['street']       = 'Tottenham Court Road';
        $feedRecord['city']         = 'London';

        $logImportError = $this->_addImportError( $feedRecord, 'Generic Error Message' );
        $this->sfRequest->setParam( 'import_error_id', $logImportError['id'] );
        $recordInfo = LogImportErrorHelper::getMergedObject( $this->sfAction, $this->sfRequest );

        // Test class & vendor do not change, and that not error was presented to the user.
        $this->assertEquals( get_class( $feedRecord ) , get_class( $recordInfo['record'] ), 'Class should be same as feed record.' );
        $this->assertEquals( $feedRecord['vendor_id'], $recordInfo['record']['vendor_id'], 'Vendor should not change' );
        $this->assertEquals( $feedRecord['vendor_poi_id'], $recordInfo['record']['vendor_poi_id'], 'Vendor reference should not change' );
        $this->assertEquals( LogImportErrorHelper::MSG_NO_MATCHING_DB_RECORD, $this->sfAction->getUser()->getFlash( 'notice' ) );

        // Test successful deserialize and merge.
        $this->assertEquals( $feedRecord['poi_name'],   $recordInfo['record']['poi_name'] );
        $this->assertEquals( $feedRecord['house_no'],   $recordInfo['record']['house_no'] );
        $this->assertEquals( $feedRecord['street'],     $recordInfo['record']['street'] );
        $this->assertEquals( $feedRecord['city'],       $recordInfo['record']['city'] );

        // Test if we have a new record the previous values array should be empty
        $this->assertEquals( 0, count( $recordInfo['previousValues'] ) );
    }

    public function testGetErrorObjectSuccess()
    {
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $unknownVendor = Doctrine::getTable( "Vendor" )->findOneByCity( "unknown" );
        $feedRecord['vendor_id'] = $unknownVendor['id'];

        $logImportError = $this->_addImportError( $feedRecord, 'Poi With A Different Vendor ID' );

        $errorObject = LogImportErrorHelper::getErrorObject( $logImportError['id'] );
        $this->assertTrue( $errorObject instanceof Poi, 'failed to successfully get error object' );
    }

    public function testGetErrorObjectFailure()
    {
        $feedRecord = ProjectN_Test_Unit_Factory::get( 'Poi' );

        $unknownVendor = Doctrine::getTable( "Vendor" )->findOneByCity( "unknown" );
        $feedRecord['vendor_id'] = $unknownVendor['id'];

        $logImportError = $this->_addImportError( $feedRecord, 'Poi With A Different Vendor ID' );

        $errorObject = LogImportErrorHelper::getErrorObject( 123 );
        $this->assertFalse( $errorObject, 'returned error object for invalid error' );

        $errorObject = LogImportErrorHelper::getErrorObject( 'abc' );
        $this->assertFalse( $errorObject, 'returned error object for invalid error' );
    }
}

class MockSfAction
{
    public static $user;

    public static function getUser()
    {
        if (!isset(self::$user))
        {
            self::$user = new MockSfUser;
        }
        return self::$user;
    }
}

class MockSfUser
{
    private $flash = array();

    public function setFlash( $type, $message )
    {
        $this->flash[ $type ] = $message;
    }

    public function getFlash( $type )
    {
        return array_key_exists( $type, $this->flash ) ? $this->flash[ $type ] : false;
    }

    public function clearFlash()
    {
        $this->flash = array();
    }
}

class MockSfRequest
{
    private $params = array();

    public function getGetParameter( $name ){ return $this->getParam( $name ); }
    public function getPostParameter( $name ){ return $this->getParam( $name ); }

    public function getParam( $name )
    {
        return array_key_exists( $name, $this->params ) ? $this->params[ $name ] : false;
    }

    public function setParam( $name, $value )
    {
        return $this->params[ $name ] = $value;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams( $params )
    {
        $this->params = $params;
    }
}

class MockException extends Exception {}