<?php
// This used to Hold the Category Total
    $categoryTotal = array();

    // Manipulate No Category in UI
    $uicategories[]=array( 'id' => 0, 'name' => 'NOCAT' );

?>
<table border="0" celspacing="0" colpadding="0" class="filter_info">
    <thead>
        <tr>
            <th colspan="2">Filter Info</th>
        </tr>
    </thead>
    <tr>
        <th>Vendor:</th>
        <td><?php echo ucfirst( $vendor['city'] );?></td>
    </tr>
    <tr>
        <th>Model:</th>
        <td><?php echo ucfirst( $model ); ?></td>
    </tr>
    <tr>
        <th>Date Range:</th>
        <td><?php echo date('d/m/Y', strtotime( $dateFrom ) );?><br /><?php echo date('d/m/Y', strtotime( $dateTo ) );?></td>
    </tr>
</table>

<table border="0" celspacing="0" colpadding="0">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <?php foreach( $uicategories as $cat ): ?>
            <th><?php echo $cat['name'];?></th>
            <?php endforeach; ?>
            <th>SUM()</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach( $data as $date => $record ): ?>
        <tr>
            <th><?php echo date('d M Y', strtotime( $date ) ); ?></th>
            <?php $sum = 0; foreach( $uicategories as $cat ): ?>
            <td><?php
            $uniqueThisDate = isset( $record[ $cat['id'] ] ) ? $record[ $cat['id'] ] : 0;

            if( !isset( $categoryTotal[$cat['id']]) ) $categoryTotal[$cat['id']] = 0;
            $categoryTotal[$cat['id']]+= $uniqueThisDate;

            echo $uniqueThisDate;
            $sum += $uniqueThisDate;
            ?></td>
            <?php endforeach; ?>
            <th><?php echo $sum; ?></th>
        </tr>
        <?php endforeach; ?>
    </tbody>

    <tfoot>
        <tr>
            <th>&nbsp;</th>
            <?php foreach( $uicategories as $cat ): ?>
            <th><?php echo $cat['name'];?></th>
            <?php endforeach; ?>
            <th>SUM()</th>
        </tr>
        <tr>
            <th>Total: </th>
            <?php $sum = 0;foreach( $uicategories as $cat ): ?>
            <th><?php echo $categoryTotal[$cat['id']]; $sum += $categoryTotal[$cat['id']]; ?></th>
            <?php endforeach; ?>
            <th><?php echo $sum; ?></th>
        </tr>
    </tfoot>
</table>
