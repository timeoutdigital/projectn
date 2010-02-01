<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('SLLEvent', 'searchlight_london');

/**
 * BaseSLLEvent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $master_category_id
 * @property integer $default_venue_id
 * @property string $title
 * @property string $title_sort
 * @property integer $free
 * @property integer $image_id
 * @property integer $status
 * @property integer $recommended
 * @property integer $distinct_occurrences
 * @property string $travel
 * @property string $venue_prefix
 * @property integer $search_priority
 * @property string $source
 * @property integer $source_id
 * @property integer $source_event_id
 * @property date $date_start
 * @property integer $type
 * @property string $title_url
 * @property integer $suitable_for_kids
 * @property string $seo_synopsis
 * @property string $annotation
 * @property string $phone
 * @property string $url
 * @property string $price
 * @property decimal $price_cheapest
 * @property integer $discount
 * @property string $keywords
 * @property string $tags
 * @property date $date_end
 * @property string $opening_times
 * @property integer $booking_ahead
 * @property integer $rescheduled
 * @property integer $extra
 * @property integer $cancelled
 * @property string $flickr_tag
 * @property string $advanced_text
 * @property date $date_created
 * @property date $date_modified
 * @property string $source_field
 * @property SLLCategory $SLLCategory
 * @property Doctrine_Collection $SLLOccurrence
 * @property Doctrine_Collection $SLLEventCategoryMapping
 * 
 * @method integer             getId()                      Returns the current record's "id" value
 * @method integer             getMasterCategoryId()        Returns the current record's "master_category_id" value
 * @method integer             getDefaultVenueId()          Returns the current record's "default_venue_id" value
 * @method string              getTitle()                   Returns the current record's "title" value
 * @method string              getTitleSort()               Returns the current record's "title_sort" value
 * @method integer             getFree()                    Returns the current record's "free" value
 * @method integer             getImageId()                 Returns the current record's "image_id" value
 * @method integer             getStatus()                  Returns the current record's "status" value
 * @method integer             getRecommended()             Returns the current record's "recommended" value
 * @method integer             getDistinctOccurrences()     Returns the current record's "distinct_occurrences" value
 * @method string              getTravel()                  Returns the current record's "travel" value
 * @method string              getVenuePrefix()             Returns the current record's "venue_prefix" value
 * @method integer             getSearchPriority()          Returns the current record's "search_priority" value
 * @method string              getSource()                  Returns the current record's "source" value
 * @method integer             getSourceId()                Returns the current record's "source_id" value
 * @method integer             getSourceEventId()           Returns the current record's "source_event_id" value
 * @method date                getDateStart()               Returns the current record's "date_start" value
 * @method integer             getType()                    Returns the current record's "type" value
 * @method string              getTitleUrl()                Returns the current record's "title_url" value
 * @method integer             getSuitableForKids()         Returns the current record's "suitable_for_kids" value
 * @method string              getSeoSynopsis()             Returns the current record's "seo_synopsis" value
 * @method string              getAnnotation()              Returns the current record's "annotation" value
 * @method string              getPhone()                   Returns the current record's "phone" value
 * @method string              getUrl()                     Returns the current record's "url" value
 * @method string              getPrice()                   Returns the current record's "price" value
 * @method decimal             getPriceCheapest()           Returns the current record's "price_cheapest" value
 * @method integer             getDiscount()                Returns the current record's "discount" value
 * @method string              getKeywords()                Returns the current record's "keywords" value
 * @method string              getTags()                    Returns the current record's "tags" value
 * @method date                getDateEnd()                 Returns the current record's "date_end" value
 * @method string              getOpeningTimes()            Returns the current record's "opening_times" value
 * @method integer             getBookingAhead()            Returns the current record's "booking_ahead" value
 * @method integer             getRescheduled()             Returns the current record's "rescheduled" value
 * @method integer             getExtra()                   Returns the current record's "extra" value
 * @method integer             getCancelled()               Returns the current record's "cancelled" value
 * @method string              getFlickrTag()               Returns the current record's "flickr_tag" value
 * @method string              getAdvancedText()            Returns the current record's "advanced_text" value
 * @method date                getDateCreated()             Returns the current record's "date_created" value
 * @method date                getDateModified()            Returns the current record's "date_modified" value
 * @method string              getSourceField()             Returns the current record's "source_field" value
 * @method SLLCategory         getSLLCategory()             Returns the current record's "SLLCategory" value
 * @method Doctrine_Collection getSLLOccurrence()           Returns the current record's "SLLOccurrence" collection
 * @method Doctrine_Collection getSLLEventCategoryMapping() Returns the current record's "SLLEventCategoryMapping" collection
 * @method SLLEvent            setId()                      Sets the current record's "id" value
 * @method SLLEvent            setMasterCategoryId()        Sets the current record's "master_category_id" value
 * @method SLLEvent            setDefaultVenueId()          Sets the current record's "default_venue_id" value
 * @method SLLEvent            setTitle()                   Sets the current record's "title" value
 * @method SLLEvent            setTitleSort()               Sets the current record's "title_sort" value
 * @method SLLEvent            setFree()                    Sets the current record's "free" value
 * @method SLLEvent            setImageId()                 Sets the current record's "image_id" value
 * @method SLLEvent            setStatus()                  Sets the current record's "status" value
 * @method SLLEvent            setRecommended()             Sets the current record's "recommended" value
 * @method SLLEvent            setDistinctOccurrences()     Sets the current record's "distinct_occurrences" value
 * @method SLLEvent            setTravel()                  Sets the current record's "travel" value
 * @method SLLEvent            setVenuePrefix()             Sets the current record's "venue_prefix" value
 * @method SLLEvent            setSearchPriority()          Sets the current record's "search_priority" value
 * @method SLLEvent            setSource()                  Sets the current record's "source" value
 * @method SLLEvent            setSourceId()                Sets the current record's "source_id" value
 * @method SLLEvent            setSourceEventId()           Sets the current record's "source_event_id" value
 * @method SLLEvent            setDateStart()               Sets the current record's "date_start" value
 * @method SLLEvent            setType()                    Sets the current record's "type" value
 * @method SLLEvent            setTitleUrl()                Sets the current record's "title_url" value
 * @method SLLEvent            setSuitableForKids()         Sets the current record's "suitable_for_kids" value
 * @method SLLEvent            setSeoSynopsis()             Sets the current record's "seo_synopsis" value
 * @method SLLEvent            setAnnotation()              Sets the current record's "annotation" value
 * @method SLLEvent            setPhone()                   Sets the current record's "phone" value
 * @method SLLEvent            setUrl()                     Sets the current record's "url" value
 * @method SLLEvent            setPrice()                   Sets the current record's "price" value
 * @method SLLEvent            setPriceCheapest()           Sets the current record's "price_cheapest" value
 * @method SLLEvent            setDiscount()                Sets the current record's "discount" value
 * @method SLLEvent            setKeywords()                Sets the current record's "keywords" value
 * @method SLLEvent            setTags()                    Sets the current record's "tags" value
 * @method SLLEvent            setDateEnd()                 Sets the current record's "date_end" value
 * @method SLLEvent            setOpeningTimes()            Sets the current record's "opening_times" value
 * @method SLLEvent            setBookingAhead()            Sets the current record's "booking_ahead" value
 * @method SLLEvent            setRescheduled()             Sets the current record's "rescheduled" value
 * @method SLLEvent            setExtra()                   Sets the current record's "extra" value
 * @method SLLEvent            setCancelled()               Sets the current record's "cancelled" value
 * @method SLLEvent            setFlickrTag()               Sets the current record's "flickr_tag" value
 * @method SLLEvent            setAdvancedText()            Sets the current record's "advanced_text" value
 * @method SLLEvent            setDateCreated()             Sets the current record's "date_created" value
 * @method SLLEvent            setDateModified()            Sets the current record's "date_modified" value
 * @method SLLEvent            setSourceField()             Sets the current record's "source_field" value
 * @method SLLEvent            setSLLCategory()             Sets the current record's "SLLCategory" value
 * @method SLLEvent            setSLLOccurrence()           Sets the current record's "SLLOccurrence" collection
 * @method SLLEvent            setSLLEventCategoryMapping() Sets the current record's "SLLEventCategoryMapping" collection
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseSLLEvent extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => 1,
             'primary' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('master_category_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => 1,
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('default_venue_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => 1,
             'length' => '4',
             ));
        $this->hasColumn('title', 'string', 2147483647, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '2147483647',
             ));
        $this->hasColumn('title_sort', 'string', 2147483647, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '2147483647',
             ));
        $this->hasColumn('free', 'integer', 1, array(
             'type' => 'integer',
             'default' => '0',
             'length' => '1',
             ));
        $this->hasColumn('image_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => 1,
             'length' => '4',
             ));
        $this->hasColumn('status', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => 1,
             'length' => '4',
             ));
        $this->hasColumn('recommended', 'integer', 1, array(
             'type' => 'integer',
             'unsigned' => 1,
             'default' => '0',
             'notnull' => true,
             'length' => '1',
             ));
        $this->hasColumn('distinct_occurrences', 'integer', 1, array(
             'type' => 'integer',
             'default' => '0',
             'notnull' => true,
             'length' => '1',
             ));
        $this->hasColumn('travel', 'string', 2147483647, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '2147483647',
             ));
        $this->hasColumn('venue_prefix', 'string', 2147483647, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '2147483647',
             ));
        $this->hasColumn('search_priority', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => 1,
             'length' => '4',
             ));
        $this->hasColumn('source', 'string', 15, array(
             'type' => 'string',
             'default' => '0',
             'length' => '15',
             ));
        $this->hasColumn('source_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => 1,
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('source_event_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('date_start', 'date', null, array(
             'type' => 'date',
             'notnull' => true,
             ));
        $this->hasColumn('type', 'integer', 1, array(
             'type' => 'integer',
             'unsigned' => 1,
             'default' => '0',
             'notnull' => true,
             'length' => '1',
             ));
        $this->hasColumn('title_url', 'string', 255, array(
             'type' => 'string',
             'default' => '',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('suitable_for_kids', 'integer', 1, array(
             'type' => 'integer',
             'unsigned' => 1,
             'length' => '1',
             ));
        $this->hasColumn('seo_synopsis', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('annotation', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('phone', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('url', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('price', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('price_cheapest', 'decimal', 10, array(
             'type' => 'decimal',
             'length' => '10',
             ));
        $this->hasColumn('discount', 'integer', 1, array(
             'type' => 'integer',
             'length' => '1',
             ));
        $this->hasColumn('keywords', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('tags', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('date_end', 'date', null, array(
             'type' => 'date',
             ));
        $this->hasColumn('opening_times', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('booking_ahead', 'integer', 1, array(
             'type' => 'integer',
             'length' => '1',
             ));
        $this->hasColumn('rescheduled', 'integer', 1, array(
             'type' => 'integer',
             'length' => '1',
             ));
        $this->hasColumn('extra', 'integer', 1, array(
             'type' => 'integer',
             'length' => '1',
             ));
        $this->hasColumn('cancelled', 'integer', 1, array(
             'type' => 'integer',
             'length' => '1',
             ));
        $this->hasColumn('flickr_tag', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('advanced_text', 'string', 2147483647, array(
             'type' => 'string',
             'length' => '2147483647',
             ));
        $this->hasColumn('date_created', 'date', null, array(
             'type' => 'date',
             ));
        $this->hasColumn('date_modified', 'date', null, array(
             'type' => 'date',
             ));
        $this->hasColumn('source_field', 'string', 25, array(
             'type' => 'string',
             'length' => '25',
             ));

        $this->option('charset', 'utf8');
        $this->option('collate', 'utf8_general_ci');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('SLLCategory', array(
             'local' => 'master_category_id',
             'foreign' => 'id'));

        $this->hasMany('SLLOccurrence', array(
             'local' => 'id',
             'foreign' => 'event_id'));

        $this->hasMany('SLLEventCategoryMapping', array(
             'local' => 'id',
             'foreign' => 'event_id'));
    }
}