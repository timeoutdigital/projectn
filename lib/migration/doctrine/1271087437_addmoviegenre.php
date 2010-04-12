<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addmoviegenre extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('movie_genre', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => 8,
              'autoincrement' => true,
              'primary' => true,
             ),
             'genre' => 
             array(
              'type' => 'string',
              'notnull' => true,
              'length' => 255,
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
              'genre_index' => 
              array(
              'fields' => 
              array(
               0 => 'genre',
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
        $this->dropTable('movie_genre');
    }
}