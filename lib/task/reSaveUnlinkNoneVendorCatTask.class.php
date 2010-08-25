<?php

class reSaveUnlinkNoneVendorCatTask extends sfBaseTask
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
    $this->name             = 'reSaveUnlinkNoneVendorCat';
    $this->briefDescription = '';
    $this->detailedDescription =  '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $allowedOptions = array( 'Poi', 'Event' );

    if( !in_array( $options[ 'model' ], $allowedOptions ) )
    {
        throw new Exception( 'Invalid model given! use --model=Poi/Event  ');
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
  protected function reSaveModel( $model , Vendor $vendor )
  {
      // create the Query
    $selectQuery = Doctrine_Query::create( )
                 ->select( '*' )
                 ->from( $model . ' m' )
                 ->where( 'm.vendor_id = ?' , $vendor[ 'id' ] );

    // Set the Category Model
    $vendorModelCategory    = ( strtolower( $model ) == 'poi' ) ? 'VendorPoiCategory' : 'VendorEventCategory';

    // Paggination Count
    $i = 1;
    do
    {
        $pager = new Doctrine_Pager( $selectQuery, $i, 250 );

        $objects = $pager->execute();

        foreach ( $objects as $object )
        {
            foreach( $object[ $vendorModelCategory ] as $existingCategory )
            {
                // This will unlink all vendor category relationships that dont match the poi vendor.
                if( $existingCategory[ 'vendor_id' ] != $vendor['id'] )
                {
                    $this->unlinkInDb( $vendorModelCategory, array( $existingCategory[ 'id' ] ) );
                    $this->logSection( $model , 'Unlinking Category for Model id: ' . $object['id'] );
                }
            }            
            // Save
            $object->save();
        }

        $i++; // Next page

        $objects->free( );

    } while ( $pager->getLastPage() >= $i );


  }



}
