<?php

/**
 * Poi form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PoiForm extends BasePoiForm
{
  public function configure()
  {
    $this->widgetSchema[ 'vendor_poi_id' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'review_date' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'local_language' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'created_at' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'updated_at' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'vendor_id' ] = new widgetFormFixedText();
  }

  protected function doUpdateObject( $values = null )
  {
    parent::doUpdateObject( $values );
    //var_dump( $this->getObject()->getModified( true ) ); exit;

    $record = $this->getObject();
    $override = new recordFieldOverrideManager( $record );
    $override->saveRecordModificationsAsOverrides();
  }
}
