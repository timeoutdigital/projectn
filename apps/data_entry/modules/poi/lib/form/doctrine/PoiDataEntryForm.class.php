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

  protected $poiMediasScheduledForDeletion = array();


  public function configure()
  {
    $this->user = sfContext::getInstance()->getUser();

    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );

    $this->widgetSchema[ 'country' ]         = new widgetFormFixedText( array( 'default' => $this->user->getCurrentVendorCountryCodeLong() ) );
    $this->widgetSchema[ 'local_language' ]  = new widgetFormFixedText( array( 'default' => $this->user->getCurrentVendorLanguage() ) );
    $this->widgetSchema[ 'city' ]            = new widgetFormFixedText( array( 'default' => ucwords( $this->user->getCurrentVendorCity() ) ) );

    $this->widgetSchema[ 'poi_name' ] = new widgetFormInputTextJQueryAutocompleter( array( 'url' => sfContext::getInstance()->getRequest()->getScriptName() . '/poi/ajaxPoiList' ) );

    //@todo maybe use the jquery calendar
    $this->widgetSchema[ 'review_date' ]      = new sfWidgetFormDate(array());
    
    $this->validatorSchema[ 'review_date' ]    = new sfValidatorDate(array( 'required' => false ));

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

    $this->configureVendorPoiCategoryWidget();

    /* images */
    $this->embedRelation('PoiMedia');

    /* new poi media */
    //@todo find issue why more than 2 imgs failing to save
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
        'edit_mode'   => false,
        'is_image'    => true,
        'with_delete' => false,
    )));

    $this->embedForm( 'newPoiMediaDataEntry', $form );

    $this->mergePostValidator(new PoiDataEntryValidatorSchema());
  }

  protected function doBind(array $values)
  {

    if (isset($values['PoiMedia']))
    {
      foreach ($values['PoiMedia'] as $i => $poiMediaFields )
      {
        if ( isset($poiMediaFields['url_delete']) && $poiMediaFields['id'] )
        {
            $this->poiMediasScheduledForDeletion[$i] = $poiMediaFields['id'];
        }
      }
    }

    parent::doBind($values);
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

        $newPoiMediaDataEntry = $this->getValue('newPoiMediaDataEntry');
        if ( !isset( $newPoiMediaDataEntry['url']) )
        {
            unset($forms['newPoiMediaDataEntry'] );
        }
      }

      foreach ($forms as $form)
      {
          if ($form instanceof sfFormObject)
          {
              if ( $form->getObject() instanceof PoiMedia )
              {
                  if ( !in_array($form->getObject()->getId(), $this->poiMediasScheduledForDeletion ))
                  {
                    $form->saveEmbeddedForms($con);
                    
                    $media = $form->getObject();
                    $urlParts = explode( '.', $media['url'] );

                    if ( isset( $urlParts[0] ) && $urlParts[0] != '' )
                        $media['ident'] = $urlParts[0];
                    else
                        unset( $media['ident'] );
                    
                    $media->save($con);
                  }
              }
          }
          else
          {
              $this->saveEmbeddedForms($con, $form->getEmbeddedForms());
          }
      }
  }

 protected function doUpdateObject($values)
  {
    if ( count( $this->poiMediasScheduledForDeletion ) )
     {
       foreach ( $this->poiMediasScheduledForDeletion as $index => $id )
       {
         unset( $values['PoiMedia'][$index] );
         unset( $this->object['PoiMedia'][$index] );
         Doctrine::getTable('PoiMedia')->findOneById( $id )->delete();
       }
     }

     $this->getObject()->fromArray( $values );
  }


  private function configureVendorPoiCategoryWidget()
  {
    $widget = new widgetFormPoiVendorCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->widgetSchema[ 'vendor_poi_category_list' ] = $widget;

    $validator = new validatorVendorPoiCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->validatorSchema[ 'vendor_poi_category_list' ] = $validator;
  }
}
