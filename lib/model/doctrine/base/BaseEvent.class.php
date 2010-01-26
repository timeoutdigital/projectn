<?php

/**
 * BaseEvent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $vendor_event_id
 * @property string $name
 * @property string $short_description
 * @property string $description
 * @property string $booking_url
 * @property string $url
 * @property string $price
 * @property float $rating
 * @property integer $vendor_id
 * @property Vendor $Vendor
 * @property Doctrine_Collection $EventCategories
 * @property Doctrine_Collection $VendorEventCategories
 * @property Doctrine_Collection $EventOccurrence
 * @property Doctrine_Collection $EventMedia
 * @property Doctrine_Collection $EventProperty
 * 
 * @method string              getVendorEventId()         Returns the current record's "vendor_event_id" value
 * @method string              getName()                  Returns the current record's "name" value
 * @method string              getShortDescription()      Returns the current record's "short_description" value
 * @method string              getDescription()           Returns the current record's "description" value
 * @method string              getBookingUrl()            Returns the current record's "booking_url" value
 * @method string              getUrl()                   Returns the current record's "url" value
 * @method string              getPrice()                 Returns the current record's "price" value
 * @method float               getRating()                Returns the current record's "rating" value
 * @method integer             getVendorId()              Returns the current record's "vendor_id" value
 * @method Vendor              getVendor()                Returns the current record's "Vendor" value
 * @method Doctrine_Collection getEventCategories()       Returns the current record's "EventCategories" collection
 * @method Doctrine_Collection getVendorEventCategories() Returns the current record's "VendorEventCategories" collection
 * @method Doctrine_Collection getEventOccurrence()       Returns the current record's "EventOccurrence" collection
 * @method Doctrine_Collection getEventMedia()            Returns the current record's "EventMedia" collection
 * @method Doctrine_Collection getEventProperty()         Returns the current record's "EventProperty" collection
 * @method Event               setVendorEventId()         Sets the current record's "vendor_event_id" value
 * @method Event               setName()                  Sets the current record's "name" value
 * @method Event               setShortDescription()      Sets the current record's "short_description" value
 * @method Event               setDescription()           Sets the current record's "description" value
 * @method Event               setBookingUrl()            Sets the current record's "booking_url" value
 * @method Event               setUrl()                   Sets the current record's "url" value
 * @method Event               setPrice()                 Sets the current record's "price" value
 * @method Event               setRating()                Sets the current record's "rating" value
 * @method Event               setVendorId()              Sets the current record's "vendor_id" value
 * @method Event               setVendor()                Sets the current record's "Vendor" value
 * @method Event               setEventCategories()       Sets the current record's "EventCategories" collection
 * @method Event               setVendorEventCategories() Sets the current record's "VendorEventCategories" collection
 * @method Event               setEventOccurrence()       Sets the current record's "EventOccurrence" collection
 * @method Event               setEventMedia()            Sets the current record's "EventMedia" collection
 * @method Event               setEventProperty()         Sets the current record's "EventProperty" collection
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseEvent extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event');
        $this->hasColumn('vendor_event_id', 'string', 10, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('name', 'string', 256, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '256',
             ));
        $this->hasColumn('short_description', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('description', 'string', 65535, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '65535',
             ));
        $this->hasColumn('booking_url', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('url', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('price', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('rating', 'float', null, array(
             'type' => 'float',
             'notnull' => false,
             ));
        $this->hasColumn('vendor_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Vendor', array(
             'local' => 'vendor_id',
             'foreign' => 'id'));

        $this->hasMany('EventCategory as EventCategories', array(
             'refClass' => 'LinkingEventCategory',
             'local' => 'event_id',
             'foreign' => 'event_category_id'));

        $this->hasMany('VendorEventCategory as VendorEventCategories', array(
             'refClass' => 'LinkingVendorEventCategory',
             'local' => 'event_id',
             'foreign' => 'vendor_event_category_id'));

        $this->hasMany('EventOccurrence', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $this->hasMany('EventMedia', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $this->hasMany('EventProperty', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}