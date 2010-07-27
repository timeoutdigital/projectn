<?php

/**
 * Poi filter form.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PoiDataEntryFormFilter extends BasePoiFormFilter
{

  private $user;

  public function configure()
  {
      $this->user = sfContext::getInstance()->getUser();
      $this->setVendorWidget();
  }

  private function setVendorWidget()
  {
      $permittedVendorCitiesChoices = $this->user->getPermittedVendorCities( true );

      $this->widgetSchema ['vendor_id'] = new sfWidgetFormSelect( array( 'choices' => $permittedVendorCitiesChoices ) );
      $this->validatorSchema ['vendor_id'] = new sfValidatorChoice( array( 'choices' => array_keys( $permittedVendorCitiesChoices ), 'required' => true ) );
   }

  public function addVendorIdColumnQuery($query, $field, $value)
  {
      $this->user->setCurrentVendorById( $value );
      $query->andWhere( $query->getRootAlias() . '.vendor_id = ?', $value );
  }

}
