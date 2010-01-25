<?php
/**
 * Interface for logging functionality
 *
 * @author Tim Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @package projectn
 * @subpackage import.lib
 *
 *
 * @version 1.0.0
 *
 */
interface logger
{
    public function countNewInsert($type);

    public function countUpdate($type);

}
?>
