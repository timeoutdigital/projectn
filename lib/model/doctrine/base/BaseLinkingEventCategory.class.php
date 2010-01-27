<?php

/**
 * BaseLinkingEventCategory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $event_category_id
 * @property integer $event_id
 * 
 * @method integer              getEventCategoryId()   Returns the current record's "event_category_id" value
 * @method integer              getEventId()           Returns the current record's "event_id" value
 * @method LinkingEventCategory setEventCategoryId()   Sets the current record's "event_category_id" value
 * @method LinkingEventCategory setEventId()           Sets the current record's "event_id" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseLinkingEventCategory extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('linking_event_category');
        $this->hasColumn('event_category_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
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
        
    }
}