<?php
/**
 * Class to parse NY XML feeds
 *
 * @package projectn
 * @subpackage ny.import.lib
 *
 * @author Tim Bowler <timbowler@timeout.com>
 *
 * @copyright Timeout Communications Ltd
 * @version 1.0.1
 *
 */

class processNyXml extends processXml
{

    /**
     * Constructor method
     *
     */
    public function  __construct($sourceFile)
    {
      parent::__construct($sourceFile);
    }


    /**
     * Sets the xpath to the events and the total amount
     *
     * @param string $eventsPath The xpath to events
     */
    public function setEvents($eventsPath)
    {
      $this->events = $this->xmlObj->xpath($eventsPath);
      $this->totalEvents = count($this->events);
      return $this;
    }


    /**
     * Get the events
     *
     * @return array Array of xml objects
     */
    public function getEvents()
    {
      return $this->events;
    }

    /**
     * Set the xpath to venues and counts the total
     *
     * @param array $venues The xpath to venues
     */
    public function setVenues($venuesPath)
    {
        $this->venues = $this->xmlObj->xpath($venuesPath);
        $this->totalVenues = count($this->venues);
        return $this;
    }

     /**
     * Get the venues
     *
     * @return array Array of xml objects
     */
    public function getVenues()
    {
      return $this->venues;
    }
}
?>