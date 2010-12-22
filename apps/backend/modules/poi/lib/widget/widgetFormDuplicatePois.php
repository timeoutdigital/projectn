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

class widgetFormDuplicatePois extends sfWidgetForm
{
    private $poi;
    
    public function  __construct($options = array(), $attributes = array())
    {
        $this->poi = isset( $attributes['model'] ) ? $attributes['model'] : null;

        parent::__construct($options, $attributes);
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        // Print existing Duplicates
        if( isset( $this->poi ) )
        {
            $name.='[]';
            echo $this->renderTag( 'input', array( 'type' => 'text', 'value' => 'test', 'name' => $name) );
            echo $this->renderTag( 'input', array( 'type' => 'text', 'value' => 'test', 'name' => $name) );
            //echo $this->renderTag( 'input', array( 'type' => 'text', 'value' => 'test2', 'name' => 'poi[DuplicatePoisForm][test2]') );
        }

        $autoCompleter =  new sfWidgetFormJQueryAutocompleter(array(
            'url'   => sfContext::getInstance()->getRequest()->getScriptName() . '/poi/ajaxGetRelatedPoi',
            'config' => '{ extraParams: { vendor: '.$this->poi["vendor_id"].'} }',
              ) );
        
        $returnHTML = $autoCompleter->render( 'duplicate_poi_id' );

        $returnHTML .= $this->renderContentTag( 'input', '', array( 'type' => 'button', 'value' => 'Add as Duplicate') );

        return $returnHTML;
    }

    /**
    * Gets the stylesheet paths associated with the widget.
    *
    * @return array An array of stylesheet paths
    */
    public function getStylesheets()
    {
        return array('/sfFormExtraPlugin/css/jquery.autocompleter.css' => 'all');
    }    
}