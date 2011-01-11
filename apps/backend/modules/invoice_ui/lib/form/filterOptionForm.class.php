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

class filterOptionForm extends BaseForm
{

    public function configure()
    {
        //$jsCallback = array( 'onChange' => 'generateReport();' );

        // Add Date Range selection
        $dateWidgetFrom = new sfWidgetFormDate( array(
            'format' => '%day%/%month%/%year%',
            'can_be_empty' => false,
            'years' => array( '2010' => '2010', '2011' => '2011' ),
        ) );

        $dateWidgetTo = clone $dateWidgetFrom;

        $this->setWidgets(array(
            'date'    => new sfWidgetFormDateRange( array( 'from_date' => $dateWidgetFrom, 'to_date' => $dateWidgetTo ) )
        ));

        $this->setDefault( 'date', array( 'from' => 'last month', 'to' => 'today' ) );

        // Vendor
        $this->setWidget( 'vendor', new sfWidgetFormSelect( array( 'label' => 'Vendor', 'choices' => array() ) ) );
        
        // Invoiceable
        // $this->setWidget( 'invoiceable', new sfWidgetFormInputCheckbox( array( 'label' => 'Invoiceables' ) ) );

        $this->widgetSchema->setFormFormatterName( 'list' );
    }

    public function setVendorChoices( array $vendorList)
    {
        $this->getWidget( 'vendor' )->setOption( 'choices', $vendorList );
    }
    
}