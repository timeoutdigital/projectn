<?php

class DateSelectionForm extends BaseForm
{
  public function configure()
  {      
    $jsCallback = array( 'onChange' => 'refreshPane();' );
    
    $dateWidget = new sfWidgetFormDate( array(
        'format' => '%day%/%month%/%year%',
        'can_be_empty' => false,
        'years' => array( '2010' => '2010', '2011' => '2011', '2012' => '2012', '2013' => '2013' ),
    ), $jsCallback );

    $dateWidget->setLabel( false );

    $this->setWidgets(array(
        'date'    => $dateWidget
    ));

    $this->setDefault( 'date', 'today' );
    
    $this->widgetSchema->setFormFormatterName( 'list' );
  }
}