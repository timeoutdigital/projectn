<?php

class MetricDimensionForm extends BaseForm
{
  protected static $models = array( 'Poi', 'Event', 'Movie' );

  public function configure()
  {      
    $jsCallback = array( 'onChange' => 'refreshPane(); refreshGraph();' );

    $this->setWidgets(array(
        'vendor'  => new sfWidgetFormSelect( array( 'choices' => Doctrine::getTable('Vendor')->findAll( 'KeyValue' ) ), $jsCallback ),
        'model'   => new sfWidgetFormSelect( array( 'choices' => static::$models ), $jsCallback )
    ));
    
    $this->widgetSchema->setFormFormatterName( 'list' );
  }
}

// -----------

/**
 * Custom Hydrator that Hydrates Key Value Associative Array.
 */
class Doctrine_Hydrator_KeyValue extends Doctrine_Hydrator_Abstract
{
    public function hydrateResultSet($stmt)
    {
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if( empty( $data ) ) return $data;

        $keys = array_keys( $data[ 0 ] );
        foreach( $data as $k => $v ) $fixed_data[ $v[ $keys[ 0 ] ] ] = $v[ $keys[ 1 ] ];

        ksort( $fixed_data );
        return $fixed_data;
    }
}

Doctrine_Manager::getInstance()->registerHydrator( 'KeyValue', 'Doctrine_Hydrator_KeyValue' );