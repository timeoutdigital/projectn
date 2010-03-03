<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Vendor', 'project_n');

/**
 * BaseVendor
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $city
 * @property string $language
 * @property string $time_zone
 * @property string $inernational_dial_code
 * @property string $airport_code
 * @property Doctrine_Collection $Poi
 * @property Doctrine_Collection $VendorPoiCategory
 * @property Doctrine_Collection $Event
 * @property Doctrine_Collection $VendorEventCategory
 * @property Doctrine_Collection $Movie
 * @property Doctrine_Collection $User
 * @property Doctrine_Collection $ImportLogger
 * @property Doctrine_Collection $ExportLogger
 * 
 * @method string              getCity()                   Returns the current record's "city" value
 * @method string              getLanguage()               Returns the current record's "language" value
 * @method string              getTimeZone()               Returns the current record's "time_zone" value
 * @method string              getInernationalDialCode()   Returns the current record's "inernational_dial_code" value
 * @method string              getAirportCode()            Returns the current record's "airport_code" value
 * @method Doctrine_Collection getPoi()                    Returns the current record's "Poi" collection
 * @method Doctrine_Collection getVendorPoiCategory()      Returns the current record's "VendorPoiCategory" collection
 * @method Doctrine_Collection getEvent()                  Returns the current record's "Event" collection
 * @method Doctrine_Collection getVendorEventCategory()    Returns the current record's "VendorEventCategory" collection
 * @method Doctrine_Collection getMovie()                  Returns the current record's "Movie" collection
 * @method Doctrine_Collection getUser()                   Returns the current record's "User" collection
 * @method Doctrine_Collection getImportLogger()           Returns the current record's "ImportLogger" collection
 * @method Doctrine_Collection getExportLogger()           Returns the current record's "ExportLogger" collection
 * @method Vendor              setCity()                   Sets the current record's "city" value
 * @method Vendor              setLanguage()               Sets the current record's "language" value
 * @method Vendor              setTimeZone()               Sets the current record's "time_zone" value
 * @method Vendor              setInernationalDialCode()   Sets the current record's "inernational_dial_code" value
 * @method Vendor              setAirportCode()            Sets the current record's "airport_code" value
 * @method Vendor              setPoi()                    Sets the current record's "Poi" collection
 * @method Vendor              setVendorPoiCategory()      Sets the current record's "VendorPoiCategory" collection
 * @method Vendor              setEvent()                  Sets the current record's "Event" collection
 * @method Vendor              setVendorEventCategory()    Sets the current record's "VendorEventCategory" collection
 * @method Vendor              setMovie()                  Sets the current record's "Movie" collection
 * @method Vendor              setUser()                   Sets the current record's "User" collection
 * @method Vendor              setImportLogger()           Sets the current record's "ImportLogger" collection
 * @method Vendor              setExportLogger()           Sets the current record's "ExportLogger" collection
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseVendor extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('vendor');
        $this->hasColumn('city', 'string', 15, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '15',
             ));
        $this->hasColumn('language', 'string', 10, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('time_zone', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '50',
             ));
        $this->hasColumn('inernational_dial_code', 'string', 5, array(
             'type' => 'string',
             'length' => '5',
             ));
        $this->hasColumn('airport_code', 'string', 3, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '3',
             ));


        $this->index('city_index', array(
             'fields' => 
             array(
              0 => 'city',
             ),
             ));
        $this->index('city_language_index', array(
             'fields' => 
             array(
              0 => 'city',
              1 => 'language',
             ),
             ));
        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Poi', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $this->hasMany('VendorPoiCategory', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $this->hasMany('Event', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $this->hasMany('VendorEventCategory', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $this->hasMany('Movie', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $this->hasMany('User', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $this->hasMany('ImportLogger', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $this->hasMany('ExportLogger', array(
             'local' => 'id',
             'foreign' => 'vendor_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}