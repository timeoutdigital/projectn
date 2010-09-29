<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class PoiBackendFormFilter extends BasePoiFormFilter
{
    public function configure()
    {
        $this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
        $this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));
    }

} 
?>
