<div>
    <?php
        echo $date;
    ?>
    <input type="submit" name="generate" id="btn_generate" value="Generate" onclick="generateReport(); return false;" />
</div>
<script type="text/javascript">/* <![CDATA[ */
function generateReport()
{
    $from_day = jQuery('#date_from_day').val();
    $from_month = jQuery('#date_from_month').val();
    $from_year = jQuery('#date_from_year').val();

    $to_day = jQuery('#date_to_day').val();
    $to_month = jQuery('#date_to_month').val();
    $to_year = jQuery('#date_to_year').val();

    $vendor = jQuery('#vendor').val();
    $invoiceable = jQuery('#invoiceable').attr( 'checked' );

    $container = jQuery('#generated_results');
    $container.html('generating report...');

    $container.load( '<?php echo url_for( 'invoice_ui/GenerateReport'); ?>', {
        'from_day': $from_day,
        'from_month': $from_month,
        'from_year': $from_year,
        'to_day': $to_day,
        'to_month': $to_month,
        'to_year': $to_year,
        'vendor': $vendor,
        'invoiceable': $invoiceable
    } );
    
}
/* ]]> */</script>
