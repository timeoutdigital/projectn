<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class sfValidatorSchemaUnsolvable extends sfValidatorSchema
{
    public function  __construct($fields = null, $options = array(), $messages = array())
    {
        $this->addMessage( 'no_reason', 'Please enter a valid reason for marking this record as unsolvable');
        parent::__construct($fields, $options, $messages);
    }
    
    public function  doClean($values) {
        
        $values = parent::doClean($values);
        $errorSchema = new sfValidatorErrorSchema($this); 

        if( isset($values['unsolvable']) && $values['unsolvable'] == 'on')
        {
            if( !isset($values['reason']) || trim($values['reason']) == '' )
            {
                $errorSchema->addError( new sfValidatorError( $this, 'no_reason',  array('field' => 'reason')), 'reason' );
            }
        }
        
        if( count($errorSchema) )
            throw $errorSchema;
        
        return $values;
    }
}