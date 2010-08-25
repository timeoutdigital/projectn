<?php

/**
 * VendorPoiCategory form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class VendorPoiCategoryDataEntryForm extends BaseVendorPoiCategoryForm
{
  private $user;

  public function configure()
  {
    $this->user = sfContext::getInstance()->getUser();

    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );

    $this->widgetSchema[ 'vendor_id' ]      = new widgetFormFixedVendorText( array( 'vendor_id'  => $this->user->getCurrentVendorId(), 'vendor_name'  => $this->user->getCurrentVendorCity()  ) );
    $this->validatorSchema[ 'vendor_id' ]   = new validatorSetCurrentVendorId( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
  }
}
