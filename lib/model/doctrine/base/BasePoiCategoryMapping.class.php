<?php

/**
 * BasePoiCategoryMapping
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $map_from_id
 * @property integer $map_to_id
 * @property VendorPoiCategory $VendorPoiCategory
 * @property PoiCategory $PoiCategory
 * 
 * @method integer            getMapFromId()         Returns the current record's "map_from_id" value
 * @method integer            getMapToId()           Returns the current record's "map_to_id" value
 * @method VendorPoiCategory  getVendorPoiCategory() Returns the current record's "VendorPoiCategory" value
 * @method PoiCategory        getPoiCategory()       Returns the current record's "PoiCategory" value
 * @method PoiCategoryMapping setMapFromId()         Sets the current record's "map_from_id" value
 * @method PoiCategoryMapping setMapToId()           Sets the current record's "map_to_id" value
 * @method PoiCategoryMapping setVendorPoiCategory() Sets the current record's "VendorPoiCategory" value
 * @method PoiCategoryMapping setPoiCategory()       Sets the current record's "PoiCategory" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BasePoiCategoryMapping extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('poi_category_mapping');
        $this->hasColumn('map_from_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('map_to_id', 'integer', null, array(
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
        $this->hasOne('VendorPoiCategory', array(
             'local' => 'map_from_id',
             'foreign' => 'id'));

        $this->hasOne('PoiCategory', array(
             'local' => 'map_to_id',
             'foreign' => 'id'));
    }
}