$(function()
{
  $( '#over-view tbody tr' )
    .click( function( event )
    { 
      var row = $( this );
      var htmlClass = 'highlight';

      if( row.hasClass( htmlClass ) )
      {
        row.removeClass( 'highlight' );
      }
      else
      {
        row.addClass( 'highlight' );
      }
    });

  $( '#data-type' )
    .html( '<a>Count / Percentage</a>' )
    .click( function( event )
    {
      $( '#over-view td' ).each(function()
      {
        var td      = $( this );
        var title   = td.attr( 'title' );
        var content = td.html();
        td.attr( 'title', content );
        td.html( title );
      });
    });

  $( '#data-type a' )
    .css({'cursor':'pointer'});
});
