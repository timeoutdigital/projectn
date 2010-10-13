/**
 * author Clarence Lee
 *
 * creates UI for collapsing and expanding an element
 *
 * options:
 * handleSelector: where to put the buttons to control collapsing
 * getStoredState:  a function that returns 'collapsed' or 'expand'
 * expandMethod:    a function that collapses your ui
 * collapseMethod:  a function that expands your ui
 *
 * example:
 * jQuery( function() {
 * 
 *   jQuery( '#editForm .instance' ).toCollapsible({
 *     handleSelector: 'h3:first',
 *     getStoredState: restore,
 *     collapseMethod: collapse,
 *     expandMethod: expand
 *   });
 * 
 *   function restore( element )
 *   {
 *     return element.find( 'input.ui-collapse-state' ).val();
 *   }
 * 
 *   function collapse( element )
 *   {
 *     element.find( 'li' ).addClass( 'to-collapsed' );
 *     element.find( 'li:first, li:first *' ).removeClass('to-collapsed');
 * 
 *     element.find( 'input.ui-collapse-state' ).val( 'collapsed' );
 * 
 *     var header = element.find( 'h3:first' );
 *     var headerText = header.text();
 * 
 *     var firstValue = element.find( 'input[type=text]' ).eq( 0 ).val();
 * 
 *     if( !firstValue )
 *       firstValue = 'no title';
 * 
 *     header.append( '<span class="preview-text"> (' + firstValue + ')</span>' );
 *   }
 * 
 *   function expand( element )
 *   {
 *     element.find( '*' ).removeClass( 'to-collapsed' );
 *     element.find( 'input.ui-collapse-state' ).val( 'expanded' );
 *     element.find( '.preview-text' ).remove();
 *   }
 * });
 *
 */
( function( $ )
{
  var toCollapsible = function( element, options )
  {
    this.element = element;
    this._isCollapsed = this.getStoredState();
    this.options = $.extend(
    {
        handleSelector: '.to-collapsible-ui-handle',
        topGap: 30,
        animated: true
    }
    , options);

    if( typeof options.collapseMethod == 'function' )
      this._collapseMethod = options.collapseMethod;

    if( typeof options.expandMethod == 'function' )
      this._expandMethod = options.expandMethod;

    if( typeof options.getStoredState == 'function' )
      this.getStoredState = options.getStoredState;

    this.ui =
    '<div class="to-collapsible-ui">' +
    '  <a class="to-collapsible-ui-this" title="toggle this component"></a>' +
    '  <a class="to-collapsible-ui-others" title="show this component only"></a>' +
    '</div>';

    this._init();
  }

  toCollapsible.instances = [];

  toCollapsible._addInstance = function( instance )
  {
    toCollapsible.instances.push( instance );
  }

  toCollapsible._removeInstance = function( instance )
  {
    var instances = toCollapsible.instances
    for( var i in instances )
    {
      if( instance == instances[ i ] )
      {
        //todo
      }
    }
  }

  toCollapsible.getInstances = function()
  {
    return toCollapsible.instances;
  }

  toCollapsible.getInstance = function( element )
  {
    var instance = '';
    $( toCollapsible.getInstances() ).each( function() {
      if( this.getElement() == element ){
        instance = this;
      }
    } );
    return instance;
  }

  toCollapsible.prototype = 
  {
    collapse: function()
    {
      this._collapseMethod( this.getjQueryElement() )
      this.getjQueryElement().find( '.to-collapsible-ui-this' ).addClass( 'to-collapse-ui-is-collapsed' );
      this._isCollapsed = true;
      this.getjQueryElement().triggerHandler( 'collapse' );
    },

    expand: function()
    {
      this._expandMethod( this.getjQueryElement() )
      this.getjQueryElement().find( '.to-collapsible-ui-this' ).removeClass( 'to-collapse-ui-is-collapsed' );
      this._isCollapsed = false;
      this.getjQueryElement().triggerHandler( 'expand' );
    },

    toggle: function()
    {
      this.isCollapsed() ? this.expand() : this.collapse();
    },

    collapseOthers: function()
    {
      var self = this;
      $( toCollapsible.getInstances() ).each( function() {
        if( self != this)
          this.collapse();
      });
    },

    expandOthers: function()
    {
      var self = this;
      $( toCollapsible.getInstances() ).each( function() {
        if( self != this)
          this.expand();
      });
    },

    toggleOthers: function()
    {
      var self = this;

      $( toCollapsible.getInstances() ).each( function() {
        if( self != this)
          this.toggle();
      });
    },

    isCollapsed: function()
    {
      return this._isCollapsed;
    },

    getOptions: function()
    {
      return this.options;
    },

    getjQueryElement: function()
    {
      return $( this.element );
    },

    getElement: function()
    {
      return this.element;
    },

    getStoredState: function( collapsible )
    {
    },

    destroy: function( collapsible )
    {
    },

    _init: function()
    {
      this._initUi();
      toCollapsible._addInstance( this );
    },

    _initUi: function()
    {
      var self = this;
      var element = this.getjQueryElement()
      var handle = element.find( this.options.handleSelector );

      handle.css( 'position', 'relative' ).append( this.ui );

      handle.find( '.to-collapsible-ui-this' ).click( function( event )
      {
        event.stopImmediatePropagation();
        self.toggle();
      } );

      handle.find( '.to-collapsible-ui-others' ).click( function( event )
      {
        event.stopImmediatePropagation();
        self.collapseOthers();
        self.expand();

        var componentScroll = self.getjQueryElement().offset().top
        var componentHeight = self.getjQueryElement().height();
        var componentBottom = componentScroll + componentHeight;

        var windowScroll = $( window ).scrollTop( );
        var windowHeight = $( window ).height();
        var windowBottom = windowScroll + windowHeight;

        var scrollTop    = componentScroll - self.options.topGap;
        var bottomDiff   = windowBottom - componentBottom;
        var scrollBottom = windowScroll - bottomDiff;

        if( componentScroll < windowScroll )
        {
            $( 'html, body' ).animate({
                scrollTop: scrollTop
                }, 5 );
        }
        else if( componentBottom > windowBottom )
        {
            scroll 
            $( 'html, body' ).animate({
                scrollTop: scrollBottom
                }, 500 );
        }
      } );

      if( this.getStoredState( element ) == 'collapsed' )
        this.collapse();
    },

    _destroy: function()
    {
      this.destroy();
      toCollapsible._removeInstance( this );
    },

    _collapseMethod: function( collapsible )
    {
      collapsible.find( 'to-collapse-target' ).hide();
    },

    _expandMethod: function( collapsible )
    {
      collapsible.find( 'to-collapse-target' ).show();
    },

  }

  $.fn.toCollapsible = function( options )
  {
    if( typeof options == 'string' )
    {
      //don't call private methods
      if( options.substring( 0, 1 ) == '_' )
        return;

      $( this ).each( function() {
        var instance = toCollapsible.getInstance( this );
        instance[ options ]();
      });

      return;
    }

    $( this ).each( function()
    {
      new toCollapsible( this, options );
    });
  }

  $.toCollapsible = function( method )
  {
    //don't call private methods
    if( method.substring( 0, 1 ) == '_' )
      return;

    $( toCollapsible.getInstances() ).each( function(){
      this[ method ]();
    });
  }

}
)( jQuery );
