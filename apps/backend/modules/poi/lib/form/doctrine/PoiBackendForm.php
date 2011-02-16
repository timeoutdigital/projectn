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

class PoiBackendForm extends PoiForm
{
    public function configure()
    {
        $this->embedForm( 'DuplicatePoisForm',  new DuplicatePoisForm( $this->getObject() ) );
        $this->validatorSchema[ 'DuplicatePoisForm' ] =  new sfValidatorPass( ); // Stop validating

        $this->embedForm( 'UnsolvableForm', new UnsolvableForm( $this->getObject() ) );
        
        parent::configure();
    }

    public function  doSave($con = null) {

        parent::doSave($con); // Save the Parent Model
        
        // Catch the Unsolvable Postback and Update Model Meta
        $unsolvable = $this->taintedValues['UnsolvableForm'];
        if( isset( $unsolvable['unsolvable']) && $unsolvable['unsolvable'] == 'on')
        {
            $this->getObject()->setUnsolvable( true, $unsolvable['reason']);
            
        } else if( $this->getObject()->getUnsolvable() ){

            $this->getObject()->setUnsolvable( false );
        }
        
        $this->getObject()->save();
    }
    
    public function saveEmbeddedForms($con = null, $forms = null)
    {
        if (null === $con)
        {
            $con = $this->getConnection();
        }

        if (null === $forms)
        {
            $forms = $this->embeddedForms;
        }

        foreach ($forms as $key=>$form)
        {
            if ($form instanceof sfFormObject)
            {
                unset($form[self::$CSRFFieldName]);
                if( isset($this->taintedValues[$key]))
                $form->bindAndSave($this->taintedValues[$key], $this->taintedFiles, $con);
                $form->saveEmbeddedForms($con);
            }
            else
            {
                $this->saveEmbeddedForms($con, $form->getEmbeddedForms());
            }
        }
    }
}