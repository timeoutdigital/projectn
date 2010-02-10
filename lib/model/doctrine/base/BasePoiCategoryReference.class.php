<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PoiCategoryReference', 'project_n');

/**
 * BasePoiCategoryReference
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $parent_id
 * @property integer $child_id
 * 
 * @method integer              getParentId()  Returns the current record's "parent_id" value
 * @method integer              getChildId()   Returns the current record's "child_id" value
 * @method PoiCategoryReference setParentId()  Sets the current record's "parent_id" value
 * @method PoiCategoryReference setChildId()   Sets the current record's "child_id" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BasePoiCategoryReference extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('poi_category_reference');
        $this->hasColumn('parent_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('child_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
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