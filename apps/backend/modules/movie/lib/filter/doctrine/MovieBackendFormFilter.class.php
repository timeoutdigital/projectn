<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MovieBackendFormFilter extends BaseMovieFormFilter
{
    public function configure()
    {
        $this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
        $this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));
    }

} 
?>
