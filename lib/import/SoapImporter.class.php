<?php

/**
 * @package import.lib
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 */
abstract class SoapImporter extends Importer
{
  /**
   * @var soapclient
   */
  private $_soapClient;
  private $_fault;

  public function  __construct( $wsdl )
  {
    $this->_soapClient = new soapclient( $wsdl );
  }

  /**
   *
   * @return array Soap call response
   */
  protected function load()
  {
    try
    {
      return $this->_soapClient->__soapCall( $this->getSoapFunction(), $this->getSoapParams() );
    }
    catch( Exception $e )
    {
      $this->_fault = $e;
      echo 'Need to log exception: ' . $e;
    }
  }

  /**
   * Returns true if errors occurred
   * otherwise false if loadData() run successfully
   *
   * @return boolean
   */
  public function hasErrors()
  {
    return !is_null( $this->_fault );
  }

  /**
   * @return string Name of the soap function
   */
  abstract protected function getSoapFunction();

  /**
   * @return array Parameters for the soap function
   */
  protected function getSoapParams()
  {
    return array();
  }
}
?>
