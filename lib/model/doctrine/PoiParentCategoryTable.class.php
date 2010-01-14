<?php

class PoiParentCategoryTable extends Doctrine_Table
{

    /**
   * Get a Parent Category by name
   *
   * @param string $name The Parent cat name
   *
   * @return object
   */
  public function getByName($name)
  {
    $q = Doctrine_Query::create()
      ->select('p.name AS name, p.id AS id')
      ->from('PoiParentCategory p')
      ->where('p.name=?', $name);
     
      return $q->fetchOne();
  }
}
