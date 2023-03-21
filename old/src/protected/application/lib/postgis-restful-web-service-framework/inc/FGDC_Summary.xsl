<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!-- Sample custom stylesheet created 01/05/2000 by avienneau-->

  <!-- When the root node in the XML is encountered (the metadata element), the  
       HTML template is set up. -->
  <xsl:template match="/">
    <HTML>

	
      <!-- The BODY secton defines the content of the HTML page. This page has a 1/4 
           inch margin, a background color, and the default text size used is 13pt. -->
      <BODY STYLE="margin-left:0.25in; margin-right:0.25in; font-size:13pt">

        <!-- TITLE. The xsl:value-of element selects the title element in the XML 
             file, then places its data inside a DIV element. Because an XSL stylesheet 
             is an XML file, all XSL and HTML elements must be well-formed; that is, 
             they must be properly closed. The /DIV closes the opening division tag. 
             The value-of and break (BR) elements are closed by adding the / at the end. --> 
	<TABLE WIDTH="100%" BGCOLOR="#FFFACD" BORDER="0">
	  <TR>
	    <TD>
                        <DIV STYLE="font-size:24; font-weight:bold; color:#8B0000; text-align:center">
          	      <xsl:value-of select="metadata/idinfo/citation/citeinfo/title"/>
        	      </DIV>

         <!-- PUBLISHER. Add the publisher on a new line. -->
                          <DIV STYLE="text-align:center">
          	      <xsl:value-of select="metadata/idinfo/citation/citeinfo/pubinfo/publish"/>
        	      </DIV>

	    </TD>
	  </TR>
	</TABLE>
        <BR/>

		<!-- THUMBNAIL.  Add the thumbnail image, centered. -->
		<xsl:if test="/metadata/Binary/Thumbnail/src != ''">
			<DIV STYLE="text-align:center">
				<IMG border="1">
					<xsl:attribute name="SRC">
				<xsl:value-of select="/metadata/Binary/Thumbnail/src"/>
			</xsl:attribute>
			</IMG>
			</DIV>
		</xsl:if>
		
        <BR/>


  <!-- BROWSE GRAPHIC. Add the browse grapic data to the page. If the 
             metadata doesn't have an browse graphic element or if it contains no data, 
             no text appears. -->
        <xsl:if test="metadata/idinfo/browse/browsen[. != '']">
      <DIV STYLE="font-weight:bold; color:#B22222">
	<xsl:value-of select="metadata/idinfo/browse/browsed" disable-output-escaping="yes"/>:
        </DIV>
        <xsl:for-each select="metadata/idinfo/browse/browsen">
          <DIV STYLE="margin-left:0.25in">
            <A TARGET="_blank"><xsl:attribute name="HREF"><xsl:value-of select="."/></xsl:attribute><xsl:value-of select="."/></A>
          </DIV>
        </xsl:for-each>
        <BR/>
        </xsl:if>



         <!-- ABSTRACT. Add the abstract data to the page. If the 
             metadata doesn't have an abstract element or if it contains no data, 
             no text appears below. -->
        <DIV STYLE="font-weight:bold; color:#B22222">
          Abstract:
        </DIV>
        <DIV STYLE="margin-left:0.25in">
          <xsl:value-of select="metadata/idinfo/descript/abstract" disable-output-escaping="yes"/> 
        </DIV>
        <BR/>



       <!-- PURPOSE. Add the purpose data to the page. If the 
             metadata doesn't have an abstract element or if it contains no data, 
             no text appears. -->
        <DIV STYLE="font-weight:bold; color:#B22222">
          Purpose:
        </DIV>
        <DIV STYLE="margin-left:0.25in">
          <xsl:value-of select="metadata/idinfo/descript/purpose" disable-output-escaping="yes"/>
        </DIV>
        <BR/>


      <!-- RESOURCE DESCRIPTION. Add the purpose data to the page. If the 
             metadata doesn't have an abstract element or if it contains no data, 
             no text appears. -->
        <xsl:if test="metadata/distinfo/resdesc[. != '']">
        <DIV STYLE="font-weight:bold; color:#B22222">
          Resource Description:
        </DIV>
        <DIV STYLE="margin-left:0.25in">
          <xsl:value-of select="metadata/distinfo/resdesc"/>
        </DIV>
        <BR/>
        </xsl:if>


      <!-- GEOSPATIAL DATA PRESENTATION FORM. Add the purpose data to the page. If the 
             metadata doesn't have an abstract element or if it contains no data, 
             no text appears. -->
        <xsl:if test="metadata/idinfo/citation/citeinfo/geoform[. != '']">
        <DIV STYLE="font-weight:bold; color:#B22222">
          Resource Form:
        </DIV>
        <DIV STYLE="margin-left:0.25in">
          <xsl:value-of select="metadata/idinfo/citation/citeinfo/geoform"/>
        </DIV>
        <BR/>
        </xsl:if>


      <!-- FORMAT NAME. Add the purpose data to the page. If the 
             metadata doesn't have an abstract element or if it contains no data, 
             no text appears. -->
        <xsl:if test="metadata/distinfo/stdorder/digform/digtinfo/formname[. != '']">
        <DIV STYLE="font-weight:bold; color:#B22222">
          Format Name:
        </DIV>
        <DIV STYLE="margin-left:0.25in">
          <xsl:value-of select="metadata/distinfo/stdorder/digform/digtinfo/formname"/>
        </DIV>
        <BR/>
        </xsl:if>



        <!-- ONLINE LINKAGE. Add the online linkage on a new line. The 
             xsl:for-each element loops through each element in the metadata, 
             and for each one adds a DIV element to the page. The xsl:value-of 
             element places the data in the currently selected origin tag inside. --> 
        <xsl:if test="metadata/idinfo/citation/citeinfo/onlink[. != '']">

        <DIV STYLE="font-weight:bold; color:#B22222">
          Resource URL:
        </DIV>
        <xsl:for-each select="metadata/idinfo/citation/citeinfo/onlink">
          <DIV STYLE="margin-left:0.25in">
            <A TARGET=""><xsl:attribute name="HREF"><xsl:value-of select="."/></xsl:attribute><xsl:value-of select="."/></A>
          </DIV>
        </xsl:for-each>
        <BR/>
        </xsl:if>

        <!-- COORDINATE SYSTEM. These xsl:if elements test whether or not the 
             coordinate system name elements exist within the metadata and if they 
             contain a value. If so, an appropriate label and the coordinate system 
             name are placed on the page. -->
  



<xsl:choose>
	<xsl:when test="metadata/spref/horizsys/cordsysn/projcsn[. != '']">
      <DIV STYLE="font-weight:bold; color:#B22222">
          Spatial Reference:
        </DIV>
                		<DIV STYLE="margin-left:0.25in">
            		<SPAN STYLE="font-weight:bold">Projected: </SPAN>
            		<xsl:value-of select="metadata/spref/horizsys/cordsysn/projcsn"/>
          		</DIV>
        	</xsl:when>
	<xsl:otherwise>
        	<xsl:if test="metadata/spref/horizsys/cordsysn/geogcsn[. != '']">
      <DIV STYLE="font-weight:bold; color:#B22222">
          Spatial Reference:
        </DIV>
	          	<DIV STYLE="margin-left:0.25in">
            		<SPAN STYLE="font-weight:bold">Geographic: </SPAN>
            		<xsl:value-of select="metadata/spref/horizsys/cordsysn/geogcsn"/>
          		</DIV>
        	</xsl:if>
     	</xsl:otherwise>
</xsl:choose>


<xsl:if test="metadata/spref/horizsys/geodetic/horizdn[. != '']">
	<DIV STYLE="margin-left:0.25in">
	<SPAN STYLE="font-weight:bold">Datum: </SPAN>
            	<xsl:value-of select="metadata/spref/horizsys/geodetic/horizdn"/>
          	</DIV>
</xsl:if>

        <BR/><BR/>
      </BODY>

    </HTML>
  </xsl:template>
</xsl:stylesheet>