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
class validatorVendorPoiCategoryChoice extends sfValidatorChoice
{
  protected function configure($options = array(), $messages = array())
  {
    $this->removeRequiredOption( 'choices' );
    $this->addOption( 'multiple' );
    $this->setOption( 'multiple', true );
    $this->addRequiredOption( 'poi' );
  }

  protected function doClean($value)
  {
    return parent::doClean($value);
  }

  public function getChoices()
  {
    $choices = array();
    foreach( $this->getCategoriesForPoi() as $category )
    {
      $value   = $category['id'];
      $display = $category['name'];
      //$choices[ $value ] = $display;
      $choices[] = $value;
    }
    return $choices;
  }

  private function getCategoriesForPoi()
  {
    $poi = $this->options['poi'];
    return Doctrine::getTable( 'VendorPoiCategory' )->findByVendorId( 19 );
  }

  private function removeRequiredOption( $option )
  {
    unset( $this->requiredOptions[ $option ] );
  }
}
