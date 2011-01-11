
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/global.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/sfDoctrinePlugin/css/default.css" />
<?php use_helper('jQuery'); ?>
<div id="invoice_ui">
    <?php
    include_partial( 'filterOptions', array( 'date' => $date ) );
    ?>
    <div id="generated_results" class="clearfix">

    </div>

</div>