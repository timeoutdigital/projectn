<?php

/**
 * geocode_ui filter form.
 *
 * @package    filters
 * @subpackage geocode_ui *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class PoiDataEntryFormFilter extends BasePoiFormFilter
{
    private $user;

    public function configure()
    {

      $this->user = sfContext::getInstance()->getUser();

      $this->widgetSchema['list'] =  new sfWidgetFormFilterInput(); //array('list' => array('0' => 'Choose', '1' => "First"))
      $this->validatorSchema['list'] = new  sfValidatorPass(array ('required' => false));
      $this->setVendorWidget();

      parent::configure();

    }
    
   /**
     * list parameter is not part of the doctrine record but by adding it to the filter array in session, doctrine calls
     * this method is called
     *
     * @param $query
     * @param $field
     * @param $value
     * @return Doctrine_Query
     */
    public function addListColumnQuery( $query, $field, $value)
    {
        switch ( $value )
        {
            case 'non-geocoded':
                    $query->andWhere( '(latitude is NULL AND longitude is NULL) OR (latitude = 0 AND longitude = 0)' );
                break;

            case 'geocoded':
                    $query->leftJoin( $query->getRootAlias() . '.PoiMeta m WITH m.lookup = "Geocode_accuracy"' );
                    $query->andWhere( '(latitude is NOT NULL AND longitude is NOT NULL) AND (latitude != 0 AND longitude != 0) ' );
                    $query->andWhere( '(m.value is null OR m.value != 10)' );
                break;
           case 'manual':
                    $query->innerJoin( $query->getRootAlias() . '.PoiMeta m WITH m.lookup = "Geocode_accuracy"' );
                    $query->andWhere( '(latitude is NOT NULL AND longitude is NOT NULL) AND (latitude != 0 AND longitude != 0) ' );
                    $query->andWhere( 'm.value = 10 ' );
                break;
        }

        return $query;
    }

    private function setVendorWidget()
    {
      $permittedVendorCitiesChoices = $this->user->getPermittedVendorCities( true );
      $this->widgetSchema ['vendor_id'] = new sfWidgetFormSelect( array( 'choices' => $permittedVendorCitiesChoices ) );
      $this->validatorSchema ['vendor_id'] = new sfValidatorChoice( array( 'choices' => array_keys( $permittedVendorCitiesChoices ), 'required' => true ) );
    }

    public function addVendorIdColumnQuery($query, $field, $value)
    {
             
      if( $value == 0 )
      {
          $permittedVendorCitiesChoices = $this->user->getPermittedVendorCities( true );

          $choices = implode( ',',  array_keys( $permittedVendorCitiesChoices ) );
          
          $query->andWhere( $query->getRootAlias() . '.vendor_id in ('.$choices.')' );
          
      }else{
        $this->user->setCurrentVendorById( $value );
        $query->andWhere( $query->getRootAlias() . '.vendor_id = ?', $value );
      }
      
      return $query;

    }

    public function addPoiNameColumnQuery($query, $field, $value)
    {
        if( !is_array($value) || !$value['text'] || empty($value['text']))
            return $query;

        $query->andWhere( $query->getRootAlias() . '.poi_name LIKE ?',  '%' . $value['text'] . '%');

        return $query;
    }

    /**
     * adding the list to the fields list
     *
     * @return unknown
     */
    public function getFields()
    {
        $fields = parent::getFields();
        $fields['list'] = 'custom';
        return $fields;
    }

}
