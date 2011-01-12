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
        <th>Model:</th>
        <td><?php echo ucfirst( $model ); ?></td>
    </tr>
    <tr>
        <th>Date Range:</th>
        <td><?php echo date('d/m/Y', strtotime( $dateRange['from'] ) );?><br /><?php echo date('d/m/Y', strtotime( $dateRange['to'] ) );?></td>
    </tr>
</table>

<table border="0" celspacing="0" colpadding="0">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <?php foreach( $uicategories as $cat ): ?>
            <th><?php echo $cat['name'];?></th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php asort($vendorList); foreach( $vendorList as $key => $city ): ?>
        <tr>
            <th><?php echo ucfirst( $city ); ?></th>
            <?php foreach( $uicategories as $cat ): ?>
            <td><?php
            $uniqueThisDate = isset( $vendorResults[ $key ][ $cat['id'] ] ) ? $vendorResults[ $key ][ $cat['id'] ] : 0;

            if( !isset( $categoryTotal[$cat['id']]) ) $categoryTotal[$cat['id']] = 0;
            $categoryTotal[$cat['id']]+= $uniqueThisDate;

            echo $uniqueThisDate;
            ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>

    <tfoot>
        <tr>
            <th>&nbsp;</th>
            <?php foreach( $uicategories as $cat ): ?>
            <th><?php echo $cat['name'];?></th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th>Total: </th>
            <?php foreach( $uicategories as $cat ): ?>
            <th><?php echo $categoryTotal[$cat['id']];?></th>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>