<?php
/**
 * sfWidgetFormInput represents an HTML text input tag.
 *
 * @package symfony
 * @subpackage validator.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class overridableValidatorText extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * max_length: The maximum length of the string
   *  * min_length: The minimum length of the string
   *
   * Available error codes:
   *
   *  * max_length
   *  * min_length
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption( 'record' );
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    return $value;
  }
}