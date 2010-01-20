<?php

/**
 * BaseEvent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * @property string $vendor_category
 * @property string $short_description
 * @property string $description
 * @property string $booking_url
 * @property string $url
 * @property string $price
 * @property float $rating
 * @property integer $event_category_id
 * @property integer $poi_id
 * @property EventCategory $EventCategory
 * @property Poi $Poi
 * @property Doctrine_Collection $EventOccurence
 * @property Doctrine_Collection $EventMedia
 * @property Doctrine_Collection $EventProperty
 * 
 * @method string              getName()              Returns the current record's "name" value
 * @method string              getVendorCategory()    Returns the current record's "vendor_category" value
 * @method string              getShortDescription()  Returns the current record's "short_description" value
 * @method string              getDescription()       Returns the current record's "description" value
 * @method string              getBookingUrl()        Returns the current record's "booking_url" value
 * @method string              getUrl()               Returns the current record's "url" value
 * @method string              getPrice()             Returns the current record's "price" value
 * @method float               getRating()            Returns the current record's "rating" value
 * @method integer             getEventCategoryId()   Returns the current record's "event_category_id" value
 * @method integer             getPoiId()             Returns the current record's "poi_id" value
 * @method EventCategory       getEventCategory()     Returns the current record's "EventCategory" value
 * @method Poi                 getPoi()               Returns the current record's "Poi" value
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
 * @method Event               setPoiId()             Sets the current record's "poi_id" value
 * @method Event               setEventCategory()     Sets the current record's "EventCategory" value
 * @method Event               setPoi()               Sets the current record's "Poi" value
 * @method Event               setEventOccurence()    Sets the current record's "EventOccurence" collection
 * @method Event               setEventMedia()        Sets the current record's "EventMedia" collection
 * @method Event               setEventProperty()     Sets the current record's "EventProperty" collection
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
        $this->hasColumn('vendor_category', 'string', 256, array(
             'type' => 'string',
             'notnull' => false,
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
        $this->hasColumn('event_category_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));
        $this->hasColumn('poi_id', 'integer', null, array(
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
        $this->hasOne('EventCategory', array(
             'local' => 'event_category_id',
             'foreign' => 'id'));

        $this->hasOne('Poi', array(
             'local' => 'poi_id',
             'foreign' => 'id'));

        $this->hasMany('EventOccurence', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $this->hasMany('EventMedia', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $this->hasMany('EventProperty', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $i18n0 = new Doctrine_Template_I18n(array(
             'fields' => 
             array(
              0 => 'name',
              1 => 'vendor_category',
              2 => 'short_description',
              3 => 'description',
              4 => 'booking_url',
             ),
             ));
        $this->actAs($timestampable0);
        $this->actAs($i18n0);
    }
}