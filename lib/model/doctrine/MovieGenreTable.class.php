<?php

class MovieGenreTable extends Doctrine_Table
{


   /**
   * Get a Genre By Name
   *
   * @param string $genre The genre by name
   *
   * @return object
   */
  public function getGenreByName($name)
  {
    $q = Doctrine_Query::create()
      ->select('g.genre, g.id')
      ->from('MovieGenre g')
      ->where('g.genre=?', $name);

      return $q->fetchOne();
  }
}
