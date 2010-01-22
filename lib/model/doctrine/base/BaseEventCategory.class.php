<?php

/**
 * BaseEventCategory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * @property Doctrine_Collection $Events
 * @property Doctrine_Collection $EventCategoryMapping
 * 
 * @method string              getName()                 Returns the current record's "name" value
 * @method Doctrine_Collection getEvents()               Returns the current record's "Events" collection
 * @method Doctrine_Collection getEventCategoryMapping() Returns the current record's "EventCategoryMapping" collection
 * @method EventCategory       setName()                 Sets the current record's "name" value
 * @method EventCategory       setEvents()               Sets the current record's "Events" collection
 * @method EventCategory       setEventCategoryMapping() Sets the current record's "EventCategoryMapping" collection
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseEventCategory extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event_category');
        $this->hasColumn('name', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '50',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Event as Events', array(
             'refClass' => 'LinkingEventCategory',
             'local' => 'event_category_id',
             'foreign' => 'event_id'));

        $this->hasMany('EventCategoryMapping', array(
             'local' => 'id',
             'foreign' => 'map_to_id'));
    }
}