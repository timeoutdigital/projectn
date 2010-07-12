<?php

/**
 * PoiDataEntryForm form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PoiDataEntryForm extends BasePoiForm
{
  
  private $user;

  private $filePath = 'media/poi';

  public function configure()
  {
    $this->user = sfContext::getInstance()->getUser();

    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );

    $this->widgetSchema[ 'country' ]      = new widgetFormFixedText( array( 'default' => $this->user->getCurrentVendorCountryCodeLong() ) );
    $this->widgetSchema[ 'local_language' ]      = new widgetFormFixedText( array( 'default' => $this->user->getCurrentVendorLanguage() ) );
    $this->widgetSchema[ 'city' ]      = new widgetFormFixedText( array( 'default' => $this->user->getCurrentVendorCity() ) );

    if ( $this->object[ 'latitude' ] === NULL || $this->object[ 'longitude' ] === NULL )
    {
        unset( $this->widgetSchema[ 'latitude' ] );
        unset( $this->widgetSchema[ 'longitude' ] );    
    }
    else
    {
        $this->widgetSchema[ 'latitude' ]      = new widgetFormFixedText();
        $this->widgetSchema[ 'longitude' ]      = new widgetFormFixedText();
    }

    $this->widgetSchema[ 'vendor_id' ]      = new widgetFormFixedVendorText( array( 'vendor_id'  => $this->user->getCurrentVendorId(), 'vendor_name'  => $this->user->getCurrentVendorCity()  ) );
    $this->validatorSchema[ 'vendor_id' ]   = new validatorSetCurrentVendorId( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );

    $this->widgetSchema[ 'geocode_look_up' ] = new widgetFormFixedText( array( 'default' => 'not generated yet' ) );
    $this->validatorSchema[ 'geocode_look_up' ]   = new validatorGeocodeLookupString( array( 'poi' => $this->object, 'fields' => array( 'street', 'city', 'country' ) ) );

    $this->widgetSchema[ 'review_date' ]      = new sfWidgetFormDate();
    $this->validatorSchema[ 'review_date' ]      = new sfValidatorDate();

    $this->configureVendorPoiCategoryWidget();

    /* images */
    $this->embedRelation('PoiMedia');

    /* new poi media */
    $poiMedia = new PoiMedia();
    $poiMedia->Poi = $this->getObject();

    $form = new PoiMediaForm( $poiMedia );

    $form->setValidator('url', new sfValidatorFile(array(
        'mime_types' => array( 'image/jpeg' ),
        'path' => sfConfig::get('sf_upload_dir') . '/' . $this->filePath,
        'required' => false,
    )));

    $form->setWidget('url', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/' . $this->filePath . '/'.$this->getObject()->url,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
    )));

    $this->embedForm( 'newPoiMediaDataEntry', $form );
  }

  public function saveEmbeddedForms($con = null, $forms = null)
  {
      if (null === $forms)
      {
        $forms = $this->embeddedForms;

        $newPoiMediaDataEntry = $this->getValue('newPoiMediaDataEntry');
        if ( !isset( $newPoiMediaDataEntry['url']) )
        {
            unset($forms['newPoiMediaDataEntry'] );
        }

      }

      return parent::saveEmbeddedForms($con, $forms);
  }


  private function configureVendorPoiCategoryWidget()
  {
    $widget = new widgetFormPoiVendorCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->widgetSchema[ 'vendor_poi_category_list' ] = $widget;

    $validator = new validatorVendorPoiCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->validatorSchema[ 'vendor_poi_category_list' ] = $validator;
  }
}
