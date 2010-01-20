<?php

/**
 * BasePoi
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property datetime $review_date
 * @property integer $vendor_poi_id
 * @property string $local_language
 * @property string $poi_name
 * @property string $house_no
 * @property string $street
 * @property string $city
 * @property string $district
 * @property string $country
 * @property string $additional_address_details
 * @property string $zips
 * @property string $country_code
 * @property string $extension
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
 * @property integer $poi_category_id
 * @property integer $vendor_id
 * @property Vendor $Vendor
 * @property PoiCategory $PoiCategory
 * @property Doctrine_Collection $PoiMedia
 * @property Doctrine_Collection $PoiProperty
 * @property Doctrine_Collection $Poi
 * @property Doctrine_Collection $EventOccurence
 * @property Movie $Movie
 * 
 * @method datetime            getReviewDate()                 Returns the current record's "review_date" value
 * @method integer             getVendorPoiId()                Returns the current record's "vendor_poi_id" value
 * @method string              getLocalLanguage()              Returns the current record's "local_language" value
 * @method string              getPoiName()                    Returns the current record's "poi_name" value
 * @method string              getHouseNo()                    Returns the current record's "house_no" value
 * @method string              getStreet()                     Returns the current record's "street" value
 * @method string              getCity()                       Returns the current record's "city" value
 * @method string              getDistrict()                   Returns the current record's "district" value
 * @method string              getCountry()                    Returns the current record's "country" value
 * @method string              getAdditionalAddressDetails()   Returns the current record's "additional_address_details" value
 * @method string              getZips()                       Returns the current record's "zips" value
 * @method string              getCountryCode()                Returns the current record's "country_code" value
 * @method string              getExtension()                  Returns the current record's "extension" value
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
 * @method integer             getPoiCategoryId()              Returns the current record's "poi_category_id" value
 * @method integer             getVendorId()                   Returns the current record's "vendor_id" value
 * @method Vendor              getVendor()                     Returns the current record's "Vendor" value
 * @method PoiCategory         getPoiCategory()                Returns the current record's "PoiCategory" value
 * @method Doctrine_Collection getPoiMedia()                   Returns the current record's "PoiMedia" collection
 * @method Doctrine_Collection getPoiProperty()                Returns the current record's "PoiProperty" collection
 * @method Doctrine_Collection getPoi()                        Returns the current record's "Poi" collection
 * @method Doctrine_Collection getEventOccurence()             Returns the current record's "EventOccurence" collection
 * @method Movie               getMovie()                      Returns the current record's "Movie" value
 * @method Poi                 setReviewDate()                 Sets the current record's "review_date" value
 * @method Poi                 setVendorPoiId()                Sets the current record's "vendor_poi_id" value
 * @method Poi                 setLocalLanguage()              Sets the current record's "local_language" value
 * @method Poi                 setPoiName()                    Sets the current record's "poi_name" value
 * @method Poi                 setHouseNo()                    Sets the current record's "house_no" value
 * @method Poi                 setStreet()                     Sets the current record's "street" value
 * @method Poi                 setCity()                       Sets the current record's "city" value
 * @method Poi                 setDistrict()                   Sets the current record's "district" value
 * @method Poi                 setCountry()                    Sets the current record's "country" value
 * @method Poi                 setAdditionalAddressDetails()   Sets the current record's "additional_address_details" value
 * @method Poi                 setZips()                       Sets the current record's "zips" value
 * @method Poi                 setCountryCode()                Sets the current record's "country_code" value
 * @method Poi                 setExtension()                  Sets the current record's "extension" value
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
 * @method Poi                 setPoiCategoryId()              Sets the current record's "poi_category_id" value
 * @method Poi                 setVendorId()                   Sets the current record's "vendor_id" value
 * @method Poi                 setVendor()                     Sets the current record's "Vendor" value
 * @method Poi                 setPoiCategory()                Sets the current record's "PoiCategory" value
 * @method Poi                 setPoiMedia()                   Sets the current record's "PoiMedia" collection
 * @method Poi                 setPoiProperty()                Sets the current record's "PoiProperty" collection
 * @method Poi                 setPoi()                        Sets the current record's "Poi" collection
 * @method Poi                 setEventOccurence()             Sets the current record's "EventOccurence" collection
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
        $this->hasColumn('review_date', 'datetime', null, array(
             'type' => 'datetime',
             'notnull' => false,
             ));
        $this->hasColumn('vendor_poi_id', 'integer', null, array(
             'type' => 'integer',
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
             'regexp' => '/^[a-zA-Z]$/',
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
        $this->hasColumn('country_code', 'string', 2, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '2',
             ));
        $this->hasColumn('extension', 'string', 128, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '128',
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
        $this->hasColumn('poi_category_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
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

        $this->hasOne('PoiCategory', array(
             'local' => 'poi_category_id',
             'foreign' => 'id'));

        $this->hasMany('PoiMedia', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasMany('PoiProperty', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasMany('UserContent as Poi', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasMany('EventOccurence', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $this->hasOne('Movie', array(
             'local' => 'id',
             'foreign' => 'poi_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $i18n0 = new Doctrine_Template_I18n(array(
             'fields' => 
             array(
              0 => 'name',
              1 => 'alternative_name',
              2 => 'street',
              3 => 'house_no',
              4 => 'zips',
              5 => 'city',
              6 => 'district',
              7 => 'country',
              8 => 'extension',
             ),
             ));
        $this->actAs($timestampable0);
        $this->actAs($i18n0);
    }
}