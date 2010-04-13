<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Movie', 'project_n');

/**
 * BaseMovie
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $vendor_id
 * @property string $vendor_movie_id
 * @property string $name
 * @property string $plot
 * @property string $tag_line
 * @property string $review
 * @property string $url
 * @property string $director
 * @property string $writer
 * @property string $cast
 * @property string $age_rating
 * @property date $release_date
 * @property string $duration
 * @property string $country
 * @property string $language
 * @property string $aspect_ratio
 * @property string $sound_mix
 * @property string $company
 * @property decimal $rating
 * @property string $utf_offset
 * @property varchar $imdb_id
 * @property Vendor $Vendor
 * @property Doctrine_Collection $MovieGenres
 * @property Doctrine_Collection $MovieMedia
 * @property Doctrine_Collection $MovieProperty
 * @property Doctrine_Collection $RecordFieldOverride
 * 
 * @method integer             getId()                  Returns the current record's "id" value
 * @method integer             getVendorId()            Returns the current record's "vendor_id" value
 * @method string              getVendorMovieId()       Returns the current record's "vendor_movie_id" value
 * @method string              getName()                Returns the current record's "name" value
 * @method string              getPlot()                Returns the current record's "plot" value
 * @method string              getTagLine()             Returns the current record's "tag_line" value
 * @method string              getReview()              Returns the current record's "review" value
 * @method string              getUrl()                 Returns the current record's "url" value
 * @method string              getDirector()            Returns the current record's "director" value
 * @method string              getWriter()              Returns the current record's "writer" value
 * @method string              getCast()                Returns the current record's "cast" value
 * @method string              getAgeRating()           Returns the current record's "age_rating" value
 * @method date                getReleaseDate()         Returns the current record's "release_date" value
 * @method string              getDuration()            Returns the current record's "duration" value
 * @method string              getCountry()             Returns the current record's "country" value
 * @method string              getLanguage()            Returns the current record's "language" value
 * @method string              getAspectRatio()         Returns the current record's "aspect_ratio" value
 * @method string              getSoundMix()            Returns the current record's "sound_mix" value
 * @method string              getCompany()             Returns the current record's "company" value
 * @method decimal             getRating()              Returns the current record's "rating" value
 * @method string              getUtfOffset()           Returns the current record's "utf_offset" value
 * @method varchar             getImdbId()              Returns the current record's "imdb_id" value
 * @method Vendor              getVendor()              Returns the current record's "Vendor" value
 * @method Doctrine_Collection getMovieGenres()         Returns the current record's "MovieGenres" collection
 * @method Doctrine_Collection getMovieMedia()          Returns the current record's "MovieMedia" collection
 * @method Doctrine_Collection getMovieProperty()       Returns the current record's "MovieProperty" collection
 * @method Doctrine_Collection getRecordFieldOverride() Returns the current record's "RecordFieldOverride" collection
 * @method Movie               setId()                  Sets the current record's "id" value
 * @method Movie               setVendorId()            Sets the current record's "vendor_id" value
 * @method Movie               setVendorMovieId()       Sets the current record's "vendor_movie_id" value
 * @method Movie               setName()                Sets the current record's "name" value
 * @method Movie               setPlot()                Sets the current record's "plot" value
 * @method Movie               setTagLine()             Sets the current record's "tag_line" value
 * @method Movie               setReview()              Sets the current record's "review" value
 * @method Movie               setUrl()                 Sets the current record's "url" value
 * @method Movie               setDirector()            Sets the current record's "director" value
 * @method Movie               setWriter()              Sets the current record's "writer" value
 * @method Movie               setCast()                Sets the current record's "cast" value
 * @method Movie               setAgeRating()           Sets the current record's "age_rating" value
 * @method Movie               setReleaseDate()         Sets the current record's "release_date" value
 * @method Movie               setDuration()            Sets the current record's "duration" value
 * @method Movie               setCountry()             Sets the current record's "country" value
 * @method Movie               setLanguage()            Sets the current record's "language" value
 * @method Movie               setAspectRatio()         Sets the current record's "aspect_ratio" value
 * @method Movie               setSoundMix()            Sets the current record's "sound_mix" value
 * @method Movie               setCompany()             Sets the current record's "company" value
 * @method Movie               setRating()              Sets the current record's "rating" value
 * @method Movie               setUtfOffset()           Sets the current record's "utf_offset" value
 * @method Movie               setImdbId()              Sets the current record's "imdb_id" value
 * @method Movie               setVendor()              Sets the current record's "Vendor" value
 * @method Movie               setMovieGenres()         Sets the current record's "MovieGenres" collection
 * @method Movie               setMovieMedia()          Sets the current record's "MovieMedia" collection
 * @method Movie               setMovieProperty()       Sets the current record's "MovieProperty" collection
 * @method Movie               setRecordFieldOverride() Sets the current record's "RecordFieldOverride" collection
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseMovie extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('movie');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'autoincrement' => true,
             'primary' => true,
             ));
        $this->hasColumn('vendor_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('vendor_movie_id', 'string', 25, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '25',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('plot', 'string', 65535, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '65535',
             ));
        $this->hasColumn('tag_line', 'string', 65535, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '65535',
             ));
        $this->hasColumn('review', 'string', 65535, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '65535',
             ));
        $this->hasColumn('url', 'string', 1024, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '1024',
             ));
        $this->hasColumn('director', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '255',
             ));
        $this->hasColumn('writer', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '255',
             ));
        $this->hasColumn('cast', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '255',
             ));
        $this->hasColumn('age_rating', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('release_date', 'date', null, array(
             'type' => 'date',
             'notnull' => false,
             ));
        $this->hasColumn('duration', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('country', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('language', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('aspect_ratio', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('sound_mix', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('company', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('rating', 'decimal', null, array(
             'type' => 'decimal',
             'scale' => 1,
             'notnull' => false,
             ));
        $this->hasColumn('utf_offset', 'string', 9, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '9',
             ));
        $this->hasColumn('imdb_id', 'varchar', 10, array(
             'type' => 'varchar',
             'notnull' => false,
             'length' => '10',
             ));


        $this->index('vendor_movie_id_index', array(
             'fields' => 
             array(
              0 => 'vendor_movie_id',
             ),
             ));
        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Vendor', array(
             'local' => 'vendor_id',
             'foreign' => 'id'));

        $this->hasMany('MovieGenre as MovieGenres', array(
             'refClass' => 'LinkingMovieGenre',
             'local' => 'movie_id',
             'foreign' => 'movie_genre_id'));

        $this->hasMany('MovieMedia', array(
             'local' => 'id',
             'foreign' => 'movie_id'));

        $this->hasMany('MovieProperty', array(
             'local' => 'id',
             'foreign' => 'movie_id'));

        $this->hasMany('RecordFieldOverrideMovie as RecordFieldOverride', array(
             'local' => 'id',
             'foreign' => 'record_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}