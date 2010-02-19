<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Media', 'project_n');

/**
 * BaseMedia
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $media_url
 * @property string $mime_type
 * 
 * @method string getMediaUrl()  Returns the current record's "media_url" value
 * @method string getMimeType()  Returns the current record's "mime_type" value
 * @method Media  setMediaUrl()  Sets the current record's "media_url" value
 * @method Media  setMimeType()  Sets the current record's "mime_type" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseMedia extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('media');
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

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}