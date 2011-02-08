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

class UnsolvableForm extends sfForm
{
   private $model = null;
   
   public function  __construct( Doctrine_Record $model ) {
       
        $this->model = $model;
        parent::__construct();
        
    }
    public function  configure() {

        // Embed Check box and textbox
        $this->setWidgets( array(
            'unsolvable' => new sfWidgetFormInputCheckbox(array() ),
            'reason' => new sfWidgetFormTextarea(array() ),
        ));

        $this->setValidators( array(
            'unsolvable' => new sfValidatorPass(),
            'reason' => new sfValidatorPass()
        ));

        $this->mergePostValidator( new sfValidatorSchemaUnsolvable( array( 'unsolvable' => new sfValidatorPass(), 'reason' => new sfValidatorPass() ) ) );

        // Defaults
        if( $this->model !== null && $this->model->getUnSolvable())
        {
            $this->setDefault( 'unsolvable', true);
            $this->setDefault( 'reason', $this->model->getUnsolvableReason() );
        }
    }

}