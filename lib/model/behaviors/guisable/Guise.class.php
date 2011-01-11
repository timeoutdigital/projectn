<?php
/**
 * Supports the GuisableBehaviour to enable guisable behaviour on models
 *
 * @package projectn
 * @subpackage guisable.behaviours.model.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 * @see based on the Doctrine AuditLog Plugin
 * @version 1.0.1
 *
 */

class GuiseException extends Exception {}

class Guise extends Doctrine_Record_Generator
{
    /**
     * Array of Guise Options
     *
     * @var array
     */
    protected $_options = array('className'       => '%CLASS%Guise',
                                'guise'           => array( 'name'   => 'guise',
                                                            'alias'  => null,
                                                            'type'   => 'string',
                                                            'length' => 20,
                                                            'options' => array('primary' => true)),
                                'generateFiles'  => false,
                                'generatePath'   => false,
                                'builderOptions' => array(),
                                'identifier'     => false,
                                'table'          => false,
                                'pluginTable'    => false,
                                'children'       => array(),
                                'cascadeDelete'  => true,
                                'appLevelDelete' => false);


    /**
     * Accepts array of options to configure the Guise
     *
     * @param   array $options An array of options
     */
    public function __construct(array $options = array())
    {
       $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
    }

    public function buildRelation()
    {
        $this->buildForeignRelation('Guise');
        $this->buildLocalRelation();
    }

    public function setTableDefinition()
    {
        $name = $this->_options['table']->getComponentName();

        // Building columns
        $columns = $this->_options['table']->getColumns();

        // remove all sequence, autoincrement and unique constraint definitions and add to the behavior model
        foreach ($columns as $column => $definition) {
            unset($definition['autoincrement']);
            unset($definition['sequence']);
            unset($definition['unique']);

            $fieldName = $this->_options['table']->getFieldName($column);
            if ($fieldName != $column) {
                $name = $column . ' as ' . $fieldName;
            } else {
                $name = $fieldName;
            }

            $this->hasColumn($name, $definition['type'], $definition['length'], $definition);
        }

        // the guise column should be part of the primary key definition
        $this->hasColumn(
	    $this->_options['guise']['name'],
            $this->_options['guise']['type'],
            $this->_options['guise']['length'],
            $this->_options['guise']['options'] );

    }
    
    /**
     * Get array of information for the passed record and the specified guise
     *
     * @param   Doctrine_Record $record
     * @param   string $guise
     */
    public function getGuise( Doctrine_Record $record, $guise )
    {
        $className = $this->_options['className'];

        $q = Doctrine_Core::getTable($className)->createQuery();

        $values = array();

        foreach ((array) $this->_options['table']->getIdentifier() as $id) {
            $conditions[] = $className . '.' . $id . ' = ?';
            $values[] = $record->get($id);
        }

        $where = implode(' AND ', $conditions) . ' AND ' . $className . '.' . $this->_options['guise']['name'] . ' = ?';

        $values[] = $guise;

        $q->where($where);

        return $q->fetchOne( $values, Doctrine_Core::HYDRATE_ARRAY );
    }

}