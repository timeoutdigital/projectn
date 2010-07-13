<?php

/**
 * EventDataEntryForm form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventDataEntryForm extends BaseEventForm
{
  
  private $user;

  private $filePath = 'media/event';

  public function configure()
  {
    $this->user = sfContext::getInstance()->getUser();

    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );

    $this->widgetSchema[ 'vendor_id' ]      = new widgetFormFixedVendorText( array( 'vendor_id'  => $this->user->getCurrentVendorId(), 'vendor_name'  => $this->user->getCurrentVendorCity()  ) );
    $this->validatorSchema[ 'vendor_id' ]   = new validatorSetCurrentVendorId( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );

    $this->configureVendorEventCategoryWidget();

    /* occurrences */
    $this->embedRelation('EventOccurrence');

    /* new event occurrence */
    $eventOccurrence = new EventOccurrence();
    $eventOccurrence->Event = $this->getObject();

    $form = new EventOccurrenceForm( $eventOccurrence );

    $form->validatorSchema['start_date']->setOption('required', false);
    $form->validatorSchema['poi_id']->setOption('required', false);

    $this->embedForm( 'newEventOccurrenceDataEntry', $form );

    /* images */
    $this->embedRelation('EventMedia');

    /* new event media */
    $eventMedia = new EventMedia();
    $eventMedia->Event = $this->getObject();

    $form = new EventMediaForm( $eventMedia );

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

    $this->embedForm( 'newEventMediaDataEntry', $form );
  }

  public function saveEmbeddedForms($con = null, $forms = null)
  {
      if (null === $forms)
      {
        $forms = $this->embeddedForms;

        $newEventOccurrenceDataEntry = $this->getValue('newEventOccurrenceDataEntry');
        if ( !isset( $newEventOccurrenceDataEntry['start_date'] ) || !isset( $newEventOccurrenceDataEntry['poi_id'] ) )
        {
            unset($forms['newEventOccurrenceDataEntry'] );
        }

        $newEventMediaDataEntry = $this->getValue('newEventMediaDataEntry');
        if ( !isset( $newEventMediaDataEntry['url']) )
        {
            unset($forms['newEventMediaDataEntry'] );
        }



//        $eventMedias = $this->getValue('EventMedia');
//
//        foreach( $eventMedias as $eventMedia )
//        {
//            if ( isset ($eventMedia['url_delete']) && isset ($eventMedia['id']) )
//            {
//                foreach( $this->object->EventMedia as $eventMediaStored )
//                {
//                    if ( $eventMediaStored['id'] == $eventMedia['id'] )
//                    {
//                       $eventMediaStored->delete();
//
//                       echo $eventMediaStored['id'];
//
//                        //var_export(sfConfig::get('sf_upload_dir') . '/' . $this->filePath. '/' . sha1($eventMediaStored['url']) . $eventMediaStored->getUrl() );
//
////                        if ( $eventMediaStored['url'] != '' )
////                        {
////
////
////                            $file = sfConfig::get('sf_upload_dir') . '/' . $this->filePath. '/' . $eventMediaStored['url'];
////                            if ( is_file( $file  ) )
////                            {
////                                unlink($file);
////                            }
////                        }
////
//                    }
//                }
//            }


        

        

      }

      return parent::saveEmbeddedForms($con, $forms);
  }

//  protected function doSave($con = null)
//  {
//
//
//      $eventMedias = $this->getValue('EventMedia');
//
//        foreach( $eventMedias as $eventMedia )
//        {
//            if ( isset ($eventMedia['url_delete']) && isset ($eventMedia['id']) )
//            {
//                foreach( $this->object->EventMedia as $eventMediaStored )
//                {
//                    if ( $eventMediaStored['id'] == $eventMedia['id'] )
//                    {
//                       $eventMediaStored->delete();
//
//                       echo $eventMediaStored['id'];
//
//                        var_export(sfConfig::get('sf_upload_dir') . '/' . $this->filePath. '/' . sha1($eventMediaStored['url']) . $eventMediaStored->getUrl() );
//exit();
//                        if ( $eventMediaStored['url'] != '' )
//                        {
//
//
//                            $file = sfConfig::get('sf_upload_dir') . '/' . $this->filePath. '/' . $eventMediaStored['url'];
//                            if ( is_file( $file  ) )
//                            {
//                                unlink($file);
//                            }
//                        }
//
//                    }
//                }
//            }
//        }
//
//      parent::doSave();
//  }






  private function configureVendorEventCategoryWidget()
  {
    $widget = new widgetFormEventVendorCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->widgetSchema[ 'vendor_event_category_list' ] = $widget;

    $validator = new validatorVendorEventCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->validatorSchema[ 'vendor_event_category_list' ] = $validator;
  }
}
