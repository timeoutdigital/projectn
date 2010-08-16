<?php
/**
 * widgetFormVendorPoiCategory
 *
 * @package symfony
 * @subpackage widget.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class validatorSetCurrentVendorId extends sfValidatorBase
{
  protected function configure($options = array(), $messages = array())
  {
      $this->addRequiredOption('vendor_id');
  }

  protected function doClean($value)
  {
      return $this->options['vendor_id'];
  }

}
