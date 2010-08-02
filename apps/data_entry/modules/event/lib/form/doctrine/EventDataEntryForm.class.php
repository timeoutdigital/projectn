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

  protected $eventOccurencesScheduledForDeletion = array();

  protected $eventMediasScheduledForDeletion = array();


  public function configure()
  {
    $this->user = sfContext::getInstance()->getUser();

    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );

    $this->widgetSchema[ 'vendor_id' ]      = new widgetFormFixedVendorText( array( 'vendor_id'  => $this->user->getCurrentVendorId(), 'vendor_name'  => $this->user->getCurrentVendorCity()  ) );
    $this->validatorSchema[ 'vendor_id' ]   = new validatorSetCurrentVendorId( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );

    $this->widgetSchema[ 'name' ] = new widgetFormInputTextJQueryAutocompleter( array( 'url' => sfContext::getInstance()->getRequest()->getScriptName() . '/event/ajaxEventList' ) );

    //@todo maybe use the jquery calendar
    $this->widgetSchema[ 'review_date' ]      = new sfWidgetFormDate(array());
    $this->validatorSchema[ 'review_date' ]    = new sfValidatorDate(array( 'required' => false ));

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
    //@todo find issue why more than 2 imgs failing to save
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
        'edit_mode'   => false,
        'is_image'    => true,
        'with_delete' => false,
    )));

    $this->embedForm( 'newEventMediaDataEntry', $form );
    
  }


  protected function doBind(array $values)
  {
    if (isset($values['EventOccurrence']))
    {
      foreach ($values['EventOccurrence'] as $i => $eventOccurrenceFields )
      {
        if ( isset($eventOccurrenceFields['event_occurrence_delete']) && $eventOccurrenceFields['id'] )
        {
          $this->eventOccurencesScheduledForDeletion[$i] = $eventOccurrenceFields['id'];
        }
      }
    }

    if (isset($values['EventMedia']))
    {
      foreach ($values['EventMedia'] as $i => $eventMediaFields )
      {
        if ( isset($eventMediaFields['url_delete']) && $eventMediaFields['id'] )
        {
            $this->eventMediasScheduledForDeletion[$i] = $eventMediaFields['id'];
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
      }

      foreach ($forms as $form)
      {
          if ($form instanceof sfFormObject)
          {
              if ( $form->getObject() instanceof EventOccurrence )
              {
                  if ( !in_array($form->getObject()->getId(), $this->eventOccurencesScheduledForDeletion ))
                  {
                    $form->saveEmbeddedForms($con);
                    $form->getObject()->save($con);
                  }
              }
              else if ( $form->getObject() instanceof EventMedia )
              {
                  if ( !in_array($form->getObject()->getId(), $this->eventMediasScheduledForDeletion ))
                  {
                    $form->saveEmbeddedForms($con);
                    $form->getObject()->save($con);
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
    if ( count( $this->eventOccurencesScheduledForDeletion ) )
    {
       foreach ( $this->eventOccurencesScheduledForDeletion as $index => $id )
       {
         unset( $values['EventOccurrence'][$index] );
         unset( $this->object['EventOccurrence'][$index] );
         Doctrine::getTable('EventOccurrence')->findOneById( $id )->delete();
       }
     }

     if ( count( $this->eventMediasScheduledForDeletion ) )
     {
       foreach ( $this->eventMediasScheduledForDeletion as $index => $id )
       {
         unset( $values['EventMedia'][$index] );
         unset( $this->object['EventMedia'][$index] );
         Doctrine::getTable('EventMedia')->findOneById( $id )->delete();
       }
     }

     $this->getObject()->fromArray( $values );
  }


  private function configureVendorEventCategoryWidget()
  {
    $widget = new widgetFormEventVendorCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->widgetSchema[ 'vendor_event_category_list' ] = $widget;

    $validator = new validatorVendorEventCategoryChoice( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
    $this->validatorSchema[ 'vendor_event_category_list' ] = $validator;
  }
}
