<?php

/**
 * XmlConcatenator - Concatenates Simple XML Files.
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 * @description
 *
 * The first simplexml file is used as a parent document, the additional simplexml
 * documents are searched using the xpath string provided and appended to the parent.
 *
 * The default target for the copied nodes is the direct parent of the children
 * in the parent document.
 *
 * @example
 * 
 * $xml1 = simplexml_load_string( '<root><parent><child id="1"><![CDATA[1]]></child></parent></root>' );
 * $xml2 = simplexml_load_string( '<root><parent><child id="2"><![CDATA[2]]></child></parent></root>' );
 *
 * $concatXML = XmlConcatenator::concatXML( array( $xml1, $xml2 ), '//parent/child' );
 * echo $concatXML->save();
 *
 * @result
 * 
 * <root><parent><child id="1"><![CDATA[1]]></child><child id="2"><![CDATA[2]]></child></parent></root>
 * 
 */

class XmlConcatenator
{
    public static function concatXML( array $simpleXMLStack, /* string */ $xpath )
    {
        try {
            $parseXpath = explode( '/', trim( $xpath, '/' ) );

            $returnDomObject = new DomDocument();
            $returnDomObject->loadXML( $simpleXMLStack[ 0 ]->asXML() );

            foreach( $simpleXMLStack as $k => $simpleXMLDocument )
            {
                if( $k === 0 ) continue;

                $domObject = new DomDocument();
                $domObject->loadXML( $simpleXMLDocument->asXML() );
                $domXpath       = new domXPath( $domObject );
                $xpathQuery     = $domXpath->query( $xpath );
                $parentElement  = $parseXpath[ count( $parseXpath ) - 2 ];

                for( $i = 0; $i < $xpathQuery->length; $i++ )
                {
                    $returnDomObject->getElementsByTagName( $parentElement )->item(0)
                            ->appendChild( $returnDomObject->importNode( $xpathQuery->item( $i ), true ) );
                }
            }

            return simplexml_import_dom( $returnDomObject );
        }
        catch( Exception $e )
        {
            throw new XmlConcatenatorException( 'XmlConcatenator Error', null, $e );
        }
    }
}

class XmlConcatenatorException extends Exception {}