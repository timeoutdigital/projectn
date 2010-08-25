<?php

class reSaveItemsWithMoreThanOneMediaTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'reSaveItemsWithMoreThanOneMedia';
    $this->briefDescription = '';
    $this->detailedDescription =  'resaves Pois, events and movies if they have more than one image';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $config = $this->createConfiguration('backend', $options['env']);

    // get the doctrine database connection
    $manager  = new sfDatabaseManager($this->configuration);
    $dbConn = $manager->getDatabase('project_n')->getDoctrineConnection();

    //find the pois with more than one media -----------------------------------------
    $query = 'SELECT * from ( SELECT poi_id ,  count(*) as cnt  FROM poi_media  group by poi_id ) as subq where cnt > 1';
    $results = $dbConn->execute( $query );

    foreach ($results as $result)
    {
        $poi = Doctrine::getTable( 'Poi' )->find( $result['poi_id']  );
        $poi->save();
    }

    //find the events with more than one media ----------------------------------------
    $query = 'SELECT * from ( SELECT event_id,  count(*) as cnt  FROM event_media  group by event_id ) as subq where cnt > 1';
    $results = $dbConn->execute( $query );

    foreach ($results as $result)
    {
        $event = Doctrine::getTable( 'Event' )->find( $result['event_id']  );
        $event->save();
    }

    //find the movies with more than one media------------------------------------------
    $query = 'SELECT * from ( SELECT movie_id,  count(*) as cnt  FROM movie_media  group by movie_id ) as subq where cnt > 1';
    $results = $dbConn->execute( $query );

    foreach ($results as $result)
    {
        $movie = Doctrine::getTable( 'Movie' )->find( $result['movie_id']  );
        $movie->save();
    }
  }
}
