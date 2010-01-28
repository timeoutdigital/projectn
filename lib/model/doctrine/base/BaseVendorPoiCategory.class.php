<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('VendorPoiCategory', 'project_n');

/**
 * BaseVendorPoiCategory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * @property integer $vendor_id
 * @property Vendor $Vendor
 * @property Doctrine_Collection $Poi
 * @property Doctrine_Collection $PoiCategoryMapping
 * 
 * @method string              getName()               Returns the current record's "name" value
 * @method integer             getVendorId()           Returns the current record's "vendor_id" value
 * @method Vendor              getVendor()             Returns the current record's "Vendor" value
 * @method Doctrine_Collection getPoi()                Returns the current record's "Poi" collection
 * @method Doctrine_Collection getPoiCategoryMapping() Returns the current record's "PoiCategoryMapping" collection
 * @method VendorPoiCategory   setName()               Sets the current record's "name" value
 * @method VendorPoiCategory   setVendorId()           Sets the current record's "vendor_id" value
 * @method VendorPoiCategory   setVendor()             Sets the current record's "Vendor" value
 * @method VendorPoiCategory   setPoi()                Sets the current record's "Poi" collection
 * @method VendorPoiCategory   setPoiCategoryMapping() Sets the current record's "PoiCategoryMapping" collection
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseVendorPoiCategory extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('vendor_poi_category');
        $this->hasColumn('name', 'string', 256, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '256',
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

        $this->hasMany('Poi', array(
             'refClass' => 'LinkingVendorPoiCategory',
             'local' => 'vendor_poi_category_id',
             'foreign' => 'poi_id'));

        $this->hasMany('PoiCategoryMapping', array(
             'local' => 'id',
             'foreign' => 'map_from_id'));
    }
}