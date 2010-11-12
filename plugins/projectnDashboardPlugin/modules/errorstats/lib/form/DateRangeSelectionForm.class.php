<?php

class DateRangeSelectionForm extends BaseForm
{
  public function configure()
  {
    $jsCallback = array( 'onChange' => 'refreshGraph();' );

    $dateWidgetFrom = new sfWidgetFormDate( array(
        'format' => '%day%/%month%/%year%',
        'can_be_empty' => false,
        'years' => array( '2010' => '2010' ),
    ), $jsCallback );

    $dateWidgetTo = clone $dateWidgetFrom;
    
    $this->setWidgets(array(
        'model'   => new sfWidgetFormSelect( array( 'choices' => array( 'Poi' => 'Poi', 'Event' => 'Event', 'Movie' => 'Movie' ) ), $jsCallback ),
        'date'    => new sfWidgetFormDateRange( array( 'from_date' => $dateWidgetFrom, 'to_date' => $dateWidgetTo ) )
    ));

    $this->setDefault( 'date', array( 'from' => '-2 weeks', 'to' => 'today' ) );

    $this->widgetSchema->setFormFormatterName( 'list' );
  }
}