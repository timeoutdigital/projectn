<?php
/**
 * Creates instances from an array configuration:
 * 
 * <code>
 * $config = array( 
 *     'class'  => 'SomeClass',
 *     'param1' => 'a',
 *     'b'      => 'c',
 * );
 * 
 * $factory = new arrayInstanceFactory( $config );
 * $instance1 = $factory->createInstance();
 * $instance2 = $factory->createInstance();
 *
 * </code>
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class arrayInstanceFactory
{
    private $_constructorArguments;

    public function __construct( array $configuration )
    {
        if( !isset( $configuration[ 'class' ] ) )
        {
            throw new arrayInstanceFactoryException( 'Missing "class" in configuration array.' );
        }

        $reflection  = new ReflectionClass( $configuration[ 'class' ] );
        $constructor = $reflection->getConstructor();
        $parameters  = $constructor->getParameters();

        $constructorArguments = array(); 

        foreach( $parameters as $param )
        {
            $paramName = $param->getName();

            if( !isset( $configuration[ $paramName ] ) )
            {
                continue;
            }

            $constructorArguments[ $paramName ] = $configuration[ $paramName ];
        }

        $this->_reflection = $reflection;
        $this->_constructorArguments = $constructorArguments;
    }

    public function createInstance()
    {
        return $this->_reflection->newInstanceArgs( $this->_constructorArguments );
    }
}

class arrayInstanceFactoryException extends Exception{}
