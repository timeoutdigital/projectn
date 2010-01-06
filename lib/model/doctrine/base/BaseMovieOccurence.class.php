<?php

/**
 * BaseMovieOccurence
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $vender_id
 * @property date $start_date
 * @property date $end_date
 * @property time $start_time
 * @property time $end_time
 * @property time $utf_offset
 * @property integer $movie_id
 * @property integer $poi_id
 * @property Movie $Movie
 * @property Poi $Poi
 * 
 * @method integer        getVenderId()   Returns the current record's "vender_id" value
 * @method date           getStartDate()  Returns the current record's "start_date" value
 * @method date           getEndDate()    Returns the current record's "end_date" value
 * @method time           getStartTime()  Returns the current record's "start_time" value
 * @method time           getEndTime()    Returns the current record's "end_time" value
 * @method time           getUtfOffset()  Returns the current record's "utf_offset" value
 * @method integer        getMovieId()    Returns the current record's "movie_id" value
 * @method integer        getPoiId()      Returns the current record's "poi_id" value
 * @method Movie          getMovie()      Returns the current record's "Movie" value
 * @method Poi            getPoi()        Returns the current record's "Poi" value
 * @method MovieOccurence setVenderId()   Sets the current record's "vender_id" value
 * @method MovieOccurence setStartDate()  Sets the current record's "start_date" value
 * @method MovieOccurence setEndDate()    Sets the current record's "end_date" value
 * @method MovieOccurence setStartTime()  Sets the current record's "start_time" value
 * @method MovieOccurence setEndTime()    Sets the current record's "end_time" value
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
        $this->hasColumn('vender_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('start_date', 'date', null, array(
             'type' => 'date',
             'notnull' => true,
             ));
        $this->hasColumn('end_date', 'date', null, array(
             'type' => 'date',
             'notnull' => false,
             ));
        $this->hasColumn('start_time', 'time', null, array(
             'type' => 'time',
             'notnull' => false,
             ));
        $this->hasColumn('end_time', 'time', null, array(
             'type' => 'time',
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