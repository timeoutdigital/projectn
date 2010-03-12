<?php

/**
 * VendorPoiCategory form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class VendorPoiCategoryForm extends BaseVendorPoiCategoryForm
{
  public function configure()
  {
  }

  public function setup()
  {
      parent::setup();

      //remove the poi list as we dont want to see it and not to update the data
      //either, which it would if only hidden in the generator yaml. at least as
      //of symfony 1.4
      $this->offsetUnset( 'poi_list' );
  }
}
