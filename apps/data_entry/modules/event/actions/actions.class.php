<?php

require_once dirname(__FILE__).'/../lib/eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventGeneratorHelper.class.php';

/**
 * event actions.
 *
 * @package    sf_sandbox
 * @subpackage event
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends autoEventActions
{

  private $user;

  public function preExecute()
  {
     parent::preExecute();


     $filters = $this->getFilters() ;
     $this->user = $this->getUser();

     if ( !isset( $filters['vendor_id'] ) || !$this->user->checkIfVendorIdIsAllowed( $filters['vendor_id'] ) )
     {
          $this->setFilters( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
     }
  }

  public function executeAjaxPoiList($request)
  {
    $this->getResponse()->setContentType('application/json');

    $q = Doctrine_Query::create()
                ->select( 'id, poi_name name' )
                ->from('Poi p')
                ->where( 'vendor_id = ?', $this->user->getCurrentVendorId() )
                ->andWhere( 'poi_name LIKE ?', '%' . $request->getParameter('q') . '%' );

    $result = $q->fetchArray();

    $pois = array();
    foreach ( $result as $poi )
    {
        $pois[ $poi['id'] ] = $poi['name'];
    }

    return $this->renderText(json_encode($pois));
  }

  public function executeAjaxEventList($request)
  {
    $this->getResponse()->setContentType('application/json');

    $q = Doctrine_Query::create()
                ->select( 'id, name' )
                ->from('Event e')
                ->where( 'vendor_id = ?', $this->user->getCurrentVendorId() )
                ->andWhere( 'name LIKE ?', '%' . $request->getParameter('q') . '%' );

    $result = $q->fetchArray();

    $pois = array();
    foreach ( $result as $poi )
    {
        $pois[ $poi['id'] ] = $poi['name'];
    }

    return $this->renderText(json_encode($pois));
  }

  public function executeShow(sfWebRequest $request)
  {
        $this->redirect('@event');
  }
  public function executeEdit(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        parent::executeEdit( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change this record' );
        $this->redirect('@event');
    }
  }

  public function executeDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfRecordPermissionsByRequest( $request ) )
    {
        $request->checkCSRFProtection();

        $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

        $event = $this->getRoute()->getObject();

        $this->deleteRelations( $event );

        if ($event->delete())
        {
          $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
        }
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to delete this record' );
    }

    $this->redirect('@event');
  }

  public function executeBatch(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        parent::executeBatch( $request );
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change/delete some or all of the records selected' );
        $this->redirect('@event');
    }
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    if ( $this->getUser()->checkIfMultipleRecordsPermissionsByRequest( $request ) )
    {
        $ids = $request->getParameter('ids');

        $records = Doctrine_Query::create()
          ->from('event')
          ->whereIn('id', $ids)
          ->execute();

        foreach ($records as $record)
        {
          $this->deleteRelations( $record );
          $record->delete();
        }

        $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    }
    else
    {
        $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change/delete some or all of the records selected' );
    }

    $this->redirect('@event');
  }

  private function deleteRelations( Event $event )
  {
    //delete vendor category references
    $vendorCategoryIds = array();
    foreach( $event[ 'VendorEventCategory' ] as $vendorCategory )
    {
        $vendorCategoryIds[] =  $vendorCategory['id'];
    }
    $event->unlink( 'VendorEventCategory', $vendorCategoryIds );
    $event->save();

    //delete occurrences
    $event[ 'EventOccurrence' ]->delete();

    //delete meta
    $event[ 'EventMeta' ]->delete();

    //delete media
    foreach( $event[ 'EventMedia' ] as $eventMedia )
    {
        $file = $eventMedia->getFileUploadStorePath() . '/' . $eventMedia[ 'url' ];

        if ( is_file($file) )
        {
            unlink($file);
        }
        $eventMedia->delete();
    }
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {

    echo "<pre>";
    $eventOccurrence = $request->getParameter( 'event' );
   // print_r( $eventOccurrence );

    print_r( $eventOccurrence ['AddEventOccurrenceForm'] );
   // print_r( $eventOccurrence ['EventOccurrenceCollectionForm'] );

    $occurrenceDates =  $this->getOccurrenceDates( $eventOccurrence ['AddEventOccurrenceForm'] ) ;

   // print_r( $occurrenceDates );

    //the form is sending extra parameters for event model,so after using the data in AddEventOccurrenceForm to get the dates
    //we need to get rid of them
    //unset(  $eventOccurrence ['AddEventOccurrenceForm']  );

    //unset(  $eventOccurrence ['EventOccurrenceCollectionForm']  );

    //$request->setParameter( 'event' ,$eventOccurrence );

    $parameters = $request->getParameter($form->getName());

    //$parameters[ 'EventOccurrence' ] = array();
    $form->bind( $parameters , $request->getFiles($form->getName()));

    $event[ 'EventOccurrence' ] = new Doctrine_Collection( 'EventOccurrence' );

    var_dump( count(  $event [ 'EventOccurrence' ]  ) );

    $occurrence = $event[ 'EventOccurrence' ][0];

    var_dump( $occurrence[ 'vendor_event_occurrence_id' ] );

    if ( $form->isValid() )
    {
      $notice = $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.';

      try
      {
         $event = $form->save();
         //lets create the occurrences if only Never option wasn't selected
         $addOccurrencesData = $eventOccurrence ['AddEventOccurrenceForm'];
         $eventId = $event[ 'id' ];

         $vendor =Doctrine::getTable( 'Vendor' )->find( $this->user->getCurrentVendorId() );
         $poiId = $addOccurrencesData[ 'poi_id' ];

         if( $addOccurrencesData[ 'recurring_dates' ]['recurring_freq'] != 'other' )
         {
             if( $addOccurrencesData [ 'start_date' ] != $addOccurrencesData [ 'end_date' ] )
             {
                $this->getUser()->setFlash('error', "You can add multiple occurrences if only 'Start dat'e and 'End date' is same!Event is saved but occurrence(s) aren't saved ");

             }
             else
             {
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
                      $occurrence[ 'end_date' ]     = $date;
                      $occurrence[ 'start_time' ]   = $addOccurrencesData[ 'start_time' ];
                      $occurrence[ 'end_time' ]     = $addOccurrencesData[ 'end_time' ];
                      $occurrence[ 'utc_offset' ]   = $vendor->getUtcOffset();
                      $occurrence->save();
                 }
             }
         }
         else
         {
            //create only one occurrence if poi_id, start and end dates and times are given
            if( !empty( $addOccurrencesData [ 'poi_id' ] ) &&
                !empty( $addOccurrencesData [ 'start_date' ] ) &&
                !empty( $addOccurrencesData [ 'end_date' ] ) &&
                !empty( $addOccurrencesData [ 'start_time' ] ) &&
                !empty( $addOccurrencesData [ 'end_time' ] ) )
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
                  $occurrence[ 'end_date' ]     = $addOccurrencesData [ 'end_date' ];
                  $occurrence[ 'start_time' ]   = $addOccurrencesData [ 'start_time' ];
                  $occurrence[ 'end_time' ]     = $addOccurrencesData[  'end_time' ];
                  $occurrence[ 'utc_offset' ]   = $vendor->getUtcOffset();
                  $occurrence->save();

             }
         }

      }
      catch (Doctrine_Validator_Exception $e)
      {

        $errorStack = $form->getObject()->getErrorStack();

        $message = get_class($form->getObject()) . ' has ' . count($errorStack) . " field" . (count($errorStack) > 1 ?  's' : null) . " with validation errors: ";

        foreach ($errorStack as $field => $errors)
        {
            $message .= "$field (" . implode(", ", $errors) . "), ";
        }
        $message = trim($message, ', ');

        $this->getUser()->setFlash('error', $message);

        return sfView::SUCCESS;
      }


      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $event)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $notice.' You can add another one below.');

        $this->redirect('@event_new');
      }
      else
      {
        $this->getUser()->setFlash('notice', $notice);

        $this->redirect(array('sf_route' => 'event_edit', 'sf_subject' => $event));
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }

    $event = $form->getObject();

    var_dump( $event[ 'name' ] );

    //print_r( $form->getErrorSchema( ) );
    echo "</pre>";

  }

  protected function getOccurrenceDates(  $addEventOccurrenceFormParams = array() )
  {
        if( empty( $addEventOccurrenceFormParams [ 'start_date' ] ) )
        {
            return array();
        }
        $occ = array();

        $occUTS = array();

        $ds = $addEventOccurrenceFormParams [ 'recurring_dates' ];

        if ( $ds === NULL || empty( $ds ) )
        {
            return null;
        }

        //other is the value for the checkbox saying "Never"
        $isRecurring = $ds [ 'recurring_freq' ] != 'other';

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
