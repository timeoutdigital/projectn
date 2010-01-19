<?php

/**
 * @package london.import.lib
 *
 * @author clarence
 */
class LondonVenueSoapImporter extends SoapImporter
{
    /**
     * @see parent::getSoapFunction()
     *
     * @return string
     */
    protected function getSoapFunction()
    {
      return 'WebService_SendVenues';
    }
}
?>
