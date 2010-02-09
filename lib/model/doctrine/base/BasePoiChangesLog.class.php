<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PoiChangesLog', 'project_n');

/**
 * BasePoiChangesLog
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $log
 * @property integer $poi_id
 * @property Poi $Poi
 * 
 * @method string        getLog()    Returns the current record's "log" value
 * @method integer       getPoiId()  Returns the current record's "poi_id" value
 * @method Poi           getPoi()    Returns the current record's "Poi" value
 * @method PoiChangesLog setLog()    Sets the current record's "log" value
 * @method PoiChangesLog setPoiId()  Sets the current record's "poi_id" value
 * @method PoiChangesLog setPoi()    Sets the current record's "Poi" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BasePoiChangesLog extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('poi_changes_log');
        $this->hasColumn('log', 'string', 500, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '500',
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