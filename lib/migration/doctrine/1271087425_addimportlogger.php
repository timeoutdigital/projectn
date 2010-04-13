<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addimportlogger extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('import_logger', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => 8,
              'autoincrement' => true,
              'primary' => true,
             ),
             'total_inserts' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
             ),
             'total_updates' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
             ),
             'total_errors' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
             ),
             'total_existing' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
             ),
             'vendor_id' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
             ),
             'type' => 
             array(
              'type' => 'enum',
              'values' => 
              array(
              0 => 'movie',
              1 => 'poi',
              2 => 'event',
              ),
              'notnull' => true,
              'length' => NULL,
             ),
             'total_time' => 
             array(
              'type' => 'time',
              'notnull' => true,
              'length' => 25,
             ),
             'created_at' => 
             array(
              'notnull' => true,
              'type' => 'timestamp',
              'length' => 25,
             ),
             'updated_at' => 
             array(
              'notnull' => true,
              'type' => 'timestamp',
              'length' => 25,
             ),
             ), array(
             'type' => 'INNODB',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_unicode_ci',
             'charset' => 'utf8',
             ));
    }

    public function down()
    {
        $this->dropTable('import_logger');
    }
}