<?php
/**
 * widgetFormVendorPoiCategory
 *
 * @package symfony
 * @subpackage widget.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class validatorMovieGenreChoice extends sfValidatorChoice
{
  protected function configure($options = array(), $messages = array())
  {
    $this->removeRequiredOption( 'choices' );
    $this->addOption( 'multiple' );
    $this->setOption( 'multiple', true );
  }

  protected function doClean($value)
  {
    return parent::doClean($value);
  }

  public function getChoices()
  {
    $choices = array();
    foreach( $this->getGenres() as $category )
    {
      $value   = $category['id'];
      $display = $category['name'];
      //$choices[ $value ] = $display;
      $choices[] = $value;
    }
    return $choices;
  }

  private function getGenres()
  {
    return Doctrine::getTable( 'MovieGenre' )->findAll();
  }

  private function removeRequiredOption( $option )
  {
    unset( $this->requiredOptions[ $option ] );
  }
}
