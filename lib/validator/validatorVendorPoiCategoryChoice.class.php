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
    $this->addRequiredOption('vendor_id');
  }

  protected function doClean($value)
  {
    return parent::doClean($value);
  }

  public function getChoices()
  {
    $relatedVendorCategories = Doctrine::getTable( 'VendorPoiCategory' )->findByVendorId( $this->options['vendor_id'] );

    $choices = array();
    foreach( $relatedVendorCategories as $category )
    {
      $value   = $category['id'];
      $display = $category['name'];
      //$choices[ $value ] = $display;
      $choices[] = $value;
    }
    return $choices;
  }

  private function removeRequiredOption( $option )
  {
    unset( $this->requiredOptions[ $option ] );
  }
}
