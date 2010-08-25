<?php

class myUser extends sfGuardSecurityUser
{

    /**
    * Initializes the sfGuardSecurityUser object.
    *
    * @param sfEventDispatcher $dispatcher The event dispatcher object
    * @param sfStorage $storage The session storage object
    * @param array $options An array of options
    */
    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
    {
        parent::initialize($dispatcher, $storage, $options);
        $this->setDefaultCurrentVendor();
    }

    public function setCurrentVendorById( $currentVendorId )
    {
        //@todo check before setting
        $this->setAttribute('current_vendor', $this->getPermittedVendorCityByVendorId( $currentVendorId ) );
    }

    public function getCurrentVendorId()
    {
        $currentVendor = $this->getAttribute( 'current_vendor' );

        if ( $currentVendor !==  null )
            return $currentVendor[ 'id' ];
    }

    public function getCurrentVendorCity()
    {
        $currentVendor = $this->getAttribute( 'current_vendor' );

        if ( $currentVendor !==  null )
            return $currentVendor[ 'city' ];
    }

    public function getCurrentVendorCountryCodeLong()
    {
        $currentVendor = $this->getAttribute( 'current_vendor' );

        if ( $currentVendor !==  null )
            return $currentVendor[ 'country_code_long' ];
    }

    public function getCurrentVendorLanguage()
    {
        $currentVendor = $this->getAttribute( 'current_vendor' );

        if ( $currentVendor !==  null )
            return $currentVendor[ 'language' ];
    }

    public function getCurrentVendorUtcOffset()
    {
        $currentVendor = $this->getAttribute( 'current_vendor' );

        if ( $currentVendor !==  null )
        {
            $vendorObj = Doctrine::getTable( 'Vendor' )->find( $currentVendor[ 'id' ] );

            if ( $vendorObj !==  false)
            {
                return $vendorObj->getUtcOffset();
            }
        }
    }

    public function getCurrentVendor()
    {
        return $this->getAttribute( 'current_vendor' );
    }

    public function getPermittedVendorCities( $returnSimplified = false )
    {
      if ( 0 < count( $this->getGroupNames() ) )
      {
          $permittedVendors = Doctrine::getTable( 'Vendor' )->createQuery( 'v' )
                                                            ->whereIn( 'city', $this->getGroupNames() )
                                                            ->fetchArray();

          if ( !$returnSimplified )
              return $permittedVendors;

          $permittedVendorCities = array();
          foreach( $permittedVendors as $permittedVendor )
          {
              $permittedVendorCities[ $permittedVendor[ 'id' ] ] = $permittedVendor[ 'city' ];
          }

          return $permittedVendorCities;
      }
    }

    public function getPermittedVendorCityByVendorId( $vendorId )
    {
      if ( 0 < count( $this->getGroupNames() ) )
      {
          $permittedVendor = Doctrine::getTable( 'Vendor' )->createQuery( 'v' )
                                                           ->whereIn( 'city', $this->getGroupNames() )
                                                           ->andWhere( 'id = ' . $vendorId )
                                                           ->fetchArray();

          if ( isset( $permittedVendor[ 0 ] ) )
          {
            return $permittedVendor[ 0 ];
          }
      }
    }

    public function checkIfVendorIdIsAllowed( $vendorId )
    {
        $permittedVendor = $this->getPermittedVendorCityByVendorId( $vendorId );

        if ( $permittedVendor === null )
            return false;
        else
            return true;
    }

    public function checkIfRecordPermissionsByRequest( sfWebRequest $request )
    {
        $recordId = $request->getParameter( 'id' );
        $moduleName = $request->getParameter( 'module' );
  
        if ( $recordId === null || $moduleName === null || !is_numeric( $recordId ) )
        {
            return false;
        }

        return $this->checkRecordPermissions( $moduleName, $recordId );
    }

    public function checkIfMultipleRecordsPermissionsByRequest( sfWebRequest $request )
    {
        $recordIds = $request->getParameter( 'ids' );
        $moduleName = $request->getParameter( 'module' );
      
        foreach( $recordIds as $recordId )
        {
            if ( ! $this->checkRecordPermissions( $moduleName, $recordId ) )
            {
                return false;
            }
        }
        return true;
    }

    public function checkRecordPermissions( $moduleName, $recordId )
    {
        $permittedRecord = Doctrine::getTable( sfInflector::camelize( $moduleName ) )->find( $recordId );

        if ( $permittedRecord === false )
        {
            return false;
        }
        else
        {
            return $this->checkIfVendorIdIsAllowed( $permittedRecord[ 'vendor_id' ] );
        }
    }

    private function setDefaultCurrentVendor()
    {
        if ( $this->isAuthenticated() )
        {
            if ( $this->getCurrentVendor() !== null)
                return;

            $permittedVendorCities = $this->getPermittedVendorCities();

            if ( 0 < count( $permittedVendorCities ) )
            {
                //@todo check before setting
                $this->setAttribute('current_vendor', $permittedVendorCities[ 0 ]);
                return true;
            }

            $this->setFlash ( 'error' , 'Your do not have any valid group assigned to your account. Please speak to your administrator.');
            $this->signOut();
        }
    }

}
