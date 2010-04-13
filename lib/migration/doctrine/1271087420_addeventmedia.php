<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addeventmedia extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('event_media', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => 8,
              'autoincrement' => true,
              'primary' => true,
             ),
             'ident' => 
             array(
              'type' => 'string',
              'notnull' => true,
              'unique' => true,
              'length' => 32,
             ),
             'url' => 
             array(
              'type' => 'string',
              'notnull' => true,
              'length' => 1024,
             ),
             'mime_type' => 
             array(
              'type' => 'string',
              'notnull' => true,
              'length' => 255,
             ),
             'file_last_modified' => 
             array(
              'type' => 'string',
              'notnull' => false,
              'length' => 255,
             ),
             'etag' => 
             array(
              'type' => 'string',
              'notnull' => false,
              'length' => 255,
             ),
             'content_length' => 
             array(
              'type' => 'integer',
              'notnull' => false,
              'length' => 8,
             ),
             'event_id' => 
             array(
              'type' => 'integer',
              'notnull' => true,
              'length' => 8,
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
              'ident_index' => 
              array(
              'fields' => 
              array(
               0 => 'ident',
              ),
              ),
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
        $this->dropTable('event_media');
    }
}