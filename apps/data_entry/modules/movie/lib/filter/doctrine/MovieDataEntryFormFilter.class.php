<?php

/**
 * Movie filter form.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MovieDataEntryFormFilter extends BaseMovieFormFilter
{
  private $user;

  public function configure()
  {
      $this->user = sfContext::getInstance()->getUser();
      $this->setVendorWidget();
      $this->setMovieGenresList();
  }

  private function setVendorWidget()
  {
      $permittedVendorCitiesChoices = $this->user->getPermittedVendorCities( true );

      $this->widgetSchema ['vendor_id'] = new sfWidgetFormSelect( array( 'choices' => $permittedVendorCitiesChoices ) );
      $this->validatorSchema ['vendor_id'] = new sfValidatorChoice( array( 'choices' => array_keys( $permittedVendorCitiesChoices ), 'required' => true ) );
   }

  private function setMovieGenresList()
  {
      $permittedVendorCitiesChoices = $this->user->getPermittedVendorCities( true );
      $this->widgetSchema[ 'movie_genres_list' ] = new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MovieGenre', 'method' => 'getGenre', 'order_by' => array( 'genre', 'asc' ) ));
  }

  public function addVendorIdColumnQuery($query, $field, $value)
  {
      $this->user->setCurrentVendorById( $value );
      $query->andWhere( $query->getRootAlias() . '.vendor_id = ?', $value );
  }
}
