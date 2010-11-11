$(function()
{
  /**
   * Row highlighting
   * Click a row to toggle highlighting
   */
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

  /**
   * Change number type
   * provide links to change type of numbers:
   * - difference as percentage
   * - difference in actual count
   * - count for past period
   * - count for current period
   */
  $( '#data-type' )
    .html( '<a type="percentage">Difference %</a>'    + '<br/>' +
           '<a type="number">Difference No.</a>' + '<br/>' + 
           '<a type="pastperiod">Past count</a>'     + '<br/>' +
           '<a type="currentperiod">Current count</a>'
           )
    .css({'text-align':'left'})
  ;
  $( '#data-type a' )
    .css({'cursor':'pointer'})
    .click( function( event )
    {
      var type = $( this ).attr( 'type' );

      $( '#over-view td' ).each(function( event )
      {
        var td      = $( this );
        var currentContent = td.html();
        td.html( td.attr( type ) );
        td.attr( 'title', type );
      });
    });

  /**
   * Toggle colors for Alexis
  $( '#over-view' )
    .after( '<a id="color-toggle">Toggle colors</a>' )
  ;
  $( '#color-toggle' )
    .click(function()
    {
    })
  ;
   */
});
