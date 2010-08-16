<?php

/**
 * Movie form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MovieForm extends BaseMovieForm
{
  public function configure()
  {
    $this->widgetSchema[ 'vendor_movie_id' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText();
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText();
    $this->widgetSchema[ 'vendor_id' ]       = new widgetFormFixedText();

    $this->widgetSchema[ 'movie_genres_list' ] = new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MovieGenre', 'method' => 'getGenre', 'order_by' => array( 'genre', 'asc' ) ));
  }

  protected function doUpdateObject( $values = null )
  {
    parent::doUpdateObject( $values );

    $record = $this->getObject();
    $override = new recordFieldOverrideManager( $record );
    $override->saveRecordModificationsAsOverrides();
  }

}
