<?php

/**
 * A null export
 *
 * @author clarence
 */
class XMLExportNull extends XMLExport
{
  public function __construct( $vendor, $destination )
  {
    throw new ExportException( 'The export you are trying to run does not exist.' );
  }

  public function run()
  {
    
  }

  public function generateXML( $data )
  {
  }
}
?>
