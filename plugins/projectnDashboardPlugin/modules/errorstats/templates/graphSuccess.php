<style type="text/css">
    ul.dygraph-daterange, ul.dygraph-daterange li
    {
        padding: 0; margin: 0; list-style: none;
    }
    ul.dygraph-daterange{
        margin-bottom: 1em;
        text-align: right;
        margin-right: 20%;
    }
    ul.dygraph-daterange li{
        display:inline-table;
        margin-left: 2em;
    }
    ul.dygraph-daterange select{
        font-size: 130%;
    }
</style>
<div style="overflow:auto;">
    <div style="overflow:auto;">
        <ul class="dygraph-daterange"><?php echo $form; ?></ul>
    </div>
    <div style="clear:both;"></div>
    <div id="graphholder" style=" width:80%; height:450px; float:left;"></div>
    <div id="labels"  style="width:20%; float:right;"></div>

    <script type="text/javascript">
      new Dygraph(
        document.getElementById("graphholder"),
        "<?php
            echo 'Date,'.ucwords( implode( ', ', $vendors ) ).'\n';
            foreach( $stats as $date => $vendorMetrics ):
                $vendorValue = array();
                foreach( $vendors as $vendorID => $vendor )
                    $vendorValue[] = isset( $vendorMetrics[ $vendorID ] ) ? $vendorMetrics[ $vendorID ] : 0;

                // print OUT
                echo $date . ',' . implode(',', $vendorValue ). '\n';
            endforeach;
        ?>",
        {
          rollPeriod: 1,
          showRoller: false,
          includeZero: true,
          strokeWidth: 2,
          drawPoints: true,
          labelsShowZeroValues: false,
          labelsDivWidth: 150,
          labelsSeparateLines: true,
          labelsDiv: document.getElementById("labels")
        }
      );
          //stepPlot: true,
          //colors: ['#B21212','#B2124D','#B2127F','#B212B2','#B85EB8','#D66BD6','#FC7CFC','#0971B2','#099682','#09B29B','#093CB2','#4566B5','#BA8B32','#FFD417','#FF9E17','#FF6817','#FF8645','#FAB43C','#E6DA37','#CCC237','#CCC983','#BA8B32']
    </script>
</div>