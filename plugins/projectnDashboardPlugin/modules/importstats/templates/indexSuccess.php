<?php use_helper('jQuery'); ?>

<style type="text/css">
    form ul li { display: inline; font-size: 16px; margin-right:30px; }
    select { padding: 5px; }
    div#metricBar { margin-right:10px; padding:5px 10px; background-color:#C8D6FF }
</style>

<script type="text/javascript">

    var spinner = '<p><img src="/images/spinner.gif" style="margin-right:20px;" />loading...</p>';
    $("#graph").html( spinner );
    $("#pane").html( spinner );
    $("#failurePane").html( spinner );
    
    function refreshGraph()
    {
        $.ajax({
          type: 'POST',
          url: 'importstats/graph',
          data: ( {
              date_from_month   : $("#date_from_month").val(),
              date_from_day     : $("#date_from_day").val(),
              date_from_year    : $("#date_from_year").val(),
              date_to_month     : $("#date_to_month").val(),
              date_to_day       : $("#date_to_day").val(),
              date_to_year      : $("#date_to_year").val(),
              vendor_id         : $("#vendor").val(),
              model             : $("#model :selected").text()
          } ),
          success: function( data ) {
            $("#graph").html( data );
          }
        });

        $("#graph").html( spinner );
    }

    function refreshPane()
    {
        $.ajax({
          type: 'POST',
          url: 'importstats/pane',
          data: ( {
              vendor_id         : $("#vendor").val(),
              model             : $("#model :selected").text(),
              date_month        : $("#date_month").val(),
              date_day          : $("#date_day").val(),
              date_year         : $("#date_year").val()
          } ),
          success: function( data ) {
            $("#pane").html( data );
          }
        });

        loadErrors();
        $("#pane").html( spinner );
    }

    function loadErrors()
    {
        $.ajax({
          type: 'POST',
          url: 'importstats/errors',
          data: ( {
              vendor_id         : $("#vendor").val(),
              model             : $("#model :selected").text(),
              date_month        : $("#date_month").val(),
              date_day          : $("#date_day").val(),
              date_year         : $("#date_year").val()
          } ),
          success: function( data ) {
            $("#failurePane").html( data );
          }
        });

        $("#failurePane").html( spinner );
    }

    $( document ).ready( function() {
        refreshPane();
        refreshGraph();
    });
</script>

<div id="metricBar">
    <form action="" method="">
        <ul>
            <?php echo $form ?>
        </ul>
    </form>
</div>

<style type="text/css">
    table#panel td { border:none; background-color:#fff; }
    table#panel tr { border-bottom: solid 3px #C8D6FF; }
    table#panel span.panel-title { font-weight:bold; }
    table#panel p.diff { display:block; width:40px; height:32px; margin:-3px 0; padding:0px; text-align:right; font-style: italic; }
    p.up{ background-image: url( "/images/up_alt.png" ); background-repeat: no-repeat; }
    p.down{ background-image: url( "/images/down_alt.png" ); background-repeat: no-repeat; }
    p.num { background-color:#C8D6FF; border-radius: 5px; -moz-border-radius: 5px; padding:5px; margin-bottom: 0px; text-align:center; }
    p.noyesterday { background-image: url( "/images/alert.png" ); background-repeat: no-repeat; height:22px; padding-top:10px; padding-left:50px; margin-left:10px; }
</style>

<div id="metricPane">
<table style="background-color:#fff;margin-right:10px;">
    <tr>
        <td style="background-color:#fff; border:none;" valign="top">
            <div id="pane" style="background-color:#C8D6FF; width:300px; margin:10px; border-radius: 5px; -moz-border-radius: 5px;">
            </div>
        </td>
        <td id="graph" style="border:none; padding:17px;" width="100%"></td>
    </tr>
</table>
</div>
<div id="failurePane"></div>