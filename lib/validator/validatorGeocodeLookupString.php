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
class validatorGeocodeLookupString extends sfValidatorBase
{
  protected function configure($options = array(), $messages = array())
  {
      $this->addRequiredOption('poi');
      $this->addRequiredOption('fields');
  }

  protected function doClean($value)
  {
      $geocodeLookup = '';

      for ($i=0; $i<count( $this->options['fields'] ); $i++ )
      {
          $geocodeLookup .= $this->options['poi'][ $this->options['fields'][ $i ] ];
          if ( $i != count( $this->options['fields'] ) -1 ) $geocodeLookup .= ', ';
      }     

      return $geocodeLookup;
  }

}
