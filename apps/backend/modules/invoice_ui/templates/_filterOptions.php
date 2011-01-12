<div>
    <ul class="models clearfix">
        <li class="mode"><input type="checkbox" name="mode" id="mode" value="month" onchange="switchMode( this );" /><label for="mode">Daily Report</label></li>
        <li><a href="#" onclick="switchModel( this ); return false;" class="model_switch current">Poi</a></li>
        <li><a href="#" onclick="switchModel( this ); return false;" class="model_switch">Event</a></li>
        <li><a href="#" onclick="switchModel( this ); return false;" class="model_switch">Movie</a></li>
    </ul>
    <ul class="filter clearfix" id="daily_filter">
    <?php
        echo $date;
    ?>
        <li><input type="submit" name="generate" id="btn_generate" value="Generate" onclick="generateReport(); return false;" /></li>
    </ul>

    <ul class="filter clearfix hide" id="monthly_filter">
        <li><label>Month Range
                <select id="month">
                    <?php
                        $months = array(
                            1 => '17 Jan - 16 Feb',
                            2 => '17 Feb - 16 Mar',
                            3 => '17 Mar - 16 Apr',
                            4 => '17 Apr - 16 May',
                            5 => '17 May - 16 Jun',
                            6 => '17 Jun - 16 Jul',
                            7 => '17 Jul - 16 Aug',
                            8 => '17 Aug - 16 Sep',
                            9 => '17 Sep - 16 Oct',
                            10 => '17 Oct - 16 Nov',
                            11 => '17 Nov - 16 Dec',
                            12 => '17 Dec - 16 Jan',
                        );
                        $lastMonth = date('m', strtotime( 'last month') );
                        foreach( $months as $key => $month )
                        {
                            $selected = ( $key == $lastMonth ) ? ' selected="selected"' : '';
                            echo '<option value="'.$key.'"'.$selected.'>'.$month.'</option>';
                        }
                    ?>
                </select> </label>

            <label>
                <select id="year">
                    <?php
                    $thisYear = date('Y');
                    for( $i = 2010; $i <= $thisYear; $i++ )
                    {
                        $selected = ( $i == $thisYear ) ? ' selected="selected"' : '';
                        echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
                    }
                    ?>
                </select>
            </label> </li>
        <li><input type="submit" name="generate" id="btn_generate" value="Generate" onclick="generateReport(); return false;" /></li>
    </ul>
</div>
<script type="text/javascript">/* <![CDATA[ */
function generateReport()
{
    if( jQuery('#mode').attr('checked') )
    {
        generateMonthlyReport()
    } else{
        generateDailyReport();
    }
}

function generateDailyReport()
{
    $from_day = jQuery('#date_from_day').val();
    $from_month = jQuery('#date_from_month').val();
    $from_year = jQuery('#date_from_year').val();

    $to_day = jQuery('#date_to_day').val();
    $to_month = jQuery('#date_to_month').val();
    $to_year = jQuery('#date_to_year').val();

    $vendor = jQuery('#vendor').val();
    $invoiceable = jQuery('#invoiceable').attr( 'checked' );
    $model = jQuery('#model').val();

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
        'invoiceable': $invoiceable,
        'model': $model
    }, function(){ enableHighlightTD(); } );
}

function generateMonthlyReport()
{
    $month = jQuery('#month').val();
    $year = jQuery('#year').val();
    $model = jQuery('#model').val();

    $container = jQuery('#generated_results');
    $container.html('generating report...');

    $container.load( '<?php echo url_for( 'invoice_ui/GenerateMonthlyReport'); ?>', {
        'month': $month,
        'year': $year,
        'model': $model
    }, function(){ enableHighlightTD(); } );
}

function switchModel( button )
{
    jQuery('a.model_switch').each( function(){
        jQuery(this).removeClass('current');
    });

    jQuery(button).addClass( 'current' );
    jQuery('#model').val( jQuery(button).text().toLowerCase() );
    generateReport();
}

function switchMode( chkBox )
{
    
    $checked = jQuery(chkBox).attr( 'checked' );
    
    if($checked)
    {
        jQuery( 'label', jQuery( chkBox ).parent() ).text( 'Monthly Report' );
        jQuery('#monthly_filter').removeClass( 'hide' );
        jQuery('#daily_filter').addClass( 'hide' );
    }
    else
    {
        jQuery( 'label', jQuery( chkBox ).parent() ).text( 'Daily Report' );
        jQuery('#monthly_filter').addClass( 'hide' );
        jQuery('#daily_filter').removeClass( 'hide' );
    }
    
    generateReport();
}

function enableHighlightTD()
{
    jQuery('div#invoice_ui td').click(function() {
        jQuery(this).parent().toggleClass('highlight');
    });
    jQuery('div#invoice_ui th').click(function() {
        jQuery(this).parent().toggleClass('highlight');
    });
}

/* ]]> */</script>
