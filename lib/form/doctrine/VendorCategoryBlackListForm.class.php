<?php

/**
 * VendorCategoryBlackList form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class VendorCategoryBlackListForm extends BaseVendorCategoryBlackListForm
{
  public function configure()
  {
      // Get vendors AKey=> name and sort A-z
      $vendors_list = Doctrine::getTable( 'Vendor' )->findAll( 'KeyValue' );
      asort( $vendors_list );

      // override the vendors with new choices
      $this->widgetSchema['vendor_id'] = new sfWidgetFormChoice( array( 'choices' =>  $vendors_list ) );

      // Getuser and current Filter for Blacklist categories, this will make produce life much easier as
      // vendors will be consistance in Filter and Add pages
      $user = sfContext::getInstance()->getUser();
      $filters = $user->getAttribute( 'category_black_list.filters', array(), 'admin_module' );
      
      if( isset( $filters['vendor_id']) )
      {
          var_dump( $filters['vendor_id'] );
          $this->widgetSchema['vendor_id']->setDefault(intval( $filters['vendor_id'] ) );
      }
  }

  public function  doSave($con = null) {
        parent::doSave($con);

        // Update Filter based on Last Used vendor
        $vendor_id =  $this->getValue( 'vendor_id' );
        if( is_numeric($vendor_id) && $vendor_id > 0 )
        {
            $filters = sfContext::getInstance()->getUser()->getAttribute( 'category_black_list.filters', array(), 'admin_module' );
            $filters['vendor_id'] = intval( $vendor_id );
            sfContext::getInstance()->getUser()->setAttribute('category_black_list.filters', $filters, 'admin_module' );
        }
    }
}
