<?php

/**
 * Business logi for Poi Categorgies
 *
 *
 * @package projectn
 * @subpackage model
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd 2009
 *
 *
 */
class PoiCategoryTable extends Doctrine_Table
{
   /**
   * Get a Category by name
   *
   * @param string $name The Cat by name
   *
   * @return object
   */
  public function getByName($name)
  {
    $q = Doctrine_Query::create()
      ->select('p.name AS name, p.id AS id')
      ->from('PoiCategory p')
      ->where('p.name=?', $name);

      return $q->fetchOne();
  }
}
