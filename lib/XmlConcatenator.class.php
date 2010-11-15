<?php

/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class XmlConcatenator
{
    public static function concatXML( array $simpleXMLStack, /* string */ $xpath )
    {
        $parseXpath = explode( '/', trim( $xpath, '/' ) );

        $returnDomObject = new DomDocument();
        $returnDomObject->loadXML( $simpleXMLStack[ 0 ]->asXML() );

        foreach( $simpleXMLStack as $k => $simpleXMLDocument )
        {
            if( $k === 0 ) continue;

            $domObject = new DomDocument();
            $domObject->loadXML( $simpleXMLDocument->asXML() );
            $domXpath   = new domXPath( $domObject );
            $xpathQuery = $domXpath->query( $xpath );
            
            for( $i = 0; $i < $xpathQuery->length; $i++ )
            {
                $parentElement = $parseXpath[ count( $parseXpath ) - 2 ];
                $returnDomObject->getElementsByTagName( $parentElement )->item(0)
                        ->appendChild( $returnDomObject->importNode( $xpathQuery->item( $i ), true ) );
            } 
        }

        return simplexml_import_dom( $returnDomObject );
    }
}
?>
