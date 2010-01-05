<?php
/**
 * Base class or XML feeds.
 *
 *
 */
class processXml
{
    private $sourceFile;
    private $events;
    private $venues;
    private $totalEvents;
    private $totalVenues;


    /**
     * Constructor
     */
    public function  __construct($souceFile)
    {
        $this->sourceFile = $sourceFile;
    }

    public function setEvents(array $events)
    {
        $this->events = $events;
        $this->totalEvents = count($events);
    }
    
    public function setVenues(array $venues)
    {
        $this->venues = $venues;
        $this->totalVenues = count($venues);
    }

}
?>