<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('MovieProperty', 'project_n');

/**
 * BaseMovieProperty
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $lookup
 * @property string $value
 * @property integer $movie_id
 * @property Movie $Movie
 * 
 * @method integer       getId()       Returns the current record's "id" value
 * @method string        getLookup()   Returns the current record's "lookup" value
 * @method string        getValue()    Returns the current record's "value" value
 * @method integer       getMovieId()  Returns the current record's "movie_id" value
 * @method Movie         getMovie()    Returns the current record's "Movie" value
 * @method MovieProperty setId()       Sets the current record's "id" value
 * @method MovieProperty setLookup()   Sets the current record's "lookup" value
 * @method MovieProperty setValue()    Sets the current record's "value" value
 * @method MovieProperty setMovieId()  Sets the current record's "movie_id" value
 * @method MovieProperty setMovie()    Sets the current record's "Movie" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseMovieProperty extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('movie_property');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'autoincrement' => true,
             'primary' => true,
             ));
        $this->hasColumn('lookup', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '50',
             ));
        $this->hasColumn('value', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '50',
             ));
        $this->hasColumn('movie_id', 'integer', null, array(
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
        $this->hasOne('Movie', array(
             'local' => 'movie_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}