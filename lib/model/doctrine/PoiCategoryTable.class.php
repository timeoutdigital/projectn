<?php

/**
 *
 * @package doctrine.model.lib
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
