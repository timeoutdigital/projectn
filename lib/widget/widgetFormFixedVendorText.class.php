<?php
/**
 * sfWidgetFormInput represents an HTML text input tag.
 *
 * @package symfony
 * @subpackage widget.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class widgetFormFixedVendorText extends sfWidgetForm
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
      $this->addRequiredOption('vendor_id');
      $this->addRequiredOption('vendor_name');
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
    $options = array_merge(array(
      'type' => 'hidden', 
      'name' => $name, 
      'value' => $this->options['vendor_id']
    ));

    return $this->renderContentTag('div', $this->options['vendor_name'], $attributes) . $this->renderTag('input', $options, $attributes);
  }
}
