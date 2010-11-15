<?php use_helper('jQuery'); ?>
<script type="text/javascript" src="/js/dygraph-combined.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />

<style type="text/css">
    #metricPane{
        display:block;
        background-color: #fff;
        padding: 1em;
        margin: 1em;
        border: 1px solid #ccc;
}
</style>
<script type="text/javascript">

    var spinner = '<p><img src="/images/spinner.gif" style="margin-right:20px;" />loading...</p>';
    function refreshGraph()
    {
        $.ajax({
          type: 'POST',
          url: 'errorstats/graph',
          data: ( {
              date_from_month   : $("#date_from_month").val(),
              date_from_day     : $("#date_from_day").val(),
              date_from_year    : $("#date_from_year").val(),
              date_to_month     : $("#date_to_month").val(),
              date_to_day       : $("#date_to_day").val(),
              date_to_year      : $("#date_to_year").val(),
              model             : $("#model :selected").text()
          } ),
          success: function( data ) {
            $("#metricPane").html( data );
          }
        });

        $("#metricPane").html( spinner );
    }
    $( document ).ready( function() {
        refreshGraph();
    });
</script>

<div id="metricPane">
    
</div>