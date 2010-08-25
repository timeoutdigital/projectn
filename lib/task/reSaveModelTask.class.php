<?php

class reSaveModelTask extends sfBaseTask
{
  /**
   *
   * @var Vendor
   */
  private $_vendor = null;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name','backend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
      new sfCommandOption('city', null, sfCommandOption::PARAMETER_OPTIONAL, 'The city to run the resave',null),
      new sfCommandOption('model', null, sfCommandOption::PARAMETER_REQUIRED, 'The city to run the resave',null),

    ));

    $this->namespace        = 'projectn';
    $this->name             = 'reSaveModel';
    $this->briefDescription = '';
    $this->detailedDescription =  '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $allowedOptions = array( 'Poi', 'Event', 'Movie' );

    if( !in_array( $options[ 'model' ], $allowedOptions ) )
    {
        throw new Exception( 'Invalid model given! use --model=Poi/Event/Movie  ');
    }

    if( $options['city'] )
    {
        $this->_vendor = Doctrine::getTable('Vendor')->findOneByCity( $options['city'] );
        if( !$this->_vendor )
        {
             throw new Exception( "Vendor couldn't be found with the given city parameter. given : " . $options[ 'city' ] );
        }
    }

    $this->reSaveModel( $options[ 'model' ] , $this->_vendor );
  }



  /**
   * saves the records using pager
   *
   * @param string $model
   * @param Vendor $vendor
   */
  protected function reSaveModel( $model , Vendor $vendor = null  )
  {
    $selectQuery = Doctrine_Query::create( )
                 ->select( '*' )
                 ->from( $model . ' m' );

    if ( $vendor )
    {
         $selectQuery->where( 'm.vendor_id = ?' , $vendor[ 'id' ] );
    }

    $i = 1;

    do
    {
        $pager = new Doctrine_Pager( $selectQuery, $i, 250 );

        $objects = $pager->execute();

        foreach ( $objects as $object )
        {
            $this->logSection( $model , 'saving record with the id : ' . $object['id'] );
            $object->save();
        }

        $i++;

        $objects->free( );

    } while ( $pager->getLastPage() >= $i );


  }



}
