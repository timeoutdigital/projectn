<?php
class runnerDataEntryTask extends runnerTask
{
    protected function configure()
    {
        parent::configure();
        $this->name             = 'runnerDataEntry';
    }

    protected function getTaskArray()
    {
        $taskArray = array (
                    'export' => array(
                            'mumbai' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) ),
                            'delhi' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) ),
                            'bangalore' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) ),
                            'pune' => array( 'language' => 'en-GB', 'type' => array( 'poi', 'event', 'movie' ) )
                    ),
                 );

        return $taskArray;
    }
}
