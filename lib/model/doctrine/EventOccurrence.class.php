<?php

/**
 * EventOccurrence
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class EventOccurrence extends BaseEventOccurrence
{
  /**
  * Attempts to fix and / or format fields, e.g. url
  */
  public function preSave( $event )
  {

     if( $this['booking_url'] != '')
     {
        $this['booking_url'] = stringTransform::formatUrl($this['booking_url']);
     }

  }

//  public function save( Doctrine_Connection $conn = null)
//  {
//    if( $this->isNew() && $this->equivalentExistsInDatabase() )
//    {
//      return;
//    }
//
//    parent::save( $conn );
//  }
//
//  public function equivalentExistsInDatabase()
//  {
//    return $this->getTable()->hasEquivalent( $this );
//  }
}
