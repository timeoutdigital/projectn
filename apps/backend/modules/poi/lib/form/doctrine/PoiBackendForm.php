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

class PoiBackendForm extends BasePoiForm
{
    public function configure()
    {
        $this->embedForm( 'DuplicatePoisForm',  new DuplicatePoisForm( $this->getObject() ) );
        $this->validatorSchema[ 'DuplicatePoisForm' ] =  new sfValidatorPass( ); // Stop validating
        
        parent::configure();
    }

    public function saveEmbeddedForms($con = null, $forms = null)
    {
        //unset( $forms['DuplicatePoisForm'] );
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
                $form->bindAndSave($this->taintedValues[$key], $this->taintedFiles, $con);
                $form->saveEmbeddedForms($con);
            }
            else
            {
                $this->saveEmbeddedForms($con, $form->getEmbeddedForms());
            }
        }
    }

//    public function save($con = null) {
//
//        $poi = parent::save($con);
//
//
//        $values = $this->getValues();
//
//        print_r( $values ); die( 'SAVE()' );
//    }
}