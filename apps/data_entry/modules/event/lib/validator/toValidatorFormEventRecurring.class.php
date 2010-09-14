<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorDate validates a date. It also converts the input value to a valid date.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfValidatorDate.class.php 13278 2008-11-23 15:04:24Z FabianLange $
 */
class toValidatorFormEventRecurring extends sfValidatorBase
{

    protected function configure($options = array(), $messages = array())
    {
        //$this->addOption('sl_id', null);
        //$this->addOption('sl_template', null);
    }

    protected function doClean($value)
    {
        $errorSchema = new sfValidatorErrorSchema($this);

        if( $value['recurring_freq'] == 'other')
        {   //dont validate
            return $value;
        }
        if( empty( $value['recurring_until'] ) )
        {
            $errorSchema->addError(  new sfValidatorError( $this,
                    'Please specify the last confirmed date of the event',
                     array('field' => 'recurring_until') )  );
        }

        if (count($errorSchema))
        {
          throw $errorSchema;
        }

        return $value;
    }

}