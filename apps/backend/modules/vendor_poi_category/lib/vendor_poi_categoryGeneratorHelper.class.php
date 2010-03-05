<?php

/**
 * vendor_poi_category module helper.
 *
 * @package    sf_sandbox
 * @subpackage vendor_poi_category
 * @author     Your name here
 * @version    SVN: $Id: helper.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class vendor_poi_categoryGeneratorHelper extends BaseVendor_poi_categoryGeneratorHelper
{

  /**
   * @todo keep this file in mind when upgrading, its basically a full overload
   * of the standard function to fix a bug (http://trac.symfony-project.org/ticket/5330)
   */
  public function linkTo_saveAndList($object, $params)
  {
      return '<li class="sf_admin_action_save_and_list"><input type="submit" value="'.__($params['label'], array(), 'sf_admin').'" name="_save_and_list" /></li>';
  }

}
