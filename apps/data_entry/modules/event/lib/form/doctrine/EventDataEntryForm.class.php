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

    /* images */
    $this->embedRelation('EventMedia');

    $this->embedForm( 'EventOccurrenceCollectionForm',  new EventOccurrenceCollectionForm( $this->getObject() ) );

    $this->embedForm( 'AddEventOccurrenceForm',  new AddEventOccurrenceForm( $this->getObject() ) );
    $this->validatorSchema[ 'AddEventOccurrenceForm' ]   = new AddEventOccurrenceValidatorSchema( );

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
              if ( $form->getObject() instanceof EventMedia )
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

  public function save( $con = null )
  {

     $values = $this->getValues();

     $event = parent::save( $con );

     $addOccurrencesData = $values ['AddEventOccurrenceForm'];

     $eventId = $event[ 'id' ];

     $vendor =Doctrine::getTable( 'Vendor' )->find( $this->user->getCurrentVendorId() );

     $poiId = $addOccurrencesData[ 'poi_id' ];

      //lets create the occurrences if only Never option wasn't selected
     if( $addOccurrencesData[ 'recurring_dates' ]['recurring_freq'] != 'never' )
     {
         $occurrenceDates =  $this->getOccurrenceDates( $values ['AddEventOccurrenceForm'] ) ;

         foreach ($occurrenceDates as $date)
         {
              $vendorOccurrenceId = $eventId . '_' .$poiId . '_' .$date ;
              $occurrence = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceId( $vendorOccurrenceId );

              if( !$occurrence )
              {
                $occurrence = new EventOccurrence();
                $occurrence[ 'vendor_event_occurrence_id' ] = $vendorOccurrenceId;
              }
              $occurrence[ 'poi_id' ]       = $poiId;
              $occurrence[ 'Event' ]        = $event;
              $occurrence[ 'start_date' ]   = $date;
              //$occurrence[ 'end_date' ]     = $date;
              $occurrence[ 'start_time' ]   = $addOccurrencesData [ 'start_time' ] [ 'hour'] . ':' . $addOccurrencesData [ 'start_time' ] [ 'minute'] .':00';
              //$occurrence[ 'end_time' ]     = $addOccurrencesData[ 'end_time' ];
              $occurrence[ 'utc_offset' ]   = $vendor->getUtcOffset();
              $occurrence->save();
         }

     }
     else
     {
          if( !empty( $poiId ) )
          {
              $vendorOccurrenceId = $eventId . '_' .$poiId . '_' .$addOccurrencesData [ 'start_date' ] ;


              $occurrence = Doctrine::getTable( 'EventOccurrence' )->findOneByVendorEventOccurrenceId( $vendorOccurrenceId );

              if( !$occurrence )
              {
                $occurrence = new EventOccurrence();
                $occurrence[ 'vendor_event_occurrence_id' ] = $vendorOccurrenceId;
              }
              $occurrence[ 'poi_id' ]       = $poiId;
              $occurrence[ 'Event' ]        = $event;
              $occurrence[ 'start_date' ]   = $addOccurrencesData [ 'start_date' ];
              //$occurrence[ 'end_date' ]     = $addOccurrencesData [ 'end_date' ];
              $occurrence[ 'start_time' ]   = $addOccurrencesData [ 'start_time' ] [ 'hour'] . ':' . $addOccurrencesData [ 'start_time' ] [ 'minute'] .':00' ;
              //$occurrence[ 'end_time' ]     = $addOccurrencesData[  'end_time' ];
              $occurrence[ 'utc_offset' ]   = $vendor->getUtcOffset();
              $occurrence->save();

          }
     }

     return $event;
  }

  protected function getOccurrenceDates(  $addEventOccurrenceFormParams = array() )
  {
        if( empty( $addEventOccurrenceFormParams [ 'start_date' ] ) )
        {
            return array();
        }

        if( empty( $addEventOccurrenceFormParams [ 'poi_id' ] ) )
        {
            return array();
        }

        $occ = array();

        $occUTS = array();

        $ds = $addEventOccurrenceFormParams [ 'recurring_dates' ];

        if( !isset( $ds['recurring_daily_except']  ) )
        {
           $ds['recurring_daily_except']  = array();
        }
        if ( $ds === NULL || empty( $ds ) )
        {
            return null;
        }

        $isRecurring = $ds [ 'recurring_freq' ] != 'never';

        if( $isRecurring == false )
        {
            return array();
        }

        $dateStart = explode( '-', $addEventOccurrenceFormParams[ 'start_date' ] );

        $dateStartUTS = mktime( 0, 0, 0, $dateStart[1], $dateStart[2], $dateStart[0] );

        if( $isRecurring )
        {
            $dateEnd   = $ds['recurring_until'];
            if( empty( $dateEnd ) )
            {
                $dateEnd =date( 'Y-m-d' , strtotime( '+ 3 months' ) );
            }

        }else
        {
            //$dateEnd   = ( $listing['end_date'] != '0000-00-00' ) ? $listing['end_date'] : $listing['start_date'];
            $dateEnd   = $addEventOccurrenceFormParams[ 'event_date' ] ;
        }

        $dateEnd = explode( '-', $dateEnd );

        $dateEndUTS = mktime( 0, 0, 0, $dateEnd[1], $dateEnd[2], $dateEnd[0] );

        $recDateEnd = explode( '-', $ds['recurring_until'] );

        $recDateEndUTS = mktime( 0, 0, 0, $recDateEnd[1], $recDateEnd[2], $recDateEnd[0] );

        // create all dates between start and end date
        if ( ( $isRecurring != 1 ) && $dateStartUTS == $dateEndUTS )
        {
            $occUTS[] = date( 'Y-m-d', $dateStartUTS );
            $dayUTS[ date( 'Y-m-d', $dateStartUTS) ] = date( 'Y-m-d', $dateStartUTS );
        }
        elseif ( $isRecurring != 1 )
        {

            $dayUTS = $dateStartUTS;
            do
            {
                $occUTS[] = date( 'Y-m-d', $dayUTS);
                // day plus one day
                $dayUTS = $dayUTS + 60*60*24;


            } while ( $dayUTS <= $dateEndUTS );

        }
        elseif ( $isRecurring == 1 && $ds['recurring_freq'] == 'daily' )
        {

            // loop through every day to create the actual occurrences
            $dayUTS = null;
            $occUTS = $dateStartUTS;
            do
            {
                if ( !in_array( strtolower( date( 'l', $occUTS ) ), $ds['recurring_daily_except'] ) )
                    $dayUTS[ date( 'Y-m-d', $occUTS) ] = date( 'Y-m-d', $occUTS );

                // day plus one day
                $occUTS = $occUTS + 60*60*24;

            } while ( $occUTS <= $recDateEndUTS );


        }
        elseif ( $isRecurring == 1 && $ds['recurring_freq'] == 'weekly' )
        {

            $weeksUTS = $dateStartUTS;
            // Create the weeks where the event happens
            do
            {
                $weeks[] = date( 'W', $weeksUTS );

                // plus recurring_weekly_week_number times one week
                $weeksUTS = $weeksUTS + ( 60*60*24*7 * $ds['recurring_weekly_week_number'] );



            } while ( $weeksUTS <= $recDateEndUTS );


            // now loop through every day to create the actual occurrences
            $dayUTS = null;
            $occUTS = $dateStartUTS;
            do
            {
                if ( in_array( strtolower( date( 'l', $occUTS ) ), $ds['recurring_weekly_days'] ) &&
                     in_array( date( 'W', $occUTS ), $weeks ) )
                {
                    $dayUTS[ date( 'Y-m-d', $occUTS ) ] = date( 'Y-m-d', $occUTS );
                }

                // day plus one day
                $occUTS = $occUTS + 60*60*24;

            } while ( $occUTS <= $recDateEndUTS );



        }
        elseif ( $isRecurring == 1 && $ds['recurring_freq'] == 'monthly' )
        {

            // loop through the months to generate the occurrences

            $occUTS = $dateStartUTS;
            $monthUTS = null;

            do
            {
                // the last friday is not the last friday of the month but
                // the latest past friday, so go to the 1st of next month.
                // otherwise go the the last day of the previous month
                if ( $ds['recurring_monthly_position'] == 'last' )
                {
                    $month = date( 'm', $occUTS ) + 1 ;
                    $day = 1;
                }
                else
                {
                    $month = date( 'm', $occUTS );
                    $day = 0;
                }

                $month = mktime( 0, 0, 0, date( 'm', $occUTS ), 0, date( 'Y', $occUTS ) );

                $humanTimeString = $ds['recurring_monthly_position'] . ' ' . $ds['recurring_monthly_weekday'];

                if ( strtotime( $humanTimeString, $month ) >= $dateStartUTS )
                    $monthUTS[] = strtotime( $humanTimeString, $month );

                // create the month for the occurrence
                $occUTS = mktime( 0, 0, 0, date( 'm', $occUTS ) + $ds['recurring_monthly_month_number'], date( 'd', $occUTS ), date( 'Y', $occUTS ) );



            } while ( $occUTS <= $recDateEndUTS );

            foreach ($monthUTS as $ts)
            {
                $dayUTS[] = date( 'Y-m-d', $ts);

            }

        }

        foreach ( $dayUTS as $ts )
        {
            $occ[] = $ts;
        }

        return $occ;

    }
}
