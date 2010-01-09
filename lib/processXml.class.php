<?php
/**
 * Base class or XML feeds.
 *
 *
 */
class processXml
{
   
    public $xmlObj;
    private $events;
    private $venues;
    private $totalEvents;
    private $totalVenues;


    /**
     *
     * @assert ('sd.xml')
     *
     */
    public function  __construct($sourceFile)
    {
      if(file_exists($sourceFile))
      {
        $this->xmlObj = simplexml_load_file($sourceFile);
        return true;
      }
      else
      {
        return false;
      }
    }

    
    /**
     * Sets the xpath to the events and the total amount
     *
     * @param string $eventsPath The xpath to events
     */
    public function setEvents($eventsPath)
    {
      $this->events = $this->xmlObj->xpath($eventsPath);
      $this->totalEvents = count($events);
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
        $this->totalVenues = count($venues);
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