<?php
/**
 * widgetFormVendorEventCategory
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
class widgetFormEventVendorCategoryChoice extends sfWidgetForm
{
 /**
   * Constructor.
   *
   * Available options:
   *
   *  * type: The widget type
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('record');

    // to maintain BC with symfony 1.2
    //$this->setOption('record', 'relation');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $choices = $this->getChoices();
    $renderer = new sfWidgetFormChoice( array(  
      'choices'  => $choices,
      'multiple' => true,
      ) );
    return $renderer->render( $name, $value, $attributes, $errors );
  }

  private function getChoices()
  {
    $event = $this->options['record'];
    $relatedVendorCategories = Doctrine::getTable( 'VendorEventCategory' )->findByVendorId( $event['Vendor']['id'] );

    $choices = array();
    foreach( $relatedVendorCategories as $category )
    {
      $value   = $category[ 'id' ];
      $display = $category[ 'name' ];
      $choices[$value] = $display;
    }

    return $choices;
  }
}
