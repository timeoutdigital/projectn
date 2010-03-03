<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ExportLogger', 'project_n');

/**
 * BaseExportLogger
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $vendor_id
 * @property enum $type
<<<<<<< HEAD:lib/model/doctrine/base/BaseExportLogger.class.php
 * @property string $environment
 * @property time $total_time
 * @property Vendor $Vendor
 * @property Doctrine_Collection $ExportLoggerItens
 * 
 * @method integer             getVendorId()          Returns the current record's "vendor_id" value
 * @method enum                getType()              Returns the current record's "type" value
 * @method string              getEnvironment()       Returns the current record's "environment" value
 * @method time                getTotalTime()         Returns the current record's "total_time" value
 * @method Vendor              getVendor()            Returns the current record's "Vendor" value
 * @method Doctrine_Collection getExportLoggerItens() Returns the current record's "ExportLoggerItens" collection
 * @method ExportLogger        setVendorId()          Sets the current record's "vendor_id" value
 * @method ExportLogger        setType()              Sets the current record's "type" value
 * @method ExportLogger        setEnvironment()       Sets the current record's "environment" value
 * @method ExportLogger        setTotalTime()         Sets the current record's "total_time" value
 * @method ExportLogger        setVendor()            Sets the current record's "Vendor" value
 * @method ExportLogger        setExportLoggerItens() Sets the current record's "ExportLoggerItens" collection
=======
 * @property time $total_time
 * @property Vendor $Vendor
 * @property Doctrine_Collection $ExportLoggerItem
 * 
 * @method integer             getVendorId()         Returns the current record's "vendor_id" value
 * @method enum                getType()             Returns the current record's "type" value
 * @method time                getTotalTime()        Returns the current record's "total_time" value
 * @method Vendor              getVendor()           Returns the current record's "Vendor" value
 * @method Doctrine_Collection getExportLoggerItem() Returns the current record's "ExportLoggerItem" collection
 * @method ExportLogger        setVendorId()         Sets the current record's "vendor_id" value
 * @method ExportLogger        setType()             Sets the current record's "type" value
 * @method ExportLogger        setTotalTime()        Sets the current record's "total_time" value
 * @method ExportLogger        setVendor()           Sets the current record's "Vendor" value
 * @method ExportLogger        setExportLoggerItem() Sets the current record's "ExportLoggerItem" collection
>>>>>>> e6b61449c10006f3908b9410f7f38e2093bf28d2:lib/model/doctrine/base/BaseExportLogger.class.php
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseExportLogger extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('export_logger');
        $this->hasColumn('vendor_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('type', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'movie',
              1 => 'poi',
              2 => 'event',
             ),
             'notnull' => true,
             ));
<<<<<<< HEAD:lib/model/doctrine/base/BaseExportLogger.class.php
        $this->hasColumn('environment', 'string', 10, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '10',
             ));
=======
>>>>>>> e6b61449c10006f3908b9410f7f38e2093bf28d2:lib/model/doctrine/base/BaseExportLogger.class.php
        $this->hasColumn('total_time', 'time', null, array(
             'type' => 'time',
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

<<<<<<< HEAD:lib/model/doctrine/base/BaseExportLogger.class.php
        $this->hasMany('ExportLoggerItens', array(
=======
        $this->hasMany('ExportLoggerItem', array(
>>>>>>> e6b61449c10006f3908b9410f7f38e2093bf28d2:lib/model/doctrine/base/BaseExportLogger.class.php
             'local' => 'id',
             'foreign' => 'export_logger_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}