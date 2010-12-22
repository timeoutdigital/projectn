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
         if( isset( $this->poi ) && $this->poi->isDuplicate() )
         {
             return 'This is a Duplicate POI';
         }
         
        $name.='[]'; // Change name into Array
        
        // Print existing Duplicates
        $this->printPoiDuplicates( $name );
        $this->renderJavascript( $name );
        
        $autoCompleter =  new sfWidgetFormJQueryAutocompleter(array(
            'url'   => sfContext::getInstance()->getRequest()->getScriptName() . '/poi/ajaxGetRelatedPoi',
            'config' => '{ extraParams: { vendor: '.$this->poi["vendor_id"].'} }',
              ) );
        
        $returnHTML = $autoCompleter->render( 'duplicate_poi_id' );

        $returnHTML .= $this->renderContentTag( 'input', '', array( 'type' => 'button', 'value' => 'Add as Duplicate', 'onclick' => 'dupes_addNew();') );

        return $returnHTML;
    }

    private function printPoiDuplicates( $name )
    {
        printf( '<div id="%s" class="duplicate_pois_listing"> ', "duplicate_poi_holder");

        if( isset( $this->poi ) )
        {
            foreach( $this->poi['DuplicatePois'] as $dupePoi )
            {
                printf( '<span>' );
                printf( '<input type="hidden" name="%s" value="%s" />', $name, $dupePoi['id'] );
                printf( '<strong>%s</string>', $dupePoi['poi_name'] );
                printf( '</span>' );
            }
        }

        printf( '</div> ');

    }

    private function renderJavascript( $name )
    {
        
        printf(<<<EOF
<script type="text/javascript">
    function dupes_addNew()
    {
        hiddenField = jQuery('#%s');
        visibleField = jQuery('#%s');

        if( hiddenField.val() == '' || hiddenField.val() <= 0 )
            return;

        // add a New Duplicate POI
        poi_holder = jQuery('#%s');
        poi_holder.append( '<span><input type="hidden" name="%s" value="'+hiddenField.val()+'" /><strong>'+visibleField.val()+'</string></span>' );
        hiddenField.val('');
        visibleField.val('');
    }
</script>
EOF
      ,
                $this->generateId( 'duplicate_poi_id' ),
                'autocomplete_'.$this->generateId( 'duplicate_poi_id' ),
                'duplicate_poi_holder',
                $name
                );
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