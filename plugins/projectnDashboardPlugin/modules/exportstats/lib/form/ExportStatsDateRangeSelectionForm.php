<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class ExportStatsDateRangeSelectionForm extends BaseForm
{
  public function configure()
  {
    $jsCallback = array(); //array( 'onChange' => 'refreshGraph();' );

    $years = range( 2010, date('Y'));
    $dateWidgetFrom = new sfWidgetFormDate( array(
        'format' => '%day%/%month%/%year%',
        'can_be_empty' => false,
        'years' => array_combine( $years, $years),
    ), $jsCallback );

    $dateWidgetTo = clone $dateWidgetFrom;

    $this->setWidgets(array(
        'date'    => new sfWidgetFormDateRange( array( 'from_date' => $dateWidgetFrom, 'to_date' => $dateWidgetTo ) )
    ));

    $this->setDefault( 'date', array( 'from' => '-3 month', 'to' => 'today' ) );

    $this->widgetSchema->setFormFormatterName( 'list' );
  }
}