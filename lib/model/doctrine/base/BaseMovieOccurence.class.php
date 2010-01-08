<?php

/**
 * BaseMovieOccurence
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property datetime $start
 * @property datetime $end
 * @property time $utf_offset
 * @property integer $movie_id
 * @property integer $poi_id
 * @property Movie $Movie
 * @property Poi $Poi
 * 
 * @method datetime       getStart()      Returns the current record's "start" value
 * @method datetime       getEnd()        Returns the current record's "end" value
 * @method time           getUtfOffset()  Returns the current record's "utf_offset" value
 * @method integer        getMovieId()    Returns the current record's "movie_id" value
 * @method integer        getPoiId()      Returns the current record's "poi_id" value
 * @method Movie          getMovie()      Returns the current record's "Movie" value
 * @method Poi            getPoi()        Returns the current record's "Poi" value
 * @method MovieOccurence setStart()      Sets the current record's "start" value
 * @method MovieOccurence setEnd()        Sets the current record's "end" value
 * @method MovieOccurence setUtfOffset()  Sets the current record's "utf_offset" value
 * @method MovieOccurence setMovieId()    Sets the current record's "movie_id" value
 * @method MovieOccurence setPoiId()      Sets the current record's "poi_id" value
 * @method MovieOccurence setMovie()      Sets the current record's "Movie" value
 * @method MovieOccurence setPoi()        Sets the current record's "Poi" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseMovieOccurence extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('movie_occurence');
        $this->hasColumn('start', 'datetime', null, array(
             'type' => 'datetime',
             'notnull' => true,
             ));
        $this->hasColumn('end', 'datetime', null, array(
             'type' => 'datetime',
             'notnull' => false,
             ));
        $this->hasColumn('utf_offset', 'time', null, array(
             'type' => 'time',
             'notnull' => true,
             ));
        $this->hasColumn('movie_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('poi_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Movie', array(
             'local' => 'movie_id',
             'foreign' => 'id'));

        $this->hasOne('Poi', array(
             'local' => 'poi_id',
             'foreign' => 'id'));
    }
}