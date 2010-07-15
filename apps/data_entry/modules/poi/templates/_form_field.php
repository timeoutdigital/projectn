<?php use_helper('jQuery'); ?>

<?php if ($field->isPartial()): ?>
  <?php include_partial('poi/'.$name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php elseif ($field->isComponent()): ?>
  <?php include_component('poi', $name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php else: ?>
  <div class="<?php echo $class ?><?php $form[$name]->hasError() and print ' errors' ?>">
    <?php echo $form[$name]->renderError() ?>
    <div>
      <?php echo $form[$name]->renderLabel($label) ?>
      <div class="content">
        <?php
        switch ( $name )
        {
            case 'PoiOccurrenceDataEntry' :
                $occurrenceCount = count( $form[$name] );
                $i=0;
                foreach ( $form[$name] as $PoiOccurrence )
                {
                  $i++;
                  echo $PoiOccurrence->renderHiddenFields();
                  echo $PoiOccurrence['start_date']->renderLabel();
                  echo $PoiOccurrence['start_date']->render();
                  // echo $PoiOccurrence['start_time']->renderLabel();
                  echo $PoiOccurrence['start_time']->render();
                  echo '<br/>';
                  echo $PoiOccurrence['end_date']->renderLabel();
                  echo $PoiOccurrence['end_date']->render();
                  //echo $PoiOccurrence['end_time']->renderLabel();
                  echo $PoiOccurrence['end_time']->render();
                  echo '<br/>';
                  echo $PoiOccurrence['poi_id']->renderLabel();
                  echo $PoiOccurrence['poi_id']->render();
                  if ( $i < $occurrenceCount ) echo '<hr/>';
                }
                break;
            
            case 'newPoiOccurrenceDataEntry' :
                  echo $form[$name]->renderHiddenFields();
                  echo $form[$name]['start_date']->renderLabel();
                  echo $form[$name]['start_date']->render();
                  // echo $form[$name]['start_time']->renderLabel();
                  echo $form[$name]['start_time']->render();
                  echo '<br/>';
                  echo $form[$name]['end_date']->renderLabel();
                  echo $form[$name]['end_date']->render();
                  //echo $PoiOccurrence['end_time']->renderLabel();
                  echo $form[$name]['end_time']->render();
                  echo '<br/>';
                  echo $form[$name]['poi_id']->renderLabel();
                  echo $form[$name]['poi_id']->render();
                  break;

            case 'PoiMedia' :
                
                $mediaCount = count( $form[$name] );
                $i=0;
                foreach ( $form[$name] as $PoiMedia)
                {
                  $i++;
                  echo $PoiMedia->renderHiddenFields();
                  echo $PoiMedia['url']->render(array('width' => 200));
                  if ( $i < $mediaCount ) echo '<hr/>';
                }
                if ( 0 == $mediaCount ) echo 'no image available';
                break;
            
            case 'newPoiMediaDataEntry' :
                  echo $form[$name]->renderHiddenFields();
                  echo $form[$name]['url']->render();
                  break;
              
            default :
                echo $form[$name]->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes);
        }
        ?>
      </div>
      <?php if ($help): ?>
        <div class="help"><?php echo __($help, array(), 'messages') ?></div>
      <?php elseif ($help = $form[$name]->renderHelp()): ?>
        <div class="help"><?php echo $help ?></div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>




