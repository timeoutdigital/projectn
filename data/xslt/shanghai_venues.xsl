<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" cdata-section-elements="name house_no street city district additional_address_details country postcode long lat short_description description price_information phone timeout_url name" />
	<xsl:template match="/">
		<xsl:element name="venues">
			<xsl:for-each select="//venues/venue">
				<xsl:if test="city[text()='上海']">
					<xsl:element name="venue">

						<xsl:attribute name="id">
							<xsl:value-of select="@id" />
						</xsl:attribute>

						<xsl:copy-of select="*" />

						<xsl:if test="name( following-sibling::*[1] ) = 'categories'">
							<xsl:copy-of select="following-sibling::*[1]" />
						</xsl:if>

					</xsl:element>
				</xsl:if>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
