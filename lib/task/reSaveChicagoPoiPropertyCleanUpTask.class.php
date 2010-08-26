<?php

class reSaveChicagoPoiPropertyCleanUpTask extends sfBaseTask
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
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'reSaveChicagoPoiPropertyCleanUp';
    $this->briefDescription = '';
    $this->detailedDescription =  '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // Get Vendor
    $this->_vendor = Doctrine::getTable('Vendor')->findOneByCity( 'chicago' );
    if( !$this->_vendor )
    {
         throw new Exception( "Vendor couldn't be found with the given city 'Chicago'" );
    }
    

    $this->reSaveModel( $this->_vendor );
  }



  /**
   * saves the records using pager
   *
   * @param string $model
   * @param Vendor $vendor
   */
  protected function reSaveModel( Vendor $vendor )
  {
      // create the Query
    $selectQuery = Doctrine_Query::create( )
                 ->select( '*' )
                 ->from( 'Poi m' )
                 ->where( 'm.vendor_id = ?' , $vendor[ 'id' ] );

    // Paggination Count
    $i = 1;
    do
    {
        $pager = new Doctrine_Pager( $selectQuery, $i, 250 );

        $objects = $pager->execute();

        foreach ( $objects as $object )
        {
            $prices = array();
            $propertyCollection = new Doctrine_Collection( Doctrine::getTable( 'PoiProperty' ) );
            
            foreach( $object[ 'PoiProperty' ] as $existingProperty)
            {
                // Check for Price lookup
                if( $existingProperty[ 'lookup' ] == 'price' )
                {
                    if( trim( $existingProperty[ 'value' ]  ) != '' && !in_array( trim( $existingProperty[ 'value' ]  ), array( 'USD', '-' ) ) )
                    {
                        $prices[]   = trim( $existingProperty[ 'value' ]  );
                    }

                    // Delete from POI Property
                    //$existingProperty->delete(); // @todo: it may be a bad Idea to delete before Saving POI?
                }
                else
                {
                    $propertyCollection[] = $existingProperty;
                }
            }
            $object['PoiProperty'] = $propertyCollection;

            if( count( $prices ) > 0 )
            {
                // add to price Field
                $object['price_information']    = stringTransform::concatNonBlankStrings( ', ', array_unique( $prices ) );

                echo PHP_EOL;
                $this->logSection('Poi' , 'Saving Poi : ' . $object['id'] . ' for price in Poi Property' );
                
                // Save changes
                $object->save();
            }

            echo '.';
        }

        $i++; // Next page

        $objects->free( );

    } while ( $pager->getLastPage() >= $i );


  }



}
