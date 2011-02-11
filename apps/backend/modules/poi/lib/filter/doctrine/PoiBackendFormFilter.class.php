<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class PoiBackendFormFilter extends BasePoiFormFilter
{
    public function configure()
    {
        $this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
        $this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));

        // add Filter widget
        $this->setWidget( 'filter_by', new sfWidgetFormChoice(
                                                            array( 'choices' =>
                                                                    array(
                                                                    'all' => 'Any Poi',
                                                                    'non-duplicate' => 'Non-Duplicate Poi',
                                                                    'master' => 'Master Poi',
                                                                    'duplicate' => 'Duplicate Poi'
                                                                    )
                                                                ),
                                                            array(
                                                                'onchange' => 'this.form.submit();'
                                                            )
                                                              )
                        );
        
        $this->setValidator( 'filter_by', new sfValidatorString(array('required' => false)) );
        
        parent::configure();
    }

    /**
     * getFields() added this dynamic custom field "reference" since it don't exists,
     * we require to override here and manually set the query string
     * @param <type> $query
     * @param <type> $field
     * @param <type> $value
     */
    public function addFilterByColumnQuery( Doctrine_Query $query, $field, $value )
    {
        $poi = $query->getRootAlias();
        
        switch( strtolower( $value ) )
        {
            case 'master':

                $query->andWhere( "$poi.id IN ( SELECT master_poi_id FROM PoiReference )" );
                break;
            case 'duplicate':
                
                $query->andWhere( "$poi.id IN ( SELECT duplicate_poi_id FROM PoiReference )" );

                break;
            case 'non-duplicate':

                $query->andWhere( "$poi.id NOT IN ( SELECT duplicate_poi_id FROM PoiReference )" );

                break;
        }
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
        $fields['filter_by'] = 'custom';
        return $fields;
    }
} 
?>
