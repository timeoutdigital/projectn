<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Poi', 'project_n');

/**
 * BasePoi
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $vendor_poi_id
 * @property datetime $review_date
 * @property string $local_language
 * @property string $poi_name
 * @property string $house_no
 * @property string $street
 * @property string $city
 * @property string $district
 * @property string $country
 * @property string $additional_address_details
 * @property string $zips
 * @property decimal $longitude
 * @property decimal $latitude
 * @property string $email
 * @property string $url
 * @property string $phone
 * @property string $phone2
 * @property string $fax
 * @property string $vendor_category
 * @property string $keywords
 * @property string $short_description
 * @property string $description
 * @property string $public_transport_links
 * @property string $price_information
 * @property string $openingtimes
 * @property integer $star_rating
 * @property integer $rating
 * @property string $provider
 * @property integer $vendor_id
 * @property Vendor $Vendor
 * @property Doctrine_Collection $PoiCategories
 * @property Doctrine_Collection $VendorPoiCategories
 * @property Doctrine_Collection $PoiChangesLog
 * @property Doctrine_Collection $PoiMedia
 * @property Doctrine_Collection $PoiProperty
 * @property Doctrine_Collection $Poi
 * @property Doctrine_Collection $EventOccurrence
 * @property Movie $Movie
 * 
 * @method string              getVendorPoiId()                Returns the current record's "vendor_poi_id" value
 * @method datetime            getReviewDate()                 Returns the current record's "review_date" value
 * @method string              getLocalLanguage()              Returns the current record's "local_language" value
 * @method string              getPoiName()                    Returns the current record's "poi_name" value
 * @method string              getHouseNo()                    Returns the current record's "house_no" value
 * @method string              getStreet()                     Returns the current record's "street" value
 * @method string              getCity()                       Returns the current record's "city" value
 * @method string              getDistrict()                   Returns the current record's "district" value
 * @method string              getCountry()                    Returns the current record's "country" value
 * @method string              getAdditionalAddressDetails()   Returns the current record's "additional_address_details" value
 * @method string              getZips()                       Returns the current record's "zips" value
 * @method decimal             getLongitude()                  Returns the current record's "longitude" value
 * @method decimal             getLatitude()                   Returns the current record's "latitude" value
 * @method string              getEmail()                      Returns the current record's "email" value
 * @method string              getUrl()                        Returns the current record's "url" value
 * @method string              getPhone()                      Returns the current record's "phone" value
 * @method string              getPhone2()                     Returns the current record's "phone2" value
 * @method string              getFax()                        Returns the current record's "fax" value
 * @method string              getVendorCategory()             Returns the current record's "vendor_category" value
 * @method string              getKeywords()                   Returns the current record's "keywords" value
 * @method string              getShortDescription()           Returns the current record's "short_description" value
 * @method string              getDescription()                Returns the current record's "description" value
 * @method string              getPublicTransportLinks()       Returns the current record's "public_transport_links" value
 * @method string              getPriceInformation()           Returns the current record's "price_information" value
 * @method string              getOpeningtimes()               Returns the current record's "openingtimes" value
 * @method integer             getStarRating()                 Returns the current record's "star_rating" value
 * @method integer             getRating()                     Returns the current record's "rating" value
 * @method string              getProvider()                   Returns the current record's "provider" value
 * @method integer             getVendorId()                   Returns the current record's "vendor_id" value
 * @method Vendor              getVendor()                     Returns the current record's "Vendor" value
 * @method Doctrine_Collection getPoiCategories()              Returns the current record's "PoiCategories" collection
 * @method Doctrine_Collection getVendorPoiCategories()        Returns the current record's "VendorPoiCategories" collection
 * @method Doctrine_Collection getPoiChangesLog()              Returns the current record's "PoiChangesLog" collection
 * @method Doctrine_Collection getPoiMedia()                   Returns the current record's "PoiMedia" collection
 * @method Doctrine_Collection getPoiProperty()                Returns the current record's "PoiProperty" collection
 * @method Doctrine_Collection getPoi()                        Returns the current record's "Poi" collection
 * @method Doctrine_Collection getEventOccurrence()            Returns the current record's "EventOccurrence" collection
 * @method Movie               getMovie()                      Returns the current record's "Movie" value
 * @method Poi                 setVendorPoiId()                Sets the current record's "vendor_poi_id" value
 * @method Poi                 setReviewDate()                 Sets the current record's "review_date" value
 * @method Poi                 setLocalLanguage()              Sets the current record's "local_language" value
 * @method Poi                 setPoiName()                    Sets the current record's "poi_name" value
 * @method Poi                 setHouseNo()                    Sets the current record's "house_no" value
 * @method Poi                 setStreet()                     Sets the current record's "street" value
 * @method Poi                 setCity()                       Sets the current record's "city" value
 * @method Poi                 setDistrict()                   Sets the current record's "district" value
 * @method Poi                 setCountry()                    Sets the current record's "country" value
 * @method Poi                 setAdditionalAddressDetails()   Sets the current record's "additional_address_details" value
 * @method Poi                 setZips()                       Sets the current record's "zips" value
 * @method Poi                 setLongitude()                  Sets the current record's "longitude" value
 * @method Poi                 setLatitude()                   Sets the current record's "latitude" value
 * @method Poi                 setEmail()                      Sets the current record's "email" value
 * @method Poi                 setUrl()                        Sets the current record's "url" value
 * @method Poi                 setPhone()                      Sets the current record's "phone" value
 * @method Poi                 setPhone2()                     Sets the current record's "phone2" value
 * @method Poi                 setFax()                        Sets the current record's "fax" value
 * @method Poi                 setVendorCategory()             Sets the current record's "vendor_category" value
 * @method Poi                 setKeywords()                   Sets the current record's "keywords" value
 * @method Poi                 setShortDescription()           Sets the current record's "short_description" value
 * @method Poi                 setDescription()                Sets the current record's "description" value
 * @method Poi                 setPublicTransportLinks()       Sets the current record's "public_transport_links" value
 * @method Poi                 setPriceInformation()           Sets the current record's "price_information" value
 * @method Poi                 setOpeningtimes()               Sets the current record's "openingtimes" value
 * @method Poi                 setStarRating()                 Sets the current record's "star_rating" value
 * @method Poi                 setRating()                     Sets the current record's "rating" value
 * @method Poi                 setProvider()                   Sets the current record's "provider" value
 * @method Poi                 setVendorId()                   Sets the current record's "vendor_id" value
 * @method Poi                 setVendor()                     Sets the current record's "Vendor" value
 * @method Poi                 setPoiCategories()              Sets the current record's "PoiCategories" collection
 * @method Poi                 setVendorPoiCategories()        Sets the current record's "VendorPoiCategories" collection
 * @method Poi                 setPoiChangesLog()              Sets the current record's "PoiChangesLog" collection
 * @method Poi                 setPoiMedia()                   Sets the current record's "PoiMedia" collection
 * @method Poi                 setPoiProperty()                Sets the current record's "PoiProperty" collection
 * @method Poi                 setPoi()                        Sets the current record's "Poi" collection
 * @method Poi                 setEventOccurrence()            Sets the current record's "EventOccurrence" collection
 * @method Poi                 setMovie()                      Sets the current record's "Movie" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BasePoi extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('poi');
        $this->hasColumn('vendor_poi_id', 'string', 10, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('review_date', 'datetime', null, array(
             'type' => 'datetime',
             'notnull' => false,
             ));
        $this->hasColumn('local_language', 'string', 10, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '10',
             ));
        $this->hasColumn('poi_name', 'string', 80, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '80',
             ));
        $this->hasColumn('house_no', 'string', 16, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '16',
             ));
        $this->hasColumn('street', 'string', 128, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '128',
             ));
        $this->hasColumn('city', 'string', 32, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '32',
             ));
        $this->hasColumn('district', 'string', 128, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '128',
             ));
        $this->hasColumn('country', 'string', 3, array(
             'type' => 'string',
             'notnull' => true,
             'regexp' => '/^[a-zA-Z]{3}$/',
             'length' => '3',
             ));
        $this->hasColumn('additional_address_details', 'string', 128, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '128',
             ));
        $this->hasColumn('zips', 'string', 16, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '16',
             ));
        $this->hasColumn('longitude', 'decimal', 18, array(
             'type' => 'decimal',
             'scale' => 15,
             'notnull' => true,
             'length' => '18',
             ));
        $this->hasColumn('latitude', 'decimal', 18, array(
             'type' => 'decimal',
             'scale' => 15,
             'notnull' => true,
             'length' => '18',
             ));
        $this->hasColumn('email', 'string', 12, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '12',
             ));
        $this->hasColumn('url', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('phone', 'string', 32, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '32',
             ));
        $this->hasColumn('phone2', 'string', 32, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '32',
             ));
        $this->hasColumn('fax', 'string', 32, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '32',
             ));
        $this->hasColumn('vendor_category', 'string', 128, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '128',
             ));
        $this->hasColumn('keywords', 'string', 512, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '512',
             ));
        $this->hasColumn('short_description', 'string', 2048, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '2048',
             ));
        $this->hasColumn('description', 'string', 65535, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '65535',
             ));
        $this->hasColumn('public_transport_links', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('price_information', 'string', 512, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '512',
             ));
        $this->hasColumn('openingtimes', 'string', 512, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '512',
             ));
        $this->hasColumn('star_rating', 'integer', 1, array(
             'type' => 'integer',
             'notnull' => false,
             'length' => '1',
             ));
        $this->hasColumn('rating', 'integer', 1, array(
             'type' => 'integer',
             'notnull' => false,
             'length' => '1',
             ));
        $this->hasColumn('provider', 'string', 512, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '512',
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

        $this->hasMany('PoiCategory as PoiCategories', array(
             'refClass' => 'LinkingPoiCategory',
             'local' => 'poi_id',
             'foreign' => 'poi_category_id'));

        $this->hasMany('VendorPoiCategory as VendorPoiCategories', array(
             'refClass' => 'LinkingVendorPoiCategory',
             'local' => 'poi_id',
             'foreign' => 'vendor_poi_category_id'));

        $this->hasMany('PoiChangesLog', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasMany('PoiMedia', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasMany('PoiProperty', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasMany('UserContent as Poi', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasMany('EventOccurrence', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasOne('Movie', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}