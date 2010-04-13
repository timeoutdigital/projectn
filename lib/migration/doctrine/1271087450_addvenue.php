<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addvenue extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('venue', array(
             'id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'primary' => true,
              'autoincrement' => true,
              'length' => 4,
             ),
             'neighbourhood_id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'notnull' => true,
              'length' => 4,
             ),
             'name' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'address' => 
             array(
              'type' => 'string',
              'length' => 2147483647,
             ),
             'postcode' => 
             array(
              'type' => 'string',
              'length' => 20,
             ),
             'latitude' => 
             array(
              'type' => 'decimal',
              'notnull' => true,
              'scale' => false,
              'length' => 9,
             ),
             'longitude' => 
             array(
              'type' => 'decimal',
              'notnull' => true,
              'scale' => false,
              'length' => 9,
             ),
             'status' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'notnull' => true,
              'length' => 4,
             ),
             'source_id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'length' => 4,
             ),
             'event_count' => 
             array(
              'type' => 'integer',
              'default' => '0',
              'notnull' => true,
              'length' => 4,
             ),
             'alt_name' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'building_name' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'travel' => 
             array(
              'type' => 'string',
              'length' => 2147483647,
             ),
             'opening_times' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'url' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'phone' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'email' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'image_id' => 
             array(
              'type' => 'integer',
              'length' => 4,
             ),
             'source' => 
             array(
              'type' => 'string',
              'length' => 15,
             ),
             'annotation' => 
             array(
              'type' => 'string',
              'length' => 2147483647,
             ),
             ), array(
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
    }

    public function down()
    {
        $this->dropTable('venue');
    }
}