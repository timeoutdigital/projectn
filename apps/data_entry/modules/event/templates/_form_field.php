<?php if ($field->isPartial()): ?>
  <?php include_partial('event/'.$name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php elseif ($field->isComponent()): ?>
  <?php include_component('event', $name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php else: ?>
  <div class="<?php echo $class ?><?php $form[$name]->hasError() and print ' errors' ?>">
    <?php echo $form[$name]->renderError() ?>
    <div>
      <?php echo $form[$name]->renderLabel($label) ?>
      <div class="content">
        <?php
        switch ( $name )
        {
            case 'EventOccurrence' :
                $occurrenceCount = count( $form[$name] );
                $i=0;
                foreach ( $form[$name] as $EventOccurrence )
                {
                  $i++;
                  echo $EventOccurrence->renderHiddenFields();
                  echo $EventOccurrence['start_date']->renderLabel();
                  echo $EventOccurrence['start_date']->render();
                  // echo $EventOccurrence['start_time']->renderLabel();
                  echo $EventOccurrence['start_time']->render();
                  echo '<br/>';
                  echo $EventOccurrence['end_date']->renderLabel();
                  echo $EventOccurrence['end_date']->render();
                  //echo $EventOccurrence['end_time']->renderLabel();
                  echo $EventOccurrence['end_time']->render();
                  echo '<br/>';
                  echo $EventOccurrence['poi_id']->renderLabel();
                  echo $EventOccurrence['poi_id']->render();
                  if ( $i < $occurrenceCount ) echo '<hr/>';
                }
                if ( 0 == $occurrenceCount ) echo 'no occurrence available';
                break;
            
            case 'newEventOccurrenceDataEntry' :
                  echo $form[$name]->renderHiddenFields();
                  echo $form[$name]['start_date']->renderLabel();
                  echo $form[$name]['start_date']->render();
                  // echo $form[$name]['start_time']->renderLabel();
                  echo $form[$name]['start_time']->render();
                  echo '<br/>';
                  echo $form[$name]['end_date']->renderLabel();
                  echo $form[$name]['end_date']->render();
                  //echo $EventOccurrence['end_time']->renderLabel();
                  echo $form[$name]['end_time']->render();
                  echo '<br/>';
                  echo $form[$name]['poi_id']->renderLabel();
                  echo $form[$name]['poi_id']->render();
                  break;

            case 'EventMedia' :
                
                $mediaCount = count( $form[$name] );
                $i=0;
                foreach ( $form[$name] as $EventMedia)
                {
                  $i++;
                  echo $EventMedia->renderHiddenFields();
                  echo $EventMedia['url']->render(array('width' => 200));
                  if ( $i < $mediaCount ) echo '<hr/>';
                }
                if ( 0 == $mediaCount ) echo 'no image available';
                break;
            
            case 'newEventMediaDataEntry' :
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




