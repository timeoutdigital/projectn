<?php

class MetricDimensionForm extends BaseForm
{
  protected static $models = array( 'Poi', 'Event', 'Movie' );

  public function configure()
  {      
    $jsCallback = array( 'onChange' => 'refreshPane();' );

    $this->setWidgets(array(
        'vendor'  => new sfWidgetFormSelect( array( 'choices' => Doctrine::getTable('Vendor')->findAll( 'KeyValue' ) ), $jsCallback ),
        'model'   => new sfWidgetFormSelect( array( 'choices' => static::$models ), $jsCallback )
    ));
    
    $this->widgetSchema->setFormFormatterName( 'list' );
  }
}