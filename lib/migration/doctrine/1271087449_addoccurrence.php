<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addoccurrence extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('occurrence', array(
             'id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'primary' => true,
              'autoincrement' => true,
              'length' => 4,
             ),
             'event_id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'notnull' => true,
              'length' => 4,
             ),
             'venue_id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'notnull' => true,
              'length' => 4,
             ),
             'date_start' => 
             array(
              'type' => 'date',
              'notnull' => true,
              'length' => 25,
             ),
             'time_start' => 
             array(
              'type' => 'time',
              'notnull' => true,
              'length' => 25,
             ),
             'date_end' => 
             array(
              'type' => 'date',
              'notnull' => true,
              'length' => 25,
             ),
             'time_end' => 
             array(
              'type' => 'time',
              'notnull' => true,
              'length' => 25,
             ),
             'annotation_behaviour' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'length' => 1,
             ),
             'new' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 1,
             ),
             'last_chance' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 1,
             ),
             'recommended' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'notnull' => true,
              'length' => 1,
             ),
             'source' => 
             array(
              'type' => 'string',
              'notnull' => true,
              'length' => 15,
             ),
             'source_id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'notnull' => true,
              'length' => 4,
             ),
             'search_grouping_id' => 
             array(
              'type' => 'integer',
              'unsigned' => 1,
              'default' => '0',
              'notnull' => true,
              'length' => 4,
             ),
             'seo_synopsis' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'title' => 
             array(
              'type' => 'string',
              'length' => 2147483647,
             ),
             'annotation' => 
             array(
              'type' => 'string',
              'length' => 2147483647,
             ),
             'price' => 
             array(
              'type' => 'string',
              'length' => 2147483647,
             ),
             'notable_title' => 
             array(
              'type' => 'string',
              'length' => 30,
             ),
             'image_id' => 
             array(
              'type' => 'integer',
              'length' => 4,
             ),
             'page_views' => 
             array(
              'type' => 'int',
              'length' => 11,
             ),
             'flickr_tag' => 
             array(
              'type' => 'string',
              'length' => 255,
             ),
             'advanced_text' => 
             array(
              'type' => 'string',
              'length' => 2147483647,
             ),
             'date_created' => 
             array(
              'type' => 'date',
              'length' => 25,
             ),
             'date_modified' => 
             array(
              'type' => 'date',
              'length' => 25,
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
        $this->dropTable('occurrence');
    }
}