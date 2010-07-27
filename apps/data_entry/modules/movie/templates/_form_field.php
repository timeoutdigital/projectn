<?php if ($field->isPartial()): ?>
  <?php include_partial('movie/'.$name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php elseif ($field->isComponent()): ?>
  <?php include_component('movie', $name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php else: ?>
  <div class="<?php echo $class ?><?php $form[$name]->hasError() and print ' errors' ?>">
    <?php echo $form[$name]->renderError() ?>
    <div>
      <?php echo $form[$name]->renderLabel($label) ?>
      <div class="content">
        <?php
        switch ( $name )
        {
            case 'MovieOccurrenceDataEntry' :
                $occurrenceCount = count( $form[$name] );
                $i=0;
                foreach ( $form[$name] as $MovieOccurrence )
                {
                  $i++;
                  echo $MovieOccurrence->renderHiddenFields();
                  echo $MovieOccurrence['start_date']->renderLabel();
                  echo $MovieOccurrence['start_date']->render();
                  // echo $MovieOccurrence['start_time']->renderLabel();
                  echo $MovieOccurrence['start_time']->render();
                  echo '<br/>';
                  echo $MovieOccurrence['end_date']->renderLabel();
                  echo $MovieOccurrence['end_date']->render();
                  //echo $MovieOccurrence['end_time']->renderLabel();
                  echo $MovieOccurrence['end_time']->render();
                  echo '<br/>';
                  echo $MovieOccurrence['poi_id']->renderLabel();
                  echo $MovieOccurrence['poi_id']->render();
                  if ( $i < $occurrenceCount ) echo '<hr/>';
                }
                break;
            
            case 'newMovieOccurrenceDataEntry' :
                  echo $form[$name]->renderHiddenFields();
                  echo $form[$name]['start_date']->renderLabel();
                  echo $form[$name]['start_date']->render();
                  // echo $form[$name]['start_time']->renderLabel();
                  echo $form[$name]['start_time']->render();
                  echo '<br/>';
                  echo $form[$name]['end_date']->renderLabel();
                  echo $form[$name]['end_date']->render();
                  //echo $MovieOccurrence['end_time']->renderLabel();
                  echo $form[$name]['end_time']->render();
                  echo '<br/>';
                  echo $form[$name]['poi_id']->renderLabel();
                  echo $form[$name]['poi_id']->render();
                  break;

            case 'MovieMedia' :
                
                $mediaCount = count( $form[$name] );
                $i=0;
                foreach ( $form[$name] as $MovieMedia)
                {
                  $i++;
                  echo $MovieMedia->renderHiddenFields();
                  echo $MovieMedia['url']->render(array('width' => 200));
                  if ( $i < $mediaCount ) echo '<hr/>';
                }
                if ( 0 == $mediaCount ) echo 'no image available';
                break;
            
            case 'newMovieMediaDataEntry' :
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




