<?php

/**
 * BaseMovieMedia
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $media_url
 * @property string $mime_type
 * @property integer $movie_id
 * @property Movie $Movie
 * 
 * @method string     getMediaUrl()  Returns the current record's "media_url" value
 * @method string     getMimeType()  Returns the current record's "mime_type" value
 * @method integer    getMovieId()   Returns the current record's "movie_id" value
 * @method Movie      getMovie()     Returns the current record's "Movie" value
 * @method MovieMedia setMediaUrl()  Sets the current record's "media_url" value
 * @method MovieMedia setMimeType()  Sets the current record's "mime_type" value
 * @method MovieMedia setMovieId()   Sets the current record's "movie_id" value
 * @method MovieMedia setMovie()     Sets the current record's "Movie" value
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseMovieMedia extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('movie_media');
        $this->hasColumn('media_url', 'string', 1024, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '1024',
             ));
        $this->hasColumn('mime_type', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('movie_id', 'integer', null, array(
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

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}