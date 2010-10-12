<?php use_helper('jQuery'); ?>

<style type="text/css">
    form ul li { display: inline; font-size: 16px; margin-right:30px; }
    select { padding: 5px; }
    div#metricBar { margin-right:10px; padding:5px 10px; background-color:#C8D6FF }
</style>

<script type="text/javascript">
    function refreshMetricPane()
    {
        $.ajax({
          type: 'POST',
          url: 'statistics/pane',
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
            $("#metricPane").html( data );
          }
        });
    }

    $( document ).ready( function() {
        refreshMetricPane();
    });
</script>

<div id="metricBar">
    <form action="" method="">
        <ul>
            <?php echo $form ?>
        </ul>
    </form>
</div>

<div id="metricPane"></div>