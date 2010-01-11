<?php
/**
 * Database factory to get DB connection
 *
 * @package lib.projectn
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @version 1.0.1
 *
 */
final class database
{

  /**
   * Holds factory object
   *
   * @static
   */
  public static $pDB;


  /**
   *
   * @param string $connectionType Type of connection object
   *
   * @return object Database object for the connection required
   */
  public static function factory($connectionType = 'dev')
  {
    if(!is_object(self::$pDB))
    {
      switch($connectionType)
      {
        case 'dev':
            self::$pDB = Doctrine_Manager::connection( 'mysql://timeout:65dali32@80.250.104.16/searchlight', 'searchlight' );
          break;

        default:
            self::$pDB = Doctrine_Manager::connection( 'mysql://timeout:65dali32@80.250.104.16/searchlight', 'searchlight' );
      }
      return self::$pDB->getDbh();
    }
  }
}

?>
