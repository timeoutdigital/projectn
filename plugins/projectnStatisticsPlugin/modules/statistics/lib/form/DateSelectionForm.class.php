<?php

class DateSelectionForm extends BaseForm
{
  public function configure()
  {      
    $jsCallback = array( 'onChange' => 'refreshPane();' );
    
    $dateWidget = new sfWidgetFormDate( array(
        'format' => '%day%/%month%/%year%',
        'can_be_empty' => false,
        'years' => array( '2010' => '2010' ),
    ), $jsCallback );

    $dateWidget->setLabel( false );

    $this->setWidgets(array(
        'date'    => $dateWidget
    ));

    $this->setDefault( 'date', 'today' );
    
    $this->widgetSchema->setFormFormatterName( 'list' );
  }
}