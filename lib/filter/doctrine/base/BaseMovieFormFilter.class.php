<?php

/**
 * Movie filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vendor_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'vendor_movie_id'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'name'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'plot'              => new sfWidgetFormFilterInput(),
      'tag_line'          => new sfWidgetFormFilterInput(),
      'review'            => new sfWidgetFormFilterInput(),
      'url'               => new sfWidgetFormFilterInput(),
      'director'          => new sfWidgetFormFilterInput(),
      'writer'            => new sfWidgetFormFilterInput(),
      'cast'              => new sfWidgetFormFilterInput(),
      'age_rating'        => new sfWidgetFormFilterInput(),
      'release_date'      => new sfWidgetFormFilterInput(),
      'duration'          => new sfWidgetFormFilterInput(),
      'country'           => new sfWidgetFormFilterInput(),
      'language'          => new sfWidgetFormFilterInput(),
      'aspect_ratio'      => new sfWidgetFormFilterInput(),
      'sound_mix'         => new sfWidgetFormFilterInput(),
      'company'           => new sfWidgetFormFilterInput(),
      'rating'            => new sfWidgetFormFilterInput(),
      'utf_offset'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'imdb_id'           => new sfWidgetFormFilterInput(),
      'created_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'movie_genres_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MovieGenre')),
    ));

    $this->setValidators(array(
      'vendor_id'         => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'vendor_movie_id'   => new sfValidatorPass(array('required' => false)),
      'name'              => new sfValidatorPass(array('required' => false)),
      'plot'              => new sfValidatorPass(array('required' => false)),
      'tag_line'          => new sfValidatorPass(array('required' => false)),
      'review'            => new sfValidatorPass(array('required' => false)),
      'url'               => new sfValidatorPass(array('required' => false)),
      'director'          => new sfValidatorPass(array('required' => false)),
      'writer'            => new sfValidatorPass(array('required' => false)),
      'cast'              => new sfValidatorPass(array('required' => false)),
      'age_rating'        => new sfValidatorPass(array('required' => false)),
      'release_date'      => new sfValidatorPass(array('required' => false)),
      'duration'          => new sfValidatorPass(array('required' => false)),
      'country'           => new sfValidatorPass(array('required' => false)),
      'language'          => new sfValidatorPass(array('required' => false)),
      'aspect_ratio'      => new sfValidatorPass(array('required' => false)),
      'sound_mix'         => new sfValidatorPass(array('required' => false)),
      'company'           => new sfValidatorPass(array('required' => false)),
      'rating'            => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'utf_offset'        => new sfValidatorPass(array('required' => false)),
      'imdb_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'movie_genres_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'MovieGenre', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('movie_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addMovieGenresListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingMovieGenre LinkingMovieGenre')
          ->andWhereIn('LinkingMovieGenre.movie_genre_id', $values);
  }

  public function getModelName()
  {
    return 'Movie';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'vendor_id'         => 'ForeignKey',
      'vendor_movie_id'   => 'Text',
      'name'              => 'Text',
      'plot'              => 'Text',
      'tag_line'          => 'Text',
      'review'            => 'Text',
      'url'               => 'Text',
      'director'          => 'Text',
      'writer'            => 'Text',
      'cast'              => 'Text',
      'age_rating'        => 'Text',
      'release_date'      => 'Text',
      'duration'          => 'Text',
      'country'           => 'Text',
      'language'          => 'Text',
      'aspect_ratio'      => 'Text',
      'sound_mix'         => 'Text',
      'company'           => 'Text',
      'rating'            => 'Number',
      'utf_offset'        => 'Text',
      'imdb_id'           => 'Number',
      'created_at'        => 'Date',
      'updated_at'        => 'Date',
      'movie_genres_list' => 'ManyKey',
    );
  }
}
