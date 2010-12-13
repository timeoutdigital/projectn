<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : poiExport2Kml.xsl
    Created on : 08 December 2010, 18:14
    Author     : Peter Johnson
    Description: Transforms Our Poi Exports to Google Earth KML Format.
    Usage      : $ 4xslt summit.xml poiExport2Kml.xsl
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml" encoding="UTF-8" indent="yes" cdata-section-elements="name description" />

    <xsl:template match="/">
        <kml xmlns="http://earth.google.com/kml/2.2">

            <xsl:element name="Document">

                <xsl:element name="name">Timeout</xsl:element>

                <!--
                <xsl:element name="Style">
                    <xsl:attribute name="id">Timeout</xsl:attribute>
                    <xsl:element name="IconStyle">
                        <xsl:element name="scale">1</xsl:element>
                        <xsl:element name="Icon">
                            <xsl:element name="href">http://www.toimg.net/travel/images/logos/london.gif</xsl:element>
                        </xsl:element>
                    </xsl:element>
                </xsl:element>
                -->

                <xsl:for-each select="/vendor-pois/entry">

                    <xsl:element name="Placemark">

                        <xsl:copy-of select="name" />
                        <!--<xsl:element name="styleUrl">#Timeout</xsl:element>-->
                        <xsl:copy-of select="version/content/description" />

                        <xsl:element name="Point">
                            <xsl:element name="coordinates">
                                <xsl:value-of select="geo-position/longitude" />,<xsl:value-of select="geo-position/latitude" />
                            </xsl:element>
                        </xsl:element>

                    </xsl:element>
                </xsl:for-each>
           </xsl:element>

        </kml>
    </xsl:template>

</xsl:stylesheet>
