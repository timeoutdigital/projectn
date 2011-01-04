<?php

class reSaveModelPoiFormatPhoneTask extends sfBaseTask
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
      new sfCommandOption('model', null, sfCommandOption::PARAMETER_REQUIRED, 'The city to run the resave','Poi'),

    ));

    $this->namespace        = 'projectn';
    $this->name             = 'reSaveModelPoiFormatPhone';
    $this->briefDescription = '';
    $this->detailedDescription =  '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $allowedOptions = array( 'Poi' );

    if( !in_array( $options[ 'model' ], $allowedOptions ) )
    {
        throw new Exception( 'Invalid model given! use --model=Poi  ');
    }

    if( $options['city'] )
    {
        if( !in_array( $options[ 'city' ], array( 'moscow', 'saint petersburg', 'omsk', 'almaty', 'novosibirsk', 'krasnoyarsk', 'tyumen' ) ) )
        {
            throw new Exception("vendor {$options[ 'city' ]} is not Russian vendor");
        }

        $this->_vendor = Doctrine::getTable('Vendor')->findOneByCity( $options['city'] );
        if( !$this->_vendor )
        {
             throw new Exception( "Vendor couldn't be found with the given city parameter. given : " . $options[ 'city' ] );
        }
    } else {
        throw new Exception ( "No vendor Given, Please specify a vendor name");
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
            $object['phone'] = $this->getFormattedAndFixedPhone( $object['phone'] );
            $object['phone2'] = $this->getFormattedAndFixedPhone( $object['phone2'] );
            $object->save();
        }

        $i++;

        $objects->free( );

    } while ( $pager->getLastPage() >= $i );


  }

  private function getFormattedAndFixedPhone( $phoneNumber )
  {
      if( $phoneNumber == null )
        {
            return null;
        }

        // Remov Extensions and Brack contents
        $phoneFixer = new phoneNumberFixer( $phoneNumber);
        $phoneFixer->removeBracketContents();
        $phoneFixer->removeExtensionNumber();
        $phoneNumber = $phoneFixer->getPhoneNumber();

        // Normal Telephone number Length = 7
        // Area/City/Mobile code length = 3 (821, 911, 921, 951 etc...)
        // hence we should have have maximum of 7 numbers without area code or 10 digits with area code
        // sometime russian cities have 8 front of numbers or +7 front of it,
        // bcz you will need 8 to dial from one city to another inside russia

        // Remove everything otherthan Number
        $phoneNumber = trim( preg_replace( "/[^0-9]+/", "", $phoneNumber ) );

        if( strlen( $phoneNumber ) == 7 )
        {
            // add Area code to the Number and Return
            return $this->params['phone']['areacode'] . $phoneNumber;
        } else if ( strlen( $phoneNumber ) >= 10 )
        {
            // Get the LAST 10 Digits ( 7 Phone numbers and 3 City code = 10 ) and return
            return substr( $phoneNumber, -10 );
        } else {
            $this->logSection( 'Poi' ,  'Invalid Telephone Number: ' .  $phoneNumber );
        }

        return null; // Don't return phone number when unable to format the numbers
  }



}
