<?php
/**
 * Adds guisable functionality to a Model
 *
 * @package projectn
 * @subpackage guisable.behaviours.model.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 * @see based on the Doctrine AuditLog Plugin
 * @version 1.0.1
 *
 * @todo maybe implement some caching
 * 
 */
class Guisable extends Doctrine_Template
{
    /**
     * Current guise
     * 
     * @var mixed
     */
    private $_currentGuise = false;



    /**
     * __construct
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->_plugin = new Guise();
    }

    /**
     * setup
     */
    public function setUp()
    {
        $this->_plugin->initialize($this->_table);
        $this->addListener( new GuisableListener() );
    }

    /**
     * Change the guise of a Doctrine_Record if a guise is found for the content
     * of the field passed to the function
     *
     * @param string $field
     * @return boolean
     */
    public function useGuiseByFieldContent( $field )
    {
        $invoker = $this->getInvoker();

        if ( ! isset( $invoker[ $field ] )  )
        {
            return false;
        }

        $guise = $invoker[ $field ];

        if ( ! $this->guiseExists( $guise ) )
        {
            return false;
        }

        return $this->useGuise( $guise );
    }

    /**
     * Returns if a guise is in use
     *
     * @return boolean
     */
    public function isInGuise()
    {
        return ( $this->_currentGuise === false ) ? false : true;
    }

    /**
     * Returns current guise
     *
     * @return mixed
     */
    public function getCurrentGuiseInUse()
    {
        return $this->_currentGuise;
    }

    /**
     * Stops current use of guise
     */
    public function stopUsingGuise()
    {
        if ( $this->isInGuise() )
        {
            $this->getInvoker()->refresh();
            $this->_currentGuise = false;
        }
    }

    /**
     * Checks if a guise exists
     * 
     * @param string $guise
     * @return boolean 
     */
    public function guiseExists( $guise )
    {
        $data = $this->_plugin->getGuise($this->getInvoker(), $guise );

        return ( $data === false ) ? false : true;
    }

    /**
     * Changes the guys of a Doctrine_Record
     * 
     * @param string $guise
     * @@param boolean $clearPreviousGuiseFirst
     * @return boolean
     */
    public function useGuise( $guise, $clearPreviousGuiseFirst = true )
    {
        if ( $clearPreviousGuiseFirst )
        {
            $this->stopUsingGuise();
        }

        $data = $this->_plugin->getGuise($this->getInvoker(), $guise );

        if ( $data === false ) {
            throw new Doctrine_Record_Exception('Guisable ' . $guise . ' does not exist!');
        }

        $this->getInvoker()->merge($data);
        //set state to Doctrine_Record::STATE_CLEAN as we loose it through the merge() function
        $this->getInvoker()->state(Doctrine_Record::STATE_CLEAN);

        $this->_currentGuise = $guise;

        return $this->_currentGuise;
    }

    /**
     * Changes the guys of a Doctrine_Record if a guise is found
     *
     * @param string $guise
     * @@param boolean $clearPreviousGuiseFirst
     * @return boolean
     */
    public function useGuiseIfExists( $guise, $clearPreviousGuiseFirst = true )
    {
        return $this->guiseExists( $guise ) ? $this->useGuise( $guise, $clearPreviousGuiseFirst ) : false;
    }
}

?>
