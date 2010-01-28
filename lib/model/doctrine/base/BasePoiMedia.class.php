<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PoiMedia', 'project_n');

/**
 * BasePoiMedia
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $media_url
 * @property string $mime_type
 * @property integer $poi_id
 * @property Poi $Poi
 * 
 * @method string   getMediaUrl()  Returns the current record's "media_url" value
 * @method string   getMimeType()  Returns the current record's "mime_type" value
 * @method integer  getPoiId()     Returns the current record's "poi_id" value
 * @method Poi      getPoi()       Returns the current record's "Poi" value
 * @method PoiMedia setMediaUrl()  Sets the current record's "media_url" value
 * @method PoiMedia setMimeType()  Sets the current record's "mime_type" value
 * @method PoiMedia setPoiId()     Sets the current record's "poi_id" value
 * @method PoiMedia setPoi()       Sets the current record's "Poi" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BasePoiMedia extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('poi_media');
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
        $this->hasOne('Poi', array(
             'local' => 'poi_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}