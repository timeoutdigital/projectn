<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class EventBackendFormFilter extends BaseEventFormFilter
{
    public function configure()
    {
        $this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
        $this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));

        $this->setWidget( 'hide_unsolvable', new sfWidgetFormInputCheckbox( ) );
        $this->setValidator( 'hide_unsolvable', new sfValidatorPass());
    }

    public function addHideUnsolvableColumnQuery( Doctrine_Query $query, $field, $value )
    {
        $event = $query->getRootAlias();

        $query->andWhere( "$event.id NOT IN ( SELECT m.record_id FROM EventMeta m WHERE m.lookup = ?)", 'unsolvable' );
        return $query;
    }

    /**
     * This function will help adding dynamic column that can be filtered
     * @return array
     */
    public function getFields()
    {
        // Add a Custom dynamic field call "reference"
        $fields = parent::getFields();
        $fields['hide_unsolvable'] = 'custom';
        return $fields;
    }
} 
?>
