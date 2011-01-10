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

class dateRangeSelectForm extends BaseForm
{

    public function configure()
    {
        $jsCallback = array( 'onChange' => 'generateReport();' );

        $dateWidgetFrom = new sfWidgetFormDate( array(
            'format' => '%day%/%month%/%year%',
            'can_be_empty' => false,
            'years' => array( '2010' => '2010', '2011' => '2011' ),
        ), $jsCallback );

        $dateWidgetTo = clone $dateWidgetFrom;

        $this->setWidgets(array(
            'date'    => new sfWidgetFormDateRange( array( 'from_date' => $dateWidgetFrom, 'to_date' => $dateWidgetTo ) )
        ));

        $this->setDefault( 'date', array( 'from' => '-2 weeks', 'to' => 'today' ) );

        $this->widgetSchema->setFormFormatterName( 'list' );
    }
    
}