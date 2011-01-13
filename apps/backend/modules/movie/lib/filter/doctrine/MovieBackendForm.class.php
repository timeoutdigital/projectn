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

class MovieBackendForm extends MovieForm
{
    public function  configure() {
        parent::configure();

        $years = range(1900, (date("Y") + 5 ) );
        $this->widgetSchema['release_date'] = new sfWidgetFormDate( array( 'years' => array_combine($years, $years), 'format' => '%day%/%month%/%year%' ) );
        $this->validatorSchema[ 'release_date' ] = new sfValidatorDate( array( 'required' => false ) );
    }
}