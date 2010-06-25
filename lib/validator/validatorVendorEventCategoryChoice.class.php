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
class validatorVendorEventCategoryChoice extends sfValidatorChoice
{
  protected function configure($options = array(), $messages = array())
  {
    $this->removeRequiredOption( 'choices' );
    $this->addOption( 'multiple' );
    $this->setOption( 'multiple', true );
    $this->addRequiredOption( 'event' );
  }

  protected function doClean($value)
  {
    return parent::doClean($value);
  }

  public function getChoices()
  {
    $choices = array();
    foreach( $this->getCategoriesForEvent() as $category )
    {
      $value   = $category['id'];
      $display = $category['name'];
      //$choices[ $value ] = $display;
      $choices[] = $value;
    }
    return $choices;
  }

  private function getCategoriesForEvent()
  {
    $event = $this->options['event'];
    return Doctrine::getTable( 'VendorEventCategory' )->findByVendorId( 19 );
  }

  private function removeRequiredOption( $option )
  {
    unset( $this->requiredOptions[ $option ] );
  }
}
