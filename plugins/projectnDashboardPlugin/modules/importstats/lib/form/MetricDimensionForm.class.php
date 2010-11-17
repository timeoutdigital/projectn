<?php

class MetricDimensionForm extends BaseForm
{
  protected static $models = array( 'Poi', 'Event', 'Movie' );

  public function configure()
  {      
    $jsCallback = array( 'onChange' => 'refreshPane(); refreshGraph();' );

    $vendorList = Doctrine::getTable('Vendor')->findAll( 'KeyValue' );
    sort( $vendorList );

    $this->setWidgets(array(
        'vendor'  => new sfWidgetFormSelect( array( 'choices' => $vendorList ), $jsCallback ),
        'model'   => new sfWidgetFormSelect( array( 'choices' => static::$models ), $jsCallback )
    ));
    
    $this->widgetSchema->setFormFormatterName( 'list' );
  }
}