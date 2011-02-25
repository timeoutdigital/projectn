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
             $poiMaster = $this->poi->getMasterPoi();
             return sprintf( 'This poi is duplicate of poi: <a href="%s" title="Edit: %s"><strong>%s</strong></a>', sfContext::getInstance()->getRequest()->getScriptName() . "/poi/{$poiMaster['id']}/edit", $poiMaster['poi_name'], $poiMaster['poi_name'] );
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

        $returnHTML .= $this->renderContentTag( 'input', '', array( 'type' => 'button', 'value' => 'Add as duplicate', 'onclick' => 'dupes_addNew();') );

        return $returnHTML;
    }

    private function printPoiDuplicates( $name )
    {
        printf( '<div id="%s" class="duplicate_pois_listing"> ', "duplicate_poi_holder");

        if( isset( $this->poi ) )
        {
            $duplicatPois = $this->poi->getDuplicatePois();
                if( $duplicatPois != null )
            {
                foreach( $duplicatPois as $dupePoi )
                {
                    printf( '<span>' );
                    printf( '<a href="#" title="Remove as duplicate" class="remove" onclick="removeAsDuplicate( this ); return false;"> Remove </a> &nbsp;' );
                    printf( '<input type="hidden" name="%s" value="%s" />', $name, $dupePoi['id'] );
                    printf( '<strong><a href="%s" title="Edit this poi">%s</a></string>', sfContext::getInstance()->getRequest()->getScriptName() . "/poi/{$dupePoi['id']}/edit", $dupePoi['poi_name'] );
                    printf( '</span>' );
                }
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
        script_name = '%s';
        hiddenField = jQuery('#%s');
        visibleField = jQuery('#%s');

        if( hiddenField.val() == '' || hiddenField.val() <= 0 )
            return;

        // add a New Duplicate POI
        poi_holder = jQuery('#%s');
        poi_holder.append( '<span><a href="#" title="Remove as duplicate" class="remove" onclick="removeAsDuplicate( this ); return false;"> Remove </a> &nbsp;<input type="hidden" name="%s" value="'+hiddenField.val()+'" /><strong><a href="'+script_name+'/poi/'+hiddenField.val()+'/edit" title="Edit this poi">'+visibleField.val()+'</a></string></span>' );
        hiddenField.val('');
        visibleField.val('');
    }
    
    function removeAsDuplicate( link )
    {
        if(confirm('Remove: Are you sure You want to Remove this POI as Duplicate'))
        {
            // delete the SPAN
            jQuery(link).parent().remove();
        }
    }
</script>
EOF
      ,
                sfContext::getInstance()->getRequest()->getScriptName(),
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