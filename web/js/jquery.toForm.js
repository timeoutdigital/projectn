/*
 * author Clarence Lee
 *
 * dependencies: jquery.datepicker
 */

/*
 * creates connects datepickers with syntax:
 * connect-with-[another_datepicker]-as-['min'|'max']
 *
 * Options:
 * selector: a selector to match what becomes a datepicker
 * date_format: dateformat for the datepicker
 * string_to_date_func: a function to convert the input value into an Date object
 *                      function( dateString)
 *                      {
 *                        //some logic...
 *                        date = new Date( ... );
 *                        return date;
 *                      }
 *
 * Example:
 *
 * <input type="text" class="datepicker a connect-with-b-as-max" /> (datepicker a)
 * <input type="text" class="datepicker b connect-with-a-as-min" /> (datepicker b)
 * <input type="text" class="datepicker c connect-with-a-as-min" /> (datepicker c)
 *
 * With the above code if datepicker a has value 2010-1-1,
 * datepicker b will not allow the user to pick any date before 2010-1-1
 * datepicker c will not allow the user to pick any date before 2010-1-1
 *
 * If date picker b has value 2011-1-1,
 * datepicker a will not allow the user to pick any date after 2011-1-1,
 *
 */
( function( $ )
{
    $.fn.toFormDatepickerConnect = function ( options )
    {
        var o = $.extend( {
            selector: 'input.datepicker',
            date_format: 'yy-mm-dd',
            string_to_date_func: stringToDate
        }, options );

        var form = $( this );

        initDatepickers();

        function initDatepickers()
        {
            var dateInputs = $( o.selector );
            dateInputs.datepicker( { dateFormat: o.date_format, beforeShow: datepickerCheckConnections } );
        }

        function datepickerCheckConnections()
        {
            var classes = $( this ).attr( 'class' );
            var match = classes.match( /connect-with-/ );
            var hasConnection = match != null && match.length > 0;

            if( hasConnection )
            {
                var params = classes.match( /connect-with-(.*)-as-([^\s]*)/ );
                var partnerClass = '.'+params[ 1 ];
                var minOrMax = params[ 2 ];

                var partner = form.find( partnerClass );
                var partnerDate = o.string_to_date_func( partner.val() );

                if( partnerDate )
                {
                  if( minOrMax == 'min' )
                    $(this).datepicker( 'option', 'minDate', partnerDate );
                  else if( minOrMax == 'max' )
                    $(this).datepicker( 'option', 'maxDate', partnerDate );
                }
            }
        }

        function stringToDate( dateString )
        {
          if( !dateString )
            return false;

          var d = dateString.split( '-' );
          return new Date( d[ 0 ], d[ 1 ]-1, d[ 2 ] );
        }
    }
}
)( jQuery );


/*
 * creates hide / show relationships between form elements
 *
 * Options:
 * child_class:   the class child elements are marked with
 * parent_prefix: prefix to declare parent in child element's class
 * show_conditions_prefix: prefix to declare parent value conditions
 *                         required for this element to become visibel
 * conditions_delineator: a string to delineate conditions with. See example
 *
 * Example:
 *
 * <div id="element-one">
 *  <label>A</label>
 *  <input type="radio" name="_" value="a" />
 *
 *  <label>B</label>
 *  <input type="radio" name="_" value="b" />
 * </div>
 *
 * <div id="element-two" class="hidden parent-element-one show-if-a">
 *  <label>A</label>
 *  <input type="radio" name="__" value="a" />
 *
 *  <label>B</label>
 *  <input type="radio" name="__" value="b" />
 * </div>
 *
 * <div id="element-two-a" class="hidden parent-element-two show-if-a">
 *  Some form elements
 * </div>
 *
 * <div id="element-two-b" class="hidden parent-element-two show-if-b">
 *  Some form elements
 * </div>
 *
 * when the page loads:
 * only #element-one is visible
 * click #element-one radio button A,
 */
( function( $ )
{
    $.fn.toFormHideShowFields = function ( options )
    {
        var o = $.extend( {
            child_selector: '.child',
            parent_prefix: 'parent-',
            show_conditions_prefix: 'show-if-',
            conditions_delineator: '-'
        }, options );

        var form = $( this );

        form.find( o.child_selector ).each( function()
        {
          var child = $( this );

          var parentElement = $( '#' + getParentIdFromChild( child ) );

          child.data( 'conditionsMet', false );
          bindParentChangeEventToChild( parentElement, child );
          bindParentVisibilityEventToChild( parentElement, child );

        });

        function getParentIdFromChild( child )
        {
          var classes  = child.attr( 'class' );
          var regex    = new RegExp( o.parent_prefix + '([^ ]+)' );
          var matches  = classes.match( regex );
          var parentId = '';

          if( matches != null && matches.length > 1 )
            parentId = matches[ 1 ];

          return parentId;
        }

        function bindParentChangeEventToChild( parentElement, child )
        {
          parentElement.find( 'input' )
            .bind( 'click', function()
            {
              var conditions = getShowConditionsFromChild( child );
              var conditionsMet = $.inArray( $( this ).val(), conditions ) >= 0;

              if( conditionsMet )
              {
                child.data( 'conditionsMet', true );
                child.removeClass( 'hidden' );
                child.triggerHandler( 'visibility', { isVisible: true } );
              }
              else
              {
                child.data( 'conditionsMet', false );
                child.addClass( 'hidden' );
                child.triggerHandler( 'visibility', { isVisible: false } );
              }

            })
        }

        function bindParentVisibilityEventToChild( parentElement, child )
        {
          parentElement.bind( 'visibility', function( event, data )
          {
            if( data.isVisible && child.data( 'conditionsMet' ) )
            {
              child.removeClass( 'hidden' );
              child.triggerHandler( 'visibility', { isVisible: true } );
            }
            else
            {
              child.addClass( 'hidden' );
              child.triggerHandler( 'visibility', { isVisible: false } );
            }
          });
        }

        function getShowConditionsFromChild( child )
        {
          var classes = child.attr( 'class' );
          var regex   = new RegExp( o.show_conditions_prefix + '([^ ]+)' );
          var matches = classes.match( regex );
          var conditions = [];

          if( matches.length > 1 )
          {
            var conditions = matches[ 1 ].split( o.conditions_delineator );
          }
          return conditions;
        }
    }
}
)( jQuery );

( function( $ )
{
  $.fn.autoCompleteVenue
}
)( jQuery );


jQuery( function()
{
    jQuery( 'form.get-listed' ).toFormDatepickerConnect();
    jQuery( 'form.get-listed' ).toFormHideShowFields();
});
