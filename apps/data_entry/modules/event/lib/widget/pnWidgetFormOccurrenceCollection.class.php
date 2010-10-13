<?php
/**
 * pnWidgetFormOccurrenceCollection represents an  doctrine_collection of EventOccurrences
 *
 * @package symfony
 * @subpackage widget.lib
 *
 * @author Emre Basala <emrebasala@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class pnWidgetFormOccurrenceCollection extends sfWidgetForm
{
    /**
    * Constructor.
    *
    * Available options:
    *
    *  * type: The widget type
    *
    * @param array $options     An array of options
    * @param array $attributes  An array of default HTML attributes
    *
    * @see sfWidgetForm
    */
    protected $event ;

    protected function configure($options = array(), $attributes = array())
    {
        $this->addOption( 'format', ' %controls% %js% %clearer% ' );

        //option to decide if we need 2 calendars for  a date-interval
        parent::configure( $options, $attributes );
    }


    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        $id = $this->generateId( $name, $value );

        $event = $this->attributes[ 'event' ];
        if( !$event || count( $event[ 'EventOccurrence' ] ) == 0 )
        {
            return "No Occurrences for this event!";
        }

        $occurrences = array( );

        foreach ( $event[ 'EventOccurrence' ] as $occurrence )
        {
            //$output .= 'On ' . $occurrence[ 'start_date'  ]  .'  '. $occurrence[ 'start_time'  ] . ' at ' . $occurrence[ 'Poi' ][ 'poi_name' ] ;
            $occurrenceId =  $occurrence[ 'id' ];

            $occurrenceInfo = array(
                'id'         => $occurrence[ 'id' ],
                'event_id'   => $occurrence[ 'event_id' ],
                'start_date' => $occurrence[ 'start_date' ],
                'end_date'   => $occurrence[ 'start_date' ],
                'poi_name'   => $occurrence[ 'Poi' ][ 'poi_name' ],
                'start_time' => $occurrence[ 'start_time' ],
            );

            $occurrences [ $occurrence[ 'start_date'  ] ] = $this->renderContentTag( 'button',
                          $this->renderTag( 'img' ,
                                    array( 'src' => '/images/delete.png' ,
                                           'height' =>15 ,
                                           'width' => 15 ) ) .
                           ' ' . $occurrence[ 'start_date' ] .' at '.$occurrence[ 'Poi' ][ 'poi_name' ],
                           array( 'type'  => 'button',
                                  'name'  =>  'occurrence_' . $occurrence[ 'start_date' ],
                                  'id'    => $occurrenceId ,
                                  'class' => 'occurrence-delete-button',
                                  'value' => json_encode( $occurrenceInfo )
                                 )
                           );

        }

        ksort ( $occurrences ) ;
        $url =  sfContext::getInstance()->getRequest()->getScriptName() . '/event/ajaxDeleteOccurrence';

        //$calenderJsAsText = implode( '' , $calenderJs );
        $script = <<<JS

        $(function()
        {
            $('.occurrence-delete-button').click(function()
            {
              var occurrence =  eval('(' + $(this).val() + ')');
              var btn = $(this);
              if( confirm( 'Are you sure that you want to delete this occurence ? \\nStart Date : ' + occurrence.start_date  + '\\nEnd Date : ' + occurrence.end_date  +'\\nPoi name : ' + occurrence.poi_name ) )
              {
                 btn.attr("disabled", "true");
                 $.ajax({
                  url:  '{$url}',
                  data : {occurrenceId : occurrence.id ,eventId : occurrence.event_id},
                  success: function( data)
                  {
                     data =  eval('(' + data + ')');
                     if( data.status == 'success')
                     {
                        btn.fadeOut();
                     }
                     else
                     {   btn.removeAttr("disabled");
                         alert( data.message );

                     }
                  }
                });
              }

            });

        });
JS;

         return implode( '', $occurrences) . $this->renderContentTag( 'script', $script, array( 'type' => 'text/javascript' ) ); ;;
    }
}
