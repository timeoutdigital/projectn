<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('RecordFieldOverride', 'project_n');

/**
 * BaseRecordFieldOverride
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $record_id
 * @property string $field
 * @property string $received_value
 * @property string $edited_value
 * @property boolean $is_active
 * 
 * @method integer             getRecordId()       Returns the current record's "record_id" value
 * @method string              getField()          Returns the current record's "field" value
 * @method string              getReceivedValue()  Returns the current record's "received_value" value
 * @method string              getEditedValue()    Returns the current record's "edited_value" value
 * @method boolean             getIsActive()       Returns the current record's "is_active" value
 * @method RecordFieldOverride setRecordId()       Sets the current record's "record_id" value
 * @method RecordFieldOverride setField()          Sets the current record's "field" value
 * @method RecordFieldOverride setReceivedValue()  Sets the current record's "received_value" value
 * @method RecordFieldOverride setEditedValue()    Sets the current record's "edited_value" value
 * @method RecordFieldOverride setIsActive()       Sets the current record's "is_active" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseRecordFieldOverride extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('record_field_override');
        $this->hasColumn('record_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('field', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '50',
             ));
        $this->hasColumn('received_value', 'string', null, array(
             'type' => 'string',
             'notnull' => false,
             ));
        $this->hasColumn('edited_value', 'string', null, array(
             'type' => 'string',
             'notnull' => false,
             ));
        $this->hasColumn('is_active', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
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