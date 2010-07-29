<?php


class widgetFormInputTextJQueryAutocompleter extends sfWidgetFormJQueryAutocompleter
{
 
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $visibleValue = $this->getOption('value_callback') ? call_user_func($this->getOption('value_callback'), $value) : $value;

    return $this->renderTag('input', array('name' => $name, 'value' => $value)).
           
           sprintf(<<<EOF
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery("#%s")
    .autocomplete('%s', jQuery.extend({}, {
      dataType: 'json',
      parse:    function(data) {
        var parsed = [];
        for (key in data) {
          parsed[parsed.length] = { data: [ data[key], key ], value: data[key], result: data[key] };
        }
        return parsed;
      }
    }, %s))
    .result(function(event, data) { jQuery("#%s").val(data[0]); });
  });
</script>
EOF
      ,
      $this->generateId($name),
      $this->getOption('url'),
      $this->getOption('config'),
      $this->generateId($name)
    );
  }

}
