<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addlinkingeventcategorymapping extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('linking_event_category_mapping', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => 8,
              'autoincrement' => true,
              'primary' => true,
             ),
             'event_category_id' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
             ),
             'vendor_event_category_id' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
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
        $this->dropTable('linking_event_category_mapping');
    }
}