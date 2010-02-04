<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EventOccurrence', 'project_n');

/**
 * BaseEventOccurrence
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $vendor_event_occurrence_id
 * @property string $booking_url
 * @property date $start
 * @property date $end
 * @property string $utc_offset
 * @property integer $event_id
 * @property integer $poi_id
 * @property Event $Event
 * @property Poi $Poi
 * 
 * @method string          getVendorEventOccurrenceId()    Returns the current record's "vendor_event_occurrence_id" value
 * @method string          getBookingUrl()                 Returns the current record's "booking_url" value
 * @method date            getStart()                      Returns the current record's "start" value
 * @method date            getEnd()                        Returns the current record's "end" value
 * @method string          getUtcOffset()                  Returns the current record's "utc_offset" value
 * @method integer         getEventId()                    Returns the current record's "event_id" value
 * @method integer         getPoiId()                      Returns the current record's "poi_id" value
 * @method Event           getEvent()                      Returns the current record's "Event" value
 * @method Poi             getPoi()                        Returns the current record's "Poi" value
 * @method EventOccurrence setVendorEventOccurrenceId()    Sets the current record's "vendor_event_occurrence_id" value
 * @method EventOccurrence setBookingUrl()                 Sets the current record's "booking_url" value
 * @method EventOccurrence setStart()                      Sets the current record's "start" value
 * @method EventOccurrence setEnd()                        Sets the current record's "end" value
 * @method EventOccurrence setUtcOffset()                  Sets the current record's "utc_offset" value
 * @method EventOccurrence setEventId()                    Sets the current record's "event_id" value
 * @method EventOccurrence setPoiId()                      Sets the current record's "poi_id" value
 * @method EventOccurrence setEvent()                      Sets the current record's "Event" value
 * @method EventOccurrence setPoi()                        Sets the current record's "Poi" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseEventOccurrence extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event_occurrence');
        $this->hasColumn('vendor_event_occurrence_id', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '50',
             ));
        $this->hasColumn('booking_url', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('start', 'date', null, array(
             'type' => 'date',
             'notnull' => true,
             ));
        $this->hasColumn('end', 'date', null, array(
             'type' => 'date',
             'notnull' => false,
             ));
        $this->hasColumn('utc_offset', 'string', 9, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '9',
             ));
        $this->hasColumn('event_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('poi_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Event', array(
             'local' => 'event_id',
             'foreign' => 'id'));

        $this->hasOne('Poi', array(
             'local' => 'poi_id',
             'foreign' => 'id'));
    }
}