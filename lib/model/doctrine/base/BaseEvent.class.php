<?php

/**
 * BaseEvent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * @property string $short_description
 * @property string $description
 * @property string $booking_url
 * @property string $url
 * @property string $price
 * @property float $rating
<<<<<<< HEAD:lib/model/doctrine/base/BaseEvent.class.php
 * @property integer $vendor_id
 * @property Vendor $Vendor
 * @property Doctrine_Collection $EventCategories
 * @property Doctrine_Collection $VendorEventCategories
=======
 * @property integer $event_category_id
 * @property integer $vendor_id
 * @property EventCategory $EventCategory
 * @property Vendor $Vendor
>>>>>>> 727941d371a3d6774a32c72dbba8fa6963178c1b:lib/model/doctrine/base/BaseEvent.class.php
 * @property Doctrine_Collection $EventOccurence
 * @property Doctrine_Collection $VendorEventCategory
 * @property Doctrine_Collection $EventCategory
 * @property Doctrine_Collection $EventMedia
 * @property Doctrine_Collection $EventProperty
 * 
<<<<<<< HEAD:lib/model/doctrine/base/BaseEvent.class.php
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
 * @method Doctrine_Collection getEventOccurence()        Returns the current record's "EventOccurence" collection
 * @method Doctrine_Collection getVendorEventCategory()   Returns the current record's "VendorEventCategory" collection
 * @method Doctrine_Collection getEventCategory()         Returns the current record's "EventCategory" collection
 * @method Doctrine_Collection getEventMedia()            Returns the current record's "EventMedia" collection
 * @method Doctrine_Collection getEventProperty()         Returns the current record's "EventProperty" collection
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
 * @method Event               setEventOccurence()        Sets the current record's "EventOccurence" collection
 * @method Event               setVendorEventCategory()   Sets the current record's "VendorEventCategory" collection
 * @method Event               setEventCategory()         Sets the current record's "EventCategory" collection
 * @method Event               setEventMedia()            Sets the current record's "EventMedia" collection
 * @method Event               setEventProperty()         Sets the current record's "EventProperty" collection
=======
 * @method string              getName()              Returns the current record's "name" value
 * @method string              getVendorCategory()    Returns the current record's "vendor_category" value
 * @method string              getShortDescription()  Returns the current record's "short_description" value
 * @method string              getDescription()       Returns the current record's "description" value
 * @method string              getBookingUrl()        Returns the current record's "booking_url" value
 * @method string              getUrl()               Returns the current record's "url" value
 * @method string              getPrice()             Returns the current record's "price" value
 * @method float               getRating()            Returns the current record's "rating" value
 * @method integer             getEventCategoryId()   Returns the current record's "event_category_id" value
 * @method integer             getVendorId()          Returns the current record's "vendor_id" value
 * @method EventCategory       getEventCategory()     Returns the current record's "EventCategory" value
 * @method Vendor              getVendor()            Returns the current record's "Vendor" value
 * @method Doctrine_Collection getEventOccurence()    Returns the current record's "EventOccurence" collection
 * @method Doctrine_Collection getEventMedia()        Returns the current record's "EventMedia" collection
 * @method Doctrine_Collection getEventProperty()     Returns the current record's "EventProperty" collection
 * @method Event               setName()              Sets the current record's "name" value
 * @method Event               setVendorCategory()    Sets the current record's "vendor_category" value
 * @method Event               setShortDescription()  Sets the current record's "short_description" value
 * @method Event               setDescription()       Sets the current record's "description" value
 * @method Event               setBookingUrl()        Sets the current record's "booking_url" value
 * @method Event               setUrl()               Sets the current record's "url" value
 * @method Event               setPrice()             Sets the current record's "price" value
 * @method Event               setRating()            Sets the current record's "rating" value
 * @method Event               setEventCategoryId()   Sets the current record's "event_category_id" value
 * @method Event               setVendorId()          Sets the current record's "vendor_id" value
 * @method Event               setEventCategory()     Sets the current record's "EventCategory" value
 * @method Event               setVendor()            Sets the current record's "Vendor" value
 * @method Event               setEventOccurence()    Sets the current record's "EventOccurence" collection
 * @method Event               setEventMedia()        Sets the current record's "EventMedia" collection
 * @method Event               setEventProperty()     Sets the current record's "EventProperty" collection
>>>>>>> 727941d371a3d6774a32c72dbba8fa6963178c1b:lib/model/doctrine/base/BaseEvent.class.php
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
<<<<<<< HEAD:lib/model/doctrine/base/BaseEvent.class.php
=======
        $this->hasColumn('event_category_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));
>>>>>>> 727941d371a3d6774a32c72dbba8fa6963178c1b:lib/model/doctrine/base/BaseEvent.class.php
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

<<<<<<< HEAD:lib/model/doctrine/base/BaseEvent.class.php
        $this->hasMany('EventCategory as EventCategories', array(
             'refClass' => 'LinkingEventCategory',
             'local' => 'id',
             'foreign' => 'event_category_id'));

        $this->hasMany('VendorEventCategory as VendorEventCategories', array(
             'refClass' => 'LinkingVendorEventCategory',
             'local' => 'id',
             'foreign' => 'vendor_event_category_id'));
=======
        $this->hasOne('Vendor', array(
             'local' => 'vendor_id',
             'foreign' => 'id'));
>>>>>>> 727941d371a3d6774a32c72dbba8fa6963178c1b:lib/model/doctrine/base/BaseEvent.class.php

        $this->hasMany('EventOccurence', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $this->hasMany('VendorEventCategory', array(
             'refClass' => 'LinkingVendorEventCategory',
             'local' => 'event_id',
             'foreign' => 'vendor_event_category_id'));

        $this->hasMany('EventCategory', array(
             'refClass' => 'LinkingEventCategory',
             'local' => 'event_id',
             'foreign' => 'event_category_id'));

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