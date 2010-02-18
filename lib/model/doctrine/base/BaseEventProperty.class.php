<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EventProperty', 'project_n');

/**
 * BaseEventProperty
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $lookup
 * @property string $value
 * @property integer $event_id
 * @property Event $Event
 * 
 * @method string        getLookup()   Returns the current record's "lookup" value
 * @method string        getValue()    Returns the current record's "value" value
 * @method integer       getEventId()  Returns the current record's "event_id" value
 * @method Event         getEvent()    Returns the current record's "Event" value
 * @method EventProperty setLookup()   Sets the current record's "lookup" value
 * @method EventProperty setValue()    Sets the current record's "value" value
 * @method EventProperty setEventId()  Sets the current record's "event_id" value
 * @method EventProperty setEvent()    Sets the current record's "Event" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseEventProperty extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event_property');
        $this->hasColumn('lookup', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '50',
             ));
        $this->hasColumn('value', 'string', 1024, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '1024',
             ));
        $this->hasColumn('event_id', 'integer', null, array(
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
        $this->hasOne('Event', array(
             'local' => 'event_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}