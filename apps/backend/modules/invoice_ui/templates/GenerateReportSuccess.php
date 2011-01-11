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
        <?php foreach( $data as $date => $record ): ?>
        <tr>
            <th><?php echo date('d M Y', strtotime( $date ) ); ?></th>
            <?php foreach( $uicategories as $cat ): ?>
            <td><?php
            echo isset( $record[ $cat['id'] ] ) ? $record[ $cat['id'] ] : 0;
            ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>