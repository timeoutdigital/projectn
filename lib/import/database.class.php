<?php
/**
 * Database factory to get DB connection
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 *
 *
 */
final class database
{

  public static $pDB;


  /**
   *
   * @param string $connectionType Type of connection object
   *
   * @return object Database object for the connection required
   */
  public static function factory($connectionType)
  {
    if(!is_object(self::$pDB))
    {
      switch($connectionType)
      {
        case 'dev':
          $doctrineConnection = Doctrine_Manager::connection( 'mysql://timeout:65dali32@80.250.104.16/searchlight', 'searchlight' );

          break;

        default:
          $doctrineConnection = Doctrine_Manager::connection( 'mysql://timeout:65dali32@80.250.104.16/searchlight', 'searchlight' );
          break;
      }
      
      return $doctrineConnection->getDbh();
    }
  }
}

?>
