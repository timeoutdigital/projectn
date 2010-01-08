<?php

/**
 * BaseEventMedia
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $media_url
 * @property string $mime_type
 * @property integer $event_id
 * @property Event $Event
 * 
 * @method string     getMediaUrl()  Returns the current record's "media_url" value
 * @method string     getMimeType()  Returns the current record's "mime_type" value
 * @method integer    getEventId()   Returns the current record's "event_id" value
 * @method Event      getEvent()     Returns the current record's "Event" value
 * @method EventMedia setMediaUrl()  Sets the current record's "media_url" value
 * @method EventMedia setMimeType()  Sets the current record's "mime_type" value
 * @method EventMedia setEventId()   Sets the current record's "event_id" value
 * @method EventMedia setEvent()     Sets the current record's "Event" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseEventMedia extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event_media');
        $this->hasColumn('media_url', 'string', 1024, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '1024',
             ));
        $this->hasColumn('mime_type', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('event_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
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