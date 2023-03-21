<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<!-- An xsl template for displaying metadata in ArcInfo8 with the traditional FGDC look and feel created by mp

	Copyright (c) 1999-2005, Environmental Systems Research Institute, Inc. All rights reserved.
	
	Revision History:
		Created 6/7/99 avienneau
		Updated 3/7/00 avienneau
		Modified 7/04/06 by Howie Sternberg - Modified to support W3C DOM compatible
		browsers such as IE6, Netscape 7, and Mozilla Firefox using different
		Javascript for parsing text to respect line breaks in metadata when page loads:
		1.  Added window.onload function, which calls fixvalue() Javascript function.
		2.  Replaced fix() with fixvalue() and addtext() Javascript functions.
		3.  Replaced <xsl:value-of/> with <xsl:value-of select="."/>.
		4.  Replaced XSL code for building Distribution_Information links, using position() and last().
		5.  Replaced <xsl:stylesheet xmlns:xsl="http://www.w3.org/TR/WD-xsl" TYPE="text/javascript">
		    with <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
		6.  Replaced <SCRIPT><xsl:comment><![CDATA[
		    with <script type="text/javascript" language="JavaScript1.3"><![CDATA[
		7.  Replaced <PRE ID="original"><xsl:eval>this.text</xsl:eval></PRE><SCRIPT>fix(original)</SCRIPT>
		    with <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>, removing enclosing DIV elements if present.
		8.  Lowercased all HTML element and attribute names.
		Modified 7/04/06 by Howie Sternberg - Modified stylesheet for use by fgdc_faq_onload.js,
		which is loaded by FGDC_FAQ.htm. Removed <html>, <head>, <script> and <body> elements.
-->

<xsl:template match="/">

    <a name="Top"/>
    <h1><xsl:value-of select="metadata/idinfo/citation/citeinfo/title"/></h1>
    <h2>Metadata:</h2>

    <ul>
      <xsl:for-each select="metadata/idinfo">
        <li><a href="#Identification_Information">Identification_Information</a></li>
      </xsl:for-each>
      <xsl:for-each select="metadata/dataqual">
        <li><a href="#Data_Quality_Information">Data_Quality_Information</a></li>
      </xsl:for-each>
      <xsl:for-each select="metadata/spdoinfo">
        <li><a href="#Spatial_Data_Organization_Information">Spatial_Data_Organization_Information</a></li>
      </xsl:for-each>
      <xsl:for-each select="metadata/spref">
        <li><a href="#Spatial_Reference_Information">Spatial_Reference_Information</a></li>
      </xsl:for-each>
      <xsl:for-each select="metadata/eainfo">
        <li><a href="#Entity_and_Attribute_Information">Entity_and_Attribute_Information</a></li>
      </xsl:for-each>
      <xsl:for-each select="metadata/distinfo">
      <xsl:choose>
        <xsl:when test="position() = 1">
          <xsl:choose>
            <xsl:when test="position() = last()">
              <li><a><xsl:attribute name="href"><xsl:text>#Distributor</xsl:text><xsl:value-of select="position()"/></xsl:attribute>Distribution_Information</a></li>
            </xsl:when>
            <xsl:otherwise>		
              <li>Distribution_Information</li>
              <li STYLE="margin-left:0.3in">
                <a><xsl:attribute name="href"><xsl:text>#Distributor</xsl:text><xsl:value-of select="position()"/></xsl:attribute>Distributor <xsl:value-of select="position()"/></a>
              </li>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:otherwise>
            <li STYLE="margin-left:0.3in">
              <a><xsl:attribute name="href"><xsl:text>#Distributor</xsl:text><xsl:value-of select="position()"/></xsl:attribute>Distributor <xsl:value-of select="position()"/></a>
            </li>
          </xsl:otherwise>
       </xsl:choose>
      </xsl:for-each>
      <xsl:for-each select="metadata/metainfo">
        <li><a href="#Metadata_Reference_Information">Metadata_Reference_Information</a></li>
      </xsl:for-each>
    </ul>

    <xsl:apply-templates select="metadata/idinfo"/>
    <xsl:apply-templates select="metadata/dataqual"/>
    <xsl:apply-templates select="metadata/spdoinfo"/>
    <xsl:apply-templates select="metadata/spref"/>
    <xsl:apply-templates select="metadata/eainfo"/>
    <xsl:apply-templates select="metadata/distinfo"/>
    <xsl:apply-templates select="metadata/metainfo"/>

  <!-- <br/><br/><br/><center><font color="#6495ED">Metadata stylesheets are provided courtesy of ESRI.  Copyright (c) 1999-2004, Environmental Systems Research Institute, Inc.  All rights reserved.</font></center> -->

</xsl:template>

<!-- Identification -->
<xsl:template match="idinfo">
  <a name="Identification_Information"><hr/></a>
  <dl>
    <dt><i>Identification_Information: </i></dt>
    <dd>
    <dl>
      <xsl:for-each select="citation">
        <dt><i>Citation: </i></dt>
        <dd>
        <dl>
          <xsl:apply-templates select="citeinfo"/>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="descript">
        <dt><i>Description: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="abstract">            
            <dt><i>Abstract: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>            
          </xsl:for-each>
          <xsl:for-each select="purpose">           
            <dt><i>Purpose: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>           
          </xsl:for-each>
          <xsl:for-each select="supplinf">           
            <dt><i>Supplemental_Information: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>            
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="timeperd">
        <dt><i>Time_Period_of_Content: </i></dt>
        <dd>
        <dl>
          <xsl:apply-templates select="timeinfo"/>
          <xsl:for-each select="current">
            <dt><i>Currentness_Reference: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>  
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="status">
        <dt><i>Status: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="progress">
            <dt><i>Progress: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="update">
            <dt><i>Maintenance_and_Update_Frequency: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="spdom">
        <dt><i>Spatial_Domain: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="bounding">
            <dt><i>Bounding_Coordinates: </i></dt>
            <dd>
            <dl>
              <dt><i>West_Bounding_Coordinate: </i> <xsl:value-of select="westbc"/></dt>
              <dt><i>East_Bounding_Coordinate: </i> <xsl:value-of select="eastbc"/></dt>
              <dt><i>North_Bounding_Coordinate: </i> <xsl:value-of select="northbc"/></dt>
              <dt><i>South_Bounding_Coordinate: </i> <xsl:value-of select="southbc"/></dt>
            </dl>
            </dd>
          </xsl:for-each>
          <xsl:for-each select="dsgpoly">
            <dt><i>Data_Set_G-Polygon: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="dsgpolyo">
                <dt><i>Data_Set_G-Polygon_Outer_G-Ring: </i></dt>
                <dd>
                <dl>
                  <xsl:apply-templates select="grngpoin"/>
                  <xsl:apply-templates select="gring"/>
                </dl>
                </dd>
              </xsl:for-each>
              <xsl:for-each select="dsgpolyx">
                <dt><i>Data_Set_G-Polygon_Exclusion_G-Ring: </i></dt>
                <dd>
                <dl>
                  <xsl:apply-templates select="grngpoin"/>
                  <xsl:apply-templates select="gring"/>
                </dl>
                </dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="keywords">
        <dt><i>Keywords: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="theme">
            <dt><i>Theme: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="themekt">
                <dt><i>Theme_Keyword_Thesaurus: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="themekey">
                <dt><i>Theme_Keyword: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="place">
            <dt><i>Place: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="placekt">
                <dt><i>Place_Keyword_Thesaurus: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="placekey">
                <dt><i>Place_Keyword: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="stratum">
            <dt><i>Stratum: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="stratkt">
                <dt><i>Stratum_Keyword_Thesaurus: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="stratkey">
                <dt><i>Stratum_Keyword: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
 
          <xsl:for-each select="temporal">
            <dt><i>Temporal: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="tempkt">
                <dt><i>Temporal_Keyword_Thesaurus: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="tempkey">
                <dt><i>Temporal_Keyword: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="accconst">
        <dt><i>Access_Constraints: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
      <xsl:for-each select="useconst">
        <dt><i>Use_Constraints: </i></dt>
        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
      </xsl:for-each>

      <xsl:for-each select="ptcontac">
        <dt><i>Point_of_Contact: </i></dt>
        <dd>
        <dl>
          <xsl:apply-templates select="cntinfo"/>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="browse">
        <dt><i>Browse_Graphic: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="browsen">
            <dt><i>Browse_Graphic_File_Name: </i> <a target="viewer">
              <xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
              <xsl:value-of select="."/></a>
            </dt>
          </xsl:for-each>
          <xsl:for-each select="browsed">
            <dt><i>Browse_Graphic_File_Description: </i></dt>
            <dd><xsl:value-of select="."/></dd>
          </xsl:for-each>
          <xsl:for-each select="browset">
            <dt><i>Browse_Graphic_File_Type: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="datacred">
        <dt><i>Data_Set_Credit: </i></dt>
        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
      </xsl:for-each>

      <xsl:for-each select="secinfo">
        <dt><i>Security_Information: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="secsys">
            <dt><i>Security_Classification_System: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="secclass">
            <dt><i>Security_Classification: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="sechandl">
            <dt><i>Security_Handling_Description: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="native">
        <dt><i>Native_Data_Set_Environment: </i></dt>
        <dd><xsl:value-of select="."/></dd>
      </xsl:for-each>

      <xsl:for-each select="crossref">
        <dt><i>Cross_Reference: </i></dt>
        <dd>
        <dl>
          <xsl:apply-templates select="citeinfo"/>
        </dl>
        </dd>
      </xsl:for-each>

    </dl>
    </dd>
  </dl>
  <a href="#Top">Back to Top</a>
</xsl:template>

<!-- Data Quality -->
<xsl:template match="dataqual">
  <a name="Data_Quality_Information"><hr/></a>
  <dl>
    <dt><i>Data_Quality_Information: </i></dt>
    <dd>
    <dl>
      <xsl:for-each select="attracc">
        <dt><i>Attribute_Accuracy: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="attraccr">
            <dt><i>Attribute_Accuracy_Report: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
          </xsl:for-each>
          <xsl:for-each select="qattracc">
            <dt><i>Quantitative_Attribute_Accuracy_Assessment: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="attraccv">
                <dt><i>Attribute_Accuracy_Value: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="attracce">
                <dt><i>Attribute_Accuracy_Explanation: </i></dt>
                <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>      
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="logic">
        <dt><i>Logical_Consistency_Report: </i></dt>
        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
      </xsl:for-each>
      <xsl:for-each select="complete">
        <dt><i>Completeness_Report: </i></dt>
        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
      </xsl:for-each>

      <xsl:for-each select="posacc">
        <dt><i>Positional_Accuracy: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="horizpa">
            <dt><i>Horizontal_Positional_Accuracy: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="horizpar">
                <dt><i>Horizontal_Positional_Accuracy_Report: </i></dt>
                <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
              </xsl:for-each>
              <xsl:for-each select="qhorizpa">
                <dt><i>Quantitative_Horizontal_Positional_Accuracy_Assessment: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="horizpav">
                    <dt><i>Horizontal_Positional_Accuracy_Value: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="horizpae">
                    <dt><i>Horizontal_Positional_Accuracy_Explanation: </i></dt>
                    <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
          <xsl:for-each select="vertacc">
            <dt><i>Vertical_Positional_Accuracy: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="vertaccr">
                <dt><i>Vertical_Positional_Accuracy_Report: </i></dt>
                <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
              </xsl:for-each>
              <xsl:for-each select="qvertpa">
                <dt><i>Quantitative_Vertical_Positional_Accuracy_Assessment: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="vertaccv">
                    <dt><i>Vertical_Positional_Accuracy_Value: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="vertacce">
                    <dt><i>Vertical_Positional_Accuracy_Explanation: </i></dt>
                    <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="lineage">
        <dt><i>Lineage: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="srcinfo">
            <dt><i>Source_Information: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="srccite">
                <dt><i>Source_Citation: </i></dt>
                <dd>
                <dl>
                  <xsl:apply-templates select="citeinfo"/>
                </dl>
                </dd>
              </xsl:for-each>
              <xsl:for-each select="srcscale">
                <dt><i>Source_Scale_Denominator: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="typesrc">
                <dt><i>Type_of_Source_Media: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>

              <xsl:for-each select="srctime">
                <dt><i>Source_Time_Period_of_Content: </i></dt>
                <dd>
                <dl>
                  <xsl:apply-templates select="timeinfo"/>
                  <xsl:for-each select="srccurr">
                    <dt><i>Source_Currentness_Reference: </i></dt>
                    <dd><xsl:value-of select="."/></dd>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>

              <xsl:for-each select="srccitea">
                <dt><i>Source_Citation_Abbreviation: </i></dt>
                <dd><xsl:value-of select="."/></dd>
              </xsl:for-each>
              <xsl:for-each select="srccontr">
                <dt><i>Source_Contribution: </i></dt>
                <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="procstep">
            <dt><i>Process_Step: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="procdesc">
                <dt><i>Process_Description: </i></dt>
                <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>   
              </xsl:for-each>
              <xsl:for-each select="srcused">
                <dt><i>Source_Used_Citation_Abbreviation: </i></dt>
                <dd><xsl:value-of select="."/></dd>
              </xsl:for-each>
              <xsl:for-each select="procdate">
                <dt><i>Process_Date: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="proctime">
                <dt><i>Process_Time: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="srcprod">
                <dt><i>Source_Produced_Citation_Abbreviation: </i></dt>
                <dd><xsl:value-of select="."/></dd>
              </xsl:for-each>
              <xsl:for-each select="proccont">
                <dt><i>Process_Contact: </i></dt>
                <dd>
                <dl>
                  <xsl:apply-templates select="cntinfo"/>
                </dl>
                </dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>
      <xsl:for-each select="cloud">
        <dt><i>Cloud_Cover: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
    </dl>
    </dd>
  </dl>
  <a href="#Top">Back to Top</a>
</xsl:template>

<!-- Spatial Data Organization -->
<xsl:template match="spdoinfo">
  <a name="Spatial_Data_Organization_Information"><hr/></a>
  <dl>
    <dt><i>Spatial_Data_Organization_Information: </i></dt>
    <dd>
    <dl>
      <xsl:for-each select="indspref">
        <dt><i>Indirect_Spatial_Reference_Method: </i></dt>
        <dd><xsl:value-of select="."/></dd>
      </xsl:for-each>

      <xsl:for-each select="direct">
        <dt><i>Direct_Spatial_Reference_Method: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>

      <xsl:for-each select="ptvctinf">
        <dt><i>Point_and_Vector_Object_Information: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="sdtsterm">
            <dt><i>SDTS_Terms_Description: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="sdtstype">
                <dt><i>SDTS_Point_and_Vector_Object_Type: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="ptvctcnt">
                <dt><i>Point_and_Vector_Object_Count: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="vpfterm">
            <dt><i>VPF_Terms_Description: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="vpflevel">
                <dt><i>VPF_Topology_Level: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="vpfinfo">
                <dt><i>VPF_Point_and_Vector_Object_Information: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="vpftype">
                    <dt><i>VPF_Point_and_Vector_Object_Type: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="ptvctcnt">
                    <dt><i>Point_and_Vector_Object_Count: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="rastinfo">
        <dt><i>Raster_Object_Information: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="rasttype">
            <dt><i>Raster_Object_Type: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="rowcount">
            <dt><i>Row_Count: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="colcount">
            <dt><i>Column_Count: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="vrtcount">
            <dt><i>Vertical_Count: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>
    </dl>
    </dd>
  </dl>
  <a href="#Top">Back to Top</a>
</xsl:template>

<!-- Spatial Reference -->
<xsl:template match="spref">
  <a name="Spatial_Reference_Information"><hr/></a>
  <dl>
    <dt><i>Spatial_Reference_Information: </i></dt>
    <dd>
    <dl>
      <xsl:for-each select="horizsys">
        <dt><i>Horizontal_Coordinate_System_Definition: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="geograph">
            <dt><i>Geographic: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="latres">
                <dt><i>Latitude_Resolution: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="longres">
                <dt><i>Longitude_Resolution: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="geogunit">
                <dt><i>Geographic_Coordinate_Units: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="planar">
            <dt><i>Planar: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="mapproj">
                <dt><i>Map_Projection: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="mapprojn">
                    <dt><i>Map_Projection_Name: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>

                  <xsl:for-each select="albers">
                    <dt><i>Albers_Conical_Equal_Area: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="azimequi">
                    <dt><i>Azimuthal_Equidistant: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="equicon">
                    <dt><i>Equidistant_Conic: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="equirect">
                    <dt><i>Equirectangular: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="gvnsp">
                    <dt><i>General_Vertical_Near-sided_Perspective: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="gnomonic">
                    <dt><i>Gnomonic: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="lamberta">
                    <dt><i>Lambert_Azimuthal_Equal_Area: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="lambertc">
                    <dt><i>Lambert_Conformal_Conic: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="mercator">
                    <dt><i>Mercator: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="modsak">
                    <dt><i>Modified_Stereographic_for_Alaska: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="miller">
                    <dt><i>Miller_Cylindrical: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="obqmerc">
                    <dt><i>Oblique_Mercator: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="orthogr">
                    <dt><i>Orthographic: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="polarst">
                    <dt><i>Polar_Stereographic: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="polycon">
                    <dt><i>Polyconic: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="robinson">
                    <dt><i>Robinson: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="sinusoid">
                    <dt><i>Sinusoidal: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="spaceobq">
                    <dt><i>Space_Oblique_Mercator_(Landsat): </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="stereo">
                    <dt><i>Stereographic: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="transmer">
                    <dt><i>Transverse_Mercator: </i></dt>
                  </xsl:for-each>
                  <xsl:for-each select="vdgrin">
                    <dt><i>van_der_Grinten: </i></dt>
                  </xsl:for-each>

                  <xsl:apply-templates select="*"/>
                </dl>
                </dd>
              </xsl:for-each>

              <xsl:for-each select="gridsys">
                <dt><i>Grid_Coordinate_System: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="gridsysn">
                    <dt><i>Grid_Coordinate_System_Name: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>

                  <xsl:for-each select="utm">
                    <dt><i>Universal_Transverse_Mercator: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="utmzone">
                        <dt><i>UTM_Zone_Number: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="transmer">
                        <dt><i>Transverse_Mercator: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="transmer"/>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="ups">
                    <dt><i>Universal_Polar_Stereographic: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="upszone">
                        <dt><i>UPS_Zone_Identifier: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="polarst">
                        <dt><i>Polar_Stereographic: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="polarst"/>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="spcs">
                    <dt><i>State_Plane_Coordinate_System: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="spcszone">
                        <dt><i>SPCS_Zone_Identifier: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="lambertc">
                        <dt><i>Lambert_Conformal_Conic: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="lambertc"/>
                      <xsl:for-each select="transmer">
                        <dt><i>Transverse_Mercator: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="transmer"/>
                      <xsl:for-each select="obqmerc">
                        <dt><i>Oblique_Mercator: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="obqmerc"/>
                      <xsl:for-each select="polycon">
                        <dt><i>Polyconic: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="polycon"/>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="arcsys">
                    <dt><i>ARC_Coordinate_System: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="arczone">
                        <dt><i>ARC_System_Zone_Identifier: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="equirect">
                        <dt><i>Equirectangular: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="equirect"/>
                      <xsl:for-each select="azimequi">
                        <dt><i>Azimuthal_Equidistant: </i></dt>
                      </xsl:for-each>
                      <xsl:apply-templates select="azimequi"/>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="othergrd">
                    <dt><i>Other_Grid_System's_Definition: </i></dt>
                    <dd><xsl:value-of select="."/></dd>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>

              <xsl:for-each select="localp">
                <dt><i>Local_Planar: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="localpd">
                    <dt><i>Local_Planar_Description: </i></dt>
                    <dd><xsl:value-of select="."/></dd>
                  </xsl:for-each>
                  <xsl:for-each select="localpgi">
                    <dt><i>Local_Planar_Georeference_Information: </i></dt>
                    <dd><xsl:value-of select="."/></dd>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>

              <xsl:for-each select="planci">
                <dt><i>Planar_Coordinate_Information: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="plance">
                    <dt><i>Planar_Coordinate_Encoding_Method: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="coordrep">
                    <dt><i>Coordinate_Representation: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="absres">
                        <dt><i>Abscissa_Resolution: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="ordres">
                        <dt><i>Ordinate_Resolution: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                    </dl>
                    </dd>
                  </xsl:for-each>
                  <xsl:for-each select="distbrep">
                    <dt><i>Distance_and_Bearing_Representation: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="distres">
                        <dt><i>Distance_Resolution: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="bearres">
                        <dt><i>Bearing_Resolution: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="bearunit">
                        <dt><i>Bearing_Units: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="bearrefd">
                        <dt><i>Bearing_Reference_Direction: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="bearrefm">
                        <dt><i>Bearing_Reference_Meridian: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                    </dl>
                    </dd>
                  </xsl:for-each>
                  <xsl:for-each select="plandu">
                    <dt><i>Planar_Distance_Units: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="local">
            <dt><i>Local: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="localdes">
                <dt><i>Local_Description: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="localgeo">
                <dt><i>Local_Georeference_Information: </i></dt>
                <dd><xsl:value-of select="."/></dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="geodetic">
            <dt><i>Geodetic_Model: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="horizdn">
                <dt><i>Horizontal_Datum_Name: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="ellips">
                <dt><i>Ellipsoid_Name: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="semiaxis">
                <dt><i>Semi-major_Axis: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="denflat">
                <dt><i>Denominator_of_Flattening_Ratio: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="vertdef">
        <dt><i>Vertical_Coordinate_System_Definition: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="altsys">
            <dt><i>Altitude_System_Definition: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="altdatum">
                <dt><i>Altitude_Datum_Name: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="altres">
                <dt><i>Altitude_Resolution: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="altunits">
                <dt><i>Altitude_Distance_Units: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="altenc">
                <dt><i>Altitude_Encoding_Method: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="depthsys">
            <dt><i>Depth_System_Definition: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="depthdn">
                <dt><i>Depth_Datum_Name: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="depthres">
                <dt><i>Depth_Resolution: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="depthdu">
                <dt><i>Depth_Distance_Units: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="depthem">
                <dt><i>Depth_Encoding_Method: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>
    </dl>
    </dd>
  </dl>
  <a href="#Top">Back to Top</a>
</xsl:template>

<!-- Entity and Attribute -->
<xsl:template match="eainfo">
  <a name="Entity_and_Attribute_Information"><hr/></a>
  <dl>
    <dt><i>Entity_and_Attribute_Information: </i></dt>
    <dd>
    <dl>
      <xsl:for-each select="detailed">
        <dt><i>Detailed_Description: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="enttyp">
            <dt><i>Entity_Type: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="enttypl">
                <dt><i>Entity_Type_Label: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="enttypd">
                <dt><i>Entity_Type_Definition: </i></dt>
                <dd><xsl:value-of select="."/></dd>
              </xsl:for-each>
              <xsl:for-each select="enttypds">
                <dt><i>Entity_Type_Definition_Source: </i></dt>
                <dd><xsl:value-of select="."/></dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="attr">
            <dt><i>Attribute: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="attrlabl">
                <dt><i>Attribute_Label: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="attrdef">
                <dt><i>Attribute_Definition: </i></dt>
                <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>   
              </xsl:for-each>
              <xsl:for-each select="attrdefs">
                <dt><i>Attribute_Definition_Source: </i></dt>
                <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>   
              </xsl:for-each>

              <xsl:for-each select="attrdomv">
                <dt><i>Attribute_Domain_Values: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="edom">
                    <dt><i>Enumerated_Domain: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="edomv">
                        <dt><i>Enumerated_Domain_Value: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="edomvd">
                        <dt><i>Enumerated_Domain_Value_Definition: </i></dt>
                        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>   
                      </xsl:for-each>
                      <xsl:for-each select="edomvds">
                        <dt><i>Enumerated_Domain_Value_Definition_Source: </i></dt>
                        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
                      </xsl:for-each>
                      <xsl:for-each select="attr">
                        <dt><i>Attribute: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="rdom">
                    <dt><i>Range_Domain: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="rdommin">
                        <dt><i>Range_Domain_Minimum: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="rdommax">
                        <dt><i>Range_Domain_Maximum: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="attrunit">
                        <dt><i>Attribute_Units_of_Measure: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="attrmres">
                        <dt><i>Attribute_Measurement_Resolution: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="attr">
                        <dt><i>Attribute: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="codesetd">
                    <dt><i>Codeset_Domain: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="codesetn">
                        <dt><i>Codeset_Name: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="codesets">
                        <dt><i>Codeset_Source: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="udom">
                    <dt><i>Unrepresentable_Domain: </i></dt>
                    <dd><xsl:value-of select="."/></dd>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>

              <xsl:for-each select="begdatea">
                <dt><i>Beginning_Date_of_Attribute_Values: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>
              <xsl:for-each select="enddatea">
                <dt><i>Ending_Date_of_Attribute_Values: </i> <xsl:value-of select="."/></dt>
              </xsl:for-each>

              <xsl:for-each select="attrvai">
                <dt><i>Attribute_Value_Accuracy_Information: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="attrva">
                    <dt><i>Attribute_Value_Accuracy: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="attrvae">
                    <dt><i>Attribute_Value_Accuracy_Explanation: </i></dt>
                    <dd><xsl:value-of select="."/></dd>
                  </xsl:for-each>
                 </dl>
                </dd>
              </xsl:for-each>
              <xsl:for-each select="attrmfrq">
                <dt><i>Attribute_Measurement_Frequency: </i></dt>
                <dd><xsl:value-of select="."/></dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="overview">
        <dt><i>Overview_Description: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="eaover">
            <dt><i>Entity_and_Attribute_Overview: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>      
          </xsl:for-each>
          <xsl:for-each select="eadetcit">
            <dt><i>Entity_and_Attribute_Detail_Citation: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>
    </dl>
    </dd>
  </dl>
  <a href="#Top">Back to Top</a>
</xsl:template>

<!-- Distribution -->
<xsl:template match="distinfo">
  <a>
    <xsl:attribute name="name"><xsl:text>Distributor</xsl:text><xsl:value-of select="position()"/></xsl:attribute>
    <hr/>
  </a>
  <dl>
    <dt><i>Distribution_Information: </i> </dt>
    <dd>
    <dl>
      <xsl:for-each select="distrib">
        <dt><i>Distributor: </i></dt>
        <dd>
        <dl>
          <xsl:apply-templates select="cntinfo"/>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="resdesc">
        <dt><i>Resource_Description: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
      <xsl:for-each select="distliab">
        <dt><i>Distribution_Liability: </i></dt>
        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>      
      </xsl:for-each>

      <xsl:for-each select="stdorder">
        <dt><i>Standard_Order_Process: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="nondig">
            <dt><i>Non-digital_Form: </i></dt>
            <dd><xsl:value-of select="."/></dd>
          </xsl:for-each>
          <xsl:for-each select="digform">
            <dt><i>Digital_Form: </i></dt>
            <dd>
            <dl>
              <xsl:for-each select="digtinfo">
                <dt><i>Digital_Transfer_Information: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="formname">
                    <dt><i>Format_Name: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="formvern">
                    <dt><i>Format_Version_Number: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="formverd">
                    <dt><i>Format_Version_Date: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="formspec">
                    <dt><i>Format_Specification: </i></dt>
                    <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
                  </xsl:for-each>
                  <xsl:for-each select="formcont">
                    <dt><i>Format_Information_Content: </i></dt>
                    <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
                  </xsl:for-each>
                  <xsl:for-each select="filedec">
                    <dt><i>File_Decompression_Technique: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                  <xsl:for-each select="transize">
                    <dt><i>Transfer_Size: </i> <xsl:value-of select="."/></dt>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>

              <xsl:for-each select="digtopt">
                <dt><i>Digital_Transfer_Option: </i></dt>
                <dd>
                <dl>
                  <xsl:for-each select="onlinopt">
                    <dt><i>Online_Option: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="computer">
                        <dt><i>Computer_Contact_Information: </i></dt>
                        <dd>
                        <dl>
                          <xsl:for-each select="networka">
                            <dt><i>Network_Address: </i></dt>
                            <dd>
                            <dl>
                              <xsl:for-each select="networkr">
                                <dt><i>Network_Resource_Name: </i> <a target="viewer">
                                  <xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
                                  <xsl:value-of select="."/></a>
                                </dt>
                              </xsl:for-each>
                            </dl>
                            </dd>
                          </xsl:for-each>

                          <xsl:for-each select="dialinst">
                            <dt><i>Dialup_Instructions: </i></dt>
                            <dd>
                            <dl>
                              <xsl:for-each select="lowbps">
                                <dt><i>Lowest_BPS: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                              <xsl:for-each select="highbps">
                                <dt><i>Highest_BPS: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                              <xsl:for-each select="numdata">
                                <dt><i>Number_DataBits: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                              <xsl:for-each select="numstop">
                                <dt><i>Number_StopBits: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                              <xsl:for-each select="parity">
                                <dt><i>Parity: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                              <xsl:for-each select="compress">
                                <dt><i>Compression_Support: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                              <xsl:for-each select="dialtel">
                                <dt><i>Dialup_Telephone: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                              <xsl:for-each select="dialfile">
                                <dt><i>Dialup_File_Name: </i> <xsl:value-of select="."/></dt>
                              </xsl:for-each>
                            </dl>
                            </dd>
                          </xsl:for-each>
                        </dl>
                        </dd>
                      </xsl:for-each>
                      <xsl:for-each select="accinstr">
                        <dt><i>Access_Instructions: </i></dt>
                        <dd><xsl:value-of select="."/></dd>
                      </xsl:for-each>
                      <xsl:for-each select="oncomp">
                        <dt><i>Online_Computer_and_Operating_System: </i></dt>
                        <dd><xsl:value-of select="."/></dd>
                      </xsl:for-each>
                    </dl>
                    </dd>
                  </xsl:for-each>

                  <xsl:for-each select="offoptn">
                    <dt><i>Offline_Option: </i></dt>
                    <dd>
                    <dl>
                      <xsl:for-each select="offmedia">
                        <dt><i>Offline_Media: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="reccap">
                        <dt><i>Recording_Capacity: </i></dt>
                        <dd>
                        <dl>
                          <xsl:for-each select="recden">
                            <dt><i>Recording_Density: </i> <xsl:value-of select="."/></dt>
                          </xsl:for-each>
                          <xsl:for-each select="recdenu">
                            <dt><i>Recording_Density_Units: </i> <xsl:value-of select="."/></dt>
                          </xsl:for-each>
                        </dl>
                        </dd>
                      </xsl:for-each>
                      <xsl:for-each select="recfmt">
                        <dt><i>Recording_Format: </i> <xsl:value-of select="."/></dt>
                      </xsl:for-each>
                      <xsl:for-each select="compat">
                        <dt><i>Compatibility_Information: </i></dt>
                        <dd><xsl:value-of select="."/></dd>
                      </xsl:for-each>
                    </dl>
                    </dd>
                  </xsl:for-each>
                </dl>
                </dd>
              </xsl:for-each>
            </dl>
            </dd>
          </xsl:for-each>

          <xsl:for-each select="fees">
            <dt><i>Fees: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="ordering">
            <dt><i>Ordering_Instructions: </i></dt>
            <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>     
          </xsl:for-each>
          <xsl:for-each select="turnarnd">
            <dt><i>Turnaround: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="custom">
        <dt><i>Custom_Order_Process: </i></dt>
        <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
      </xsl:for-each>
      <xsl:for-each select="techpreq">
        <dt><i>Technical_Prerequisites: </i></dt>
        <dd><xsl:value-of select="."/></dd>
      </xsl:for-each>
      <xsl:for-each select="availabl">
        <dt><i>Available_Time_Period: </i></dt>
        <dd>
        <dl>
          <xsl:apply-templates select="timeinfo"/>
        </dl>
        </dd>
      </xsl:for-each>
    </dl>
    </dd>
  </dl>
  <a href="#Top">Back to Top</a>
</xsl:template>

<!-- Metadata -->
<xsl:template match="metainfo">
  <a name="Metadata_Reference_Information"><hr/></a>
  <dl>
    <dt><i>Metadata_Reference_Information: </i></dt>
    <dd>
    <dl>
      <xsl:for-each select="metd">
        <dt><i>Metadata_Date: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
      <xsl:for-each select="metrd">
        <dt><i>Metadata_Review_Date: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
      <xsl:for-each select="metfrd">
        <dt><i>Metadata_Future_Review_Date: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>

      <xsl:for-each select="metc">
        <dt><i>Metadata_Contact: </i></dt>
        <dd>
        <dl>
          <xsl:apply-templates select="cntinfo"/>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="metstdn">
        <dt><i>Metadata_Standard_Name: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
      <xsl:for-each select="metstdv">
        <dt><i>Metadata_Standard_Version: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
      <xsl:for-each select="mettc">
        <dt><i>Metadata_Time_Convention: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>

      <xsl:for-each select="metac">
        <dt><i>Metadata_Access_Constraints: </i> <xsl:value-of select="."/></dt>
      </xsl:for-each>
      <xsl:for-each select="metuc">
        <dt><i>Metadata_Use_Constraints: </i></dt>
        <dd><xsl:value-of select="."/></dd>
      </xsl:for-each>

      <xsl:for-each select="metsi">
        <dt><i>Metadata_Security_Information: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="metscs">
            <dt><i>Metadata_Security_Classification_System: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="metsc">
            <dt><i>Metadata_Security_Classification: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
          <xsl:for-each select="metshd">
            <dt><i>Metadata_Security_Handling_Description: </i></dt>
            <dd><xsl:value-of select="."/></dd>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>

      <xsl:for-each select="metextns">
        <dt><i>Metadata_Extensions: </i></dt>
        <dd>
        <dl>
          <xsl:for-each select="onlink">
            <dt><i>Online_Linkage: </i> <a target="viewer">
              <xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
              <xsl:value-of select="."/></a>
            </dt>
          </xsl:for-each>
          <xsl:for-each select="metprof">
            <dt><i>Profile_Name: </i> <xsl:value-of select="."/></dt>
          </xsl:for-each>
        </dl>
        </dd>
      </xsl:for-each>
    </dl>
    </dd>
  </dl>
  <a href="#Top">Back to Top</a>
</xsl:template>

<!-- Citation -->
<xsl:template match="citeinfo">
  <dt><i>Citation_Information: </i></dt>
  <dd>
  <dl>
    <xsl:for-each select="origin">
      <dt><i>Originator: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>

    <xsl:for-each select="pubdate">
      <dt><i>Publication_Date: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="pubtime">
      <dt><i>Publication_Time: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>

    <xsl:for-each select="title">
      <dt><i>Title: </i></dt>
      <dd><xsl:value-of select="."/></dd>
    </xsl:for-each>
    <xsl:for-each select="edition">
      <dt><i>Edition: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>

    <xsl:for-each select="geoform">
      <dt><i>Geospatial_Data_Presentation_Form: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>

    <xsl:for-each select="serinfo">
      <dt><i>Series_Information: </i></dt>
      <dd>
      <dl>
        <xsl:for-each select="sername">
          <dt><i>Series_Name: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="issue">
          <dt><i>Issue_Identification: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
      </dl>
      </dd>
    </xsl:for-each>

    <xsl:for-each select="pubinfo">
      <dt><i>Publication_Information: </i></dt>
      <dd>
      <dl>
        <xsl:for-each select="pubplace">
          <dt><i>Publication_Place: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="publish">
          <dt><i>Publisher: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
      </dl>
      </dd>
    </xsl:for-each>

    <xsl:for-each select="othercit">
      <dt><i>Other_Citation_Details: </i></dt>
      <dd><xsl:value-of select="."/></dd>
    </xsl:for-each>

    <xsl:for-each select="onlink">
      <dt><i>Online_Linkage: </i> <a target="viewer">
        <xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
        <xsl:value-of select="."/></a>
      </dt>
    </xsl:for-each>

    <xsl:for-each select="lworkcit">
      <dt><i>Larger_Work_Citation: </i></dt>
      <dd>
      <dl>
        <xsl:apply-templates select="citeinfo"/>
      </dl>
      </dd>
    </xsl:for-each>
  </dl>
  </dd>
</xsl:template>

<!-- Contact -->
<xsl:template match="cntinfo">
  <dt><i>Contact_Information: </i></dt>
  <dd>
  <dl>
    <xsl:for-each select="cntperp">
      <dt><i>Contact_Person_Primary: </i></dt>
      <dd>
      <dl>
        <xsl:for-each select="cntper">
          <dt><i>Contact_Person: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="cntorg">
          <dt><i>Contact_Organization: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
      </dl>
      </dd>
    </xsl:for-each>
    <xsl:for-each select="cntorgp">
      <dt><i>Contact_Organization_Primary: </i></dt>
      <dd>
      <dl>
        <xsl:for-each select="cntorg">
          <dt><i>Contact_Organization: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="cntper">
          <dt><i>Contact_Person: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
      </dl>
      </dd>
    </xsl:for-each>
    <xsl:for-each select="cntpos">
      <dt><i>Contact_Position: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>

    <xsl:for-each select="cntaddr">
      <dt><i>Contact_Address: </i></dt>
      <dd>
      <dl>
        <xsl:for-each select="addrtype">
          <dt><i>Address_Type: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="address">
          <dt><i>Address: </i></dt>
          <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>    
        </xsl:for-each>
        <xsl:for-each select="city">
          <dt><i>City: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="state">
          <dt><i>State_or_Province: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="postal">
          <dt><i>Postal_Code: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="country">
          <dt><i>Country: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
      </dl>
      </dd>
    </xsl:for-each>

    <xsl:for-each select="cntvoice">
      <dt><i>Contact_Voice_Telephone: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="cnttdd">
      <dt><i>Contact_TDD/TTY_Telephone: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="cntfax">
      <dt><i>Contact_Facsimile_Telephone: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="cntemail">
      <dt><i>Contact_Electronic_Mail_Address: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>

    <xsl:for-each select="hours">
      <dt><i>Hours_of_Service: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="cntinst">
      <dt><i>Contact Instructions: </i></dt>
      <dd><pre id="fixvalue"><xsl:value-of select="."/></pre></dd>
    </xsl:for-each>
  </dl>
  </dd>
</xsl:template>

<!-- Time Period Info -->
<xsl:template match="timeinfo">
  <dt><i>Time_Period_Information: </i></dt>
  <dd>
  <dl>
    <xsl:apply-templates select="sngdate"/>
    <xsl:apply-templates select="mdattim"/>
    <xsl:apply-templates select="rngdates"/>
  </dl>
  </dd>
</xsl:template>

<!-- Single Date/Time -->
<xsl:template match="sngdate">
  <dt><i>Single_Date/Time: </i></dt>
  <dd>
  <dl>
    <xsl:for-each select="caldate">
      <dt><i>Calendar_Date: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="time">
      <dt><i>Time of Day: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
  </dl>
  </dd>
</xsl:template>

<!-- Multiple Date/Time -->
<xsl:template match="mdattim">
  <dt><i>Multiple_Dates/Times: </i></dt>
  <dd>
  <dl>
    <xsl:apply-templates select="sngdate"/>
  </dl>
  </dd>
</xsl:template>

<!-- Range of Dates/Times -->
<xsl:template match="rngdates">
  <dt><i>Range_of_Dates/Times: </i></dt>
  <dd>
  <dl>
    <xsl:for-each select="begdate">
      <dt><i>Beginning_Date: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="begtime">
      <dt><i>Beginning_Time: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="enddate">
      <dt><i>Ending_Date: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="endtime">
      <dt><i>Ending_Time: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
  </dl>
  </dd>
</xsl:template>

<!-- G-Ring -->
<xsl:template match="grngpoin">
  <dt><i>G-Ring_Point: </i></dt>
  <dd>
  <dl>
    <xsl:for-each select="gringlat">
      <dt><i>G-Ring_Latitude: </i> <xsl:value-of select="."/></dt>
        </xsl:for-each>
        <xsl:for-each select="gringlon">
      <dt><i>G-Ring_Longitude: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
  </dl>
  </dd>
</xsl:template>
<xsl:template match="gring">
  <dt><i>G-Ring: </i></dt>
  <dd><xsl:value-of select="."/></dd>
</xsl:template>


<!-- Map Projections -->
<xsl:template match="albers | equicon | lambertc">
  <dd>
  <dl>
    <xsl:apply-templates select="stdparll"/>
    <xsl:apply-templates select="longcm"/>
    <xsl:apply-templates select="latprjo"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="gnomonic | lamberta | orthogr | stereo | gvnsp">
  <dd>
  <dl>
    <xsl:for-each select="../gvnsp">
      <xsl:apply-templates select="heightpt"/>
    </xsl:for-each>
    <xsl:apply-templates select="longpc"/>
    <xsl:apply-templates select="latprjc"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="azimequi | polycon | transmer">
  <dd>
  <dl>
    <xsl:for-each select="../transmer">
      <xsl:apply-templates select="sfctrmer"/>
    </xsl:for-each>
    <xsl:apply-templates select="longcm"/>
    <xsl:apply-templates select="latprjo"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="miller | sinusoid | vdgrin">
  <dd>
  <dl>
    <xsl:for-each select="../equirect">
      <xsl:apply-templates select="stdparll"/>
    </xsl:for-each>
    <xsl:for-each select="../mercator">
      <xsl:apply-templates select="stdparll"/>
      <xsl:apply-templates select="sfequat"/>
    </xsl:for-each>
    <xsl:apply-templates select="longcm"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="equirect">
  <dd>
  <dl>
    <xsl:apply-templates select="stdparll"/>
    <xsl:apply-templates select="longcm"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="mercator">
  <dd>
  <dl>
    <xsl:apply-templates select="stdparll"/>
    <xsl:apply-templates select="sfequat"/>
    <xsl:apply-templates select="longcm"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="polarst">
  <dd>
  <dl>
    <xsl:apply-templates select="svlong"/>
    <xsl:apply-templates select="stdparll"/>
    <xsl:apply-templates select="sfprjorg"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="obqmerc">
  <dd>
  <dl>
    <xsl:apply-templates select="sfctrlin"/>
    <xsl:apply-templates select="obqlazim"/>
    <xsl:apply-templates select="obqlpt"/>
    <xsl:apply-templates select="latprjo"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="spaceobq">
  <dd>
  <dl>
    <xsl:apply-templates select="landsat"/>
    <xsl:apply-templates select="pathnum"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="robinson">
  <dd>
  <dl>
    <xsl:apply-templates select="longpc"/>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="modsak">
  <dd>
  <dl>
    <xsl:apply-templates select="feast"/>
    <xsl:apply-templates select="fnorth"/>
  </dl>
  </dd>
</xsl:template>


<!-- Map Projection Parameters -->
<xsl:template match="stdparll">
  <dt><i>Standard_Parallel: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="longcm">
  <dt><i>Longitude_of_Central_Meridian: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="latprjo">
  <dt><i>Latitude_of_Projection_Origin: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="feast">
  <dt><i>False_Easting: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="fnorth">
  <dt><i>False_Northing: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="sfequat">
  <dt><i>Scale_Factor_at_Equator: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="heightpt">
  <dt><i>Height_of_Perspective_Point_Above_Surface: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="longpc">
  <dt><i>Longitude_of_Projection_Center: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="latprjc">
  <dt><i>Latitude_of_Projection_Center: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="sfctrlin">
  <dt><i>Scale_Factor_at_Center_Line: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="obqlazim">
  <dt><i>Oblique_Line_Azimuth: </i> <xsl:value-of select="."/></dt>
  <dd>
  <dl>
    <xsl:for-each select="azimangl">
      <dt><i>Azimuthal_Angle: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="azimptl">
      <dt><i>Azimuthal_Measure_Point_Longitude: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="obqlpt">
  <dt><i>Oblique_Line_Point: </i> <xsl:value-of select="."/></dt>
  <dd>
  <dl>
    <xsl:for-each select="obqllat">
      <dt><i>Oblique_Line_Latitude: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
    <xsl:for-each select="obqllong">
       <dt><i>Oblique_Line_Longitude: </i> <xsl:value-of select="."/></dt>
    </xsl:for-each>
  </dl>
  </dd>
</xsl:template>

<xsl:template match="svlong">
  <dt><i>Straight_Vertical_Longitude_from_Pole: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="sfprjorg">
  <dt><i>Scale_Factor_at_Projection_Origin: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="landsat">
  <dt><i>Landsat_Number: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="pathnum">
  <dt><i>Path_Number: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="sfctrmer">
  <dt><i>Scale_Factor_at_Central_Meridian: </i> <xsl:value-of select="."/></dt>
</xsl:template>

<xsl:template match="otherprj">
  <dt><i>Other_Projection's_Definition: </i></dt>
  <dd><xsl:value-of select="."/></dd>
</xsl:template>

</xsl:stylesheet>