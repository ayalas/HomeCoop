<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" 
xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" 
xmlns:xlink="http://www.w3.org/1999/xlink" 
xmlns:dc="http://purl.org/dc/elements/1.1/" 
xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" 
xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" 
xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" 
xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" 
xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" 
xmlns:math="http://www.w3.org/1998/Math/MathML" 
xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" 
xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" 
xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" 
xmlns:ooo="http://openoffice.org/2004/office" 
xmlns:ooow="http://openoffice.org/2004/writer" 
xmlns:oooc="http://openoffice.org/2004/calc" 
xmlns:dom="http://www.w3.org/2001/xml-events" 
xmlns:xforms="http://www.w3.org/2002/xforms" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xmlns:rpt="http://openoffice.org/2005/report" 
xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" 
xmlns:xhtml="http://www.w3.org/1999/xhtml" 
xmlns:grddl="http://www.w3.org/2003/g/data-view#" 
xmlns:tableooo="http://openoffice.org/2009/table" 
xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" 
xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" 
xmlns:css3t="http://www.w3.org/TR/css3-text/" 
office:version="1.2" 
office:mimetype="application/vnd.oasis.opendocument.spreadsheet"
version='1.0'>
<xsl:output method="xml"/>

<xsl:template match="/">
<xsl:apply-templates select="document" />
</xsl:template>

<xsl:template match="document">
<office:document 
office:version="1.2" 
office:mimetype="application/vnd.oasis.opendocument.spreadsheet">
 <office:meta>
 <meta:initial-creator>VeganCoop</meta:initial-creator>
 <dc:creator>VeganCoop</dc:creator>
 <meta:generator>LibreOffice/3.5$Linux_x86 LibreOffice_project/350m1$Build-2</meta:generator>
 </office:meta>
<office:settings>
  <config:config-item-set config:name="ooo:view-settings">
   <config:config-item config:name="VisibleAreaTop" config:type="int">0</config:config-item>
   <config:config-item config:name="VisibleAreaLeft" config:type="int">-36718</config:config-item>
   <config:config-item config:name="VisibleAreaWidth" config:type="int">36718</config:config-item>
   <config:config-item config:name="VisibleAreaHeight" config:type="int">13333</config:config-item>
   <config:config-item-map-indexed config:name="Views">
    <config:config-item-map-entry>
     <config:config-item config:name="ViewId" config:type="string">view1</config:config-item>
     <config:config-item-map-named config:name="Tables">
      <config:config-item-map-entry config:name="Order">
       <config:config-item config:name="CursorPositionX" config:type="int">11</config:config-item>
       <config:config-item config:name="CursorPositionY" config:type="int">31</config:config-item>
       <config:config-item config:name="HorizontalSplitMode" config:type="short">0</config:config-item>
       <config:config-item config:name="VerticalSplitMode" config:type="short">0</config:config-item>
       <config:config-item config:name="HorizontalSplitPosition" config:type="int">0</config:config-item>
       <config:config-item config:name="VerticalSplitPosition" config:type="int">0</config:config-item>
       <config:config-item config:name="ActiveSplitRange" config:type="short">2</config:config-item>
       <config:config-item config:name="PositionLeft" config:type="int">0</config:config-item>
       <config:config-item config:name="PositionRight" config:type="int">0</config:config-item>
       <config:config-item config:name="PositionTop" config:type="int">0</config:config-item>
       <config:config-item config:name="PositionBottom" config:type="int">0</config:config-item>
       <config:config-item config:name="ZoomType" config:type="short">0</config:config-item>
       <config:config-item config:name="ZoomValue" config:type="int">100</config:config-item>
       <config:config-item config:name="PageViewZoomValue" config:type="int">60</config:config-item>
       <config:config-item config:name="ShowGrid" config:type="boolean">true</config:config-item>
      </config:config-item-map-entry>
     </config:config-item-map-named>
     <config:config-item config:name="ActiveTable" config:type="string">Order</config:config-item>
     <config:config-item config:name="HorizontalScrollbarWidth" config:type="int">535</config:config-item>
     <config:config-item config:name="ZoomType" config:type="short">0</config:config-item>
     <config:config-item config:name="ZoomValue" config:type="int">100</config:config-item>
     <config:config-item config:name="PageViewZoomValue" config:type="int">60</config:config-item>
     <config:config-item config:name="ShowPageBreakPreview" config:type="boolean">false</config:config-item>
     <config:config-item config:name="ShowZeroValues" config:type="boolean">true</config:config-item>
     <config:config-item config:name="ShowNotes" config:type="boolean">true</config:config-item>
     <config:config-item config:name="ShowGrid" config:type="boolean">true</config:config-item>
     <config:config-item config:name="GridColor" config:type="long">12632256</config:config-item>
     <config:config-item config:name="ShowPageBreaks" config:type="boolean">true</config:config-item>
     <config:config-item config:name="HasColumnRowHeaders" config:type="boolean">true</config:config-item>
     <config:config-item config:name="HasSheetTabs" config:type="boolean">true</config:config-item>
     <config:config-item config:name="IsOutlineSymbolsSet" config:type="boolean">true</config:config-item>
     <config:config-item config:name="IsSnapToRaster" config:type="boolean">false</config:config-item>
     <config:config-item config:name="RasterIsVisible" config:type="boolean">false</config:config-item>
     <config:config-item config:name="RasterResolutionX" config:type="int">1270</config:config-item>
     <config:config-item config:name="RasterResolutionY" config:type="int">1270</config:config-item>
     <config:config-item config:name="RasterSubdivisionX" config:type="int">1</config:config-item>
     <config:config-item config:name="RasterSubdivisionY" config:type="int">1</config:config-item>
     <config:config-item config:name="IsRasterAxisSynchronized" config:type="boolean">true</config:config-item>
    </config:config-item-map-entry>
   </config:config-item-map-indexed>
  </config:config-item-set>
  <config:config-item-set config:name="ooo:configuration-settings">
   <config:config-item config:name="LoadReadonly" config:type="boolean">false</config:config-item>
   <config:config-item config:name="UpdateFromTemplate" config:type="boolean">true</config:config-item>
   <config:config-item config:name="PrinterSetup" config:type="base64Binary">oAH+/2hwLUxhc2VySmV0LTMwMzAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQ1VQUzpocC1MYXNlckpldC0zMDMwAAAAAAAAAAAAAAAWAAMAxgAAAAAAAAAIAFZUAAAkbQAASm9iRGF0YSAxCnByaW50ZXI9aHAtTGFzZXJKZXQtMzAzMApvcmllbnRhdGlvbj1Qb3J0cmFpdApjb3BpZXM9MQptYXJnaW5kYWp1c3RtZW50PTAsMCwwLDAKY29sb3JkZXB0aD0yNApwc2xldmVsPTAKcGRmZGV2aWNlPTEKY29sb3JkZXZpY2U9MApQUERDb250ZXhEYXRhClBhZ2VTaXplOkxldHRlcgBEdXBsZXg6Tm9uZQBJbnB1dFNsb3Q6QXV0bwAAEgBDT01QQVRfRFVQTEVYX01PREUKAERVUExFWF9PRkY=</config:config-item>
   <config:config-item-map-indexed config:name="ForbiddenCharacters">
    <config:config-item-map-entry>
     <config:config-item config:name="Language" config:type="string">en</config:config-item>
     <config:config-item config:name="Country" config:type="string">US</config:config-item>
     <config:config-item config:name="Variant" config:type="string"/>
     <config:config-item config:name="BeginLine" config:type="string"/>
     <config:config-item config:name="EndLine" config:type="string"/>
    </config:config-item-map-entry>
   </config:config-item-map-indexed>
   <config:config-item config:name="AutoCalculate" config:type="boolean">true</config:config-item>
   <config:config-item config:name="IsDocumentShared" config:type="boolean">false</config:config-item>
   <config:config-item config:name="ShowNotes" config:type="boolean">true</config:config-item>
   <config:config-item config:name="HasSheetTabs" config:type="boolean">true</config:config-item>
   <config:config-item config:name="SaveVersionOnClose" config:type="boolean">false</config:config-item>
   <config:config-item config:name="RasterIsVisible" config:type="boolean">false</config:config-item>
   <config:config-item config:name="PrinterName" config:type="string">hp-LaserJet-3030</config:config-item>
   <config:config-item config:name="LinkUpdateMode" config:type="short">3</config:config-item>
   <config:config-item config:name="ApplyUserData" config:type="boolean">true</config:config-item>
   <config:config-item config:name="IsKernAsianPunctuation" config:type="boolean">false</config:config-item>
   <config:config-item config:name="RasterResolutionX" config:type="int">1270</config:config-item>
   <config:config-item config:name="IsRasterAxisSynchronized" config:type="boolean">true</config:config-item>
   <config:config-item config:name="RasterResolutionY" config:type="int">1270</config:config-item>
   <config:config-item config:name="IsOutlineSymbolsSet" config:type="boolean">true</config:config-item>
   <config:config-item config:name="ShowPageBreaks" config:type="boolean">true</config:config-item>
   <config:config-item config:name="ShowGrid" config:type="boolean">true</config:config-item>
   <config:config-item config:name="CharacterCompressionType" config:type="short">0</config:config-item>
   <config:config-item config:name="GridColor" config:type="long">12632256</config:config-item>
   <config:config-item config:name="ShowZeroValues" config:type="boolean">true</config:config-item>
   <config:config-item config:name="AllowPrintJobCancel" config:type="boolean">true</config:config-item>
   <config:config-item config:name="HasColumnRowHeaders" config:type="boolean">true</config:config-item>
   <config:config-item config:name="IsSnapToRaster" config:type="boolean">false</config:config-item>
   <config:config-item config:name="RasterSubdivisionX" config:type="int">1</config:config-item>
   <config:config-item config:name="RasterSubdivisionY" config:type="int">1</config:config-item>
  </config:config-item-set>
 </office:settings>
 <office:scripts>
  <office:script script:language="ooo:Basic">
   <ooo:libraries xmlns:ooo="http://openoffice.org/2004/office" xmlns:xlink="http://www.w3.org/1999/xlink"/>
  </office:script>
 </office:scripts>
 <office:font-face-decls>
  <style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
  <style:font-face style:name="DejaVu Sans" svg:font-family="&apos;DejaVu Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
  <style:font-face style:name="Lohit Hindi" svg:font-family="&apos;Lohit Hindi&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
  <style:font-face style:name="WenQuanYi Micro Hei" svg:font-family="&apos;WenQuanYi Micro Hei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
 </office:font-face-decls>
 <office:styles>
  <style:default-style style:family="table-cell">
   <style:paragraph-properties style:tab-stop-distance="0.5in"/>
   <style:text-properties style:font-name="Liberation Sans" fo:language="en" fo:country="US" style:font-name-asian="DejaVu Sans" style:language-asian="zh" style:country-asian="CN" style:font-name-complex="DejaVu Sans" style:language-complex="hi" style:country-complex="IN"/>
  </style:default-style>
  <number:number-style style:name="N0">
   <number:number number:min-integer-digits="1"/>
  </number:number-style>
  <number:currency-style style:name="N104P0" style:volatile="true">
   <number:currency-symbol number:language="en" number:country="US">$</number:currency-symbol>
   <number:number number:decimal-places="2" number:min-integer-digits="1" number:grouping="true"/>
  </number:currency-style>
  <number:currency-style style:name="N104">
   <style:text-properties fo:color="#ff0000"/>
   <number:text>-</number:text>
   <number:currency-symbol number:language="en" number:country="US">$</number:currency-symbol>
   <number:number number:decimal-places="2" number:min-integer-digits="1" number:grouping="true"/>
   <style:map style:condition="value()&gt;=0" style:apply-style-name="N104P0"/>
  </number:currency-style>
  <style:style style:name="Default" style:family="table-cell">
   <style:text-properties style:font-name-asian="WenQuanYi Micro Hei" style:font-name-complex="Lohit Hindi"/>
  </style:style>
  <style:style style:name="Result" style:family="table-cell" style:parent-style-name="Default">
   <style:text-properties fo:font-style="italic" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" fo:font-weight="bold"/>
  </style:style>
  <style:style style:name="Result2" style:family="table-cell" style:parent-style-name="Result" style:data-style-name="N104"/>
  <style:style style:name="Heading" style:family="table-cell" style:parent-style-name="Default">
   <style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" />
   <style:paragraph-properties fo:text-align="center"/>
   <style:text-properties fo:font-size="16pt" fo:font-style="italic" fo:font-weight="bold"/>
  </style:style>
  <style:style style:name="Heading1" style:family="table-cell" style:parent-style-name="Heading">
   <style:table-cell-properties style:rotation-angle="90"/>
  </style:style>
 </office:styles>
 <office:automatic-styles>
 <style:style style:name="regcol" style:family="table-column">
   <style:table-column-properties fo:break-before="auto" style:column-width="1in"/>
  </style:style>
 <style:style style:name="pobcol" style:family="table-column">
   <style:table-column-properties fo:break-before="auto" style:column-width="1.2in"/>
  </style:style>
  <style:style style:name="namecol" style:family="table-column">
   <style:table-column-properties fo:break-before="auto" style:column-width="1.6in"/>
  </style:style>
 <style:style style:name="paymcol" style:family="table-column">
   <style:table-column-properties fo:break-before="auto" style:column-width="1.9in"/>
  </style:style>
 <style:style style:name="emailcol" style:family="table-column">
   <style:table-column-properties fo:break-before="auto" style:column-width="2.4in"/>
  </style:style>
  <style:style style:name="ro1" style:family="table-row">
   <style:table-row-properties style:row-height="0.22in" fo:break-before="auto" style:use-optimal-row-height="false"/>
  </style:style>
<number:date-style style:name="dsnMDY" number:automatic-order="true">
   <number:month number:style="long"/>
   <number:text>/</number:text>
   <number:day number:style="long"/>
   <number:text>/</number:text>
   <number:year number:style="long"/>
  </number:date-style>
<number:date-style style:name="dsnDMY">
   <number:day number:style="long"/>
   <number:text>/</number:text>
   <number:month number:style="long"/>
   <number:text>/</number:text>
   <number:year number:style="long"/>
  </number:date-style>
  <style:style style:name="ta1" style:family="table" style:master-page-name="Default">
   <style:table-properties table:display="true" >
   <xsl:if test="orientation = 'rtl' ">
    <xsl:attribute name="style:writing-mode">rl-tb</xsl:attribute>
   </xsl:if>
   </style:table-properties>
  </style:style>
  <style:style style:name="celltitle" style:family="table-cell" style:parent-style-name="Default">
<style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" />
   <style:paragraph-properties fo:text-align="center" fo:margin-left="0in" >
   <xsl:if test="orientation = 'rtl' ">
    <xsl:attribute name="style:writing-mode">rl-tb</xsl:attribute>
   </xsl:if>
  </style:paragraph-properties>
   <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
  </style:style>
  <style:style style:name="DMY" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="dsnDMY"/>
  <style:style style:name="MDY" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="dsnMDY"/>
 <style:style style:name="strdata" style:family="table-cell" style:parent-style-name="Default">
   <style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" />
   <style:paragraph-properties fo:margin-left="0in">
   <xsl:if test="orientation = 'rtl' ">
    <xsl:attribute name="style:writing-mode">rl-tb</xsl:attribute>
   </xsl:if>
  </style:paragraph-properties>
  <style:paragraph-properties fo:margin-left="0in">
   <xsl:if test="orientation = 'rtl' ">
    <xsl:attribute name="style:writing-mode">rl-tb</xsl:attribute>
    <xsl:attribute name="fo:text-align">end</xsl:attribute>
   </xsl:if>
  </style:paragraph-properties>
  </style:style>
  <style:page-layout style:name="pm1">
   <style:page-layout-properties fo:page-width="11in" fo:page-height="8.5in" style:num-format="1" style:print-orientation="landscape" style:writing-mode="lr-tb"/>
   <style:header-style>
    <style:header-footer-properties fo:min-height="0.2953in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-bottom="0.0984in"/>
   </style:header-style>
   <style:footer-style>
    <style:header-footer-properties fo:min-height="0.2953in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-top="0.0984in"/>
   </style:footer-style>
  </style:page-layout>
  <style:page-layout style:name="pm2">
   <style:page-layout-properties style:writing-mode="lr-tb"/>
   <style:header-style>
    <style:header-footer-properties fo:min-height="0.2953in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-bottom="0.0984in" fo:border="2.49pt solid #000000" fo:padding="0.0071in" fo:background-color="#c0c0c0">
     <style:background-image/>
    </style:header-footer-properties>
   </style:header-style>
   <style:footer-style>
    <style:header-footer-properties fo:min-height="0.2953in" fo:margin-left="0in" fo:margin-right="0in" fo:margin-top="0.0984in" fo:border="2.49pt solid #000000" fo:padding="0.0071in" fo:background-color="#c0c0c0">
     <style:background-image/>
    </style:header-footer-properties>
   </style:footer-style>
  </style:page-layout>
 </office:automatic-styles>
 <office:master-styles>
  <style:master-page style:name="Default" style:page-layout-name="pm1">
   <style:header>
    <text:p><text:sheet-name>???</text:sheet-name></text:p>
   </style:header>
   <style:header-left style:display="false"/>
   <style:footer>
    <text:p>Page <text:page-number>1</text:page-number></text:p>
   </style:footer>
   <style:footer-left style:display="false"/>
  </style:master-page>
  <style:master-page style:name="Report" style:page-layout-name="pm2">
   <style:header>
    <style:region-left>
     <text:p><text:sheet-name>???</text:sheet-name> (<text:title>???</text:title>)</text:p>
    </style:region-left>
    <style:region-right>
     <text:p><text:date style:data-style-name="N2" text:date-value="2012-06-02">00/00/0000</text:date>, <text:time>00:00:00</text:time></text:p>
    </style:region-right>
   </style:header>
   <style:header-left style:display="false"/>
   <style:footer>
    <text:p>Page <text:page-number>1</text:page-number> / <text:page-count>99</text:page-count></text:p>
   </style:footer>
   <style:footer-left style:display="false"/>
  </style:master-page>
 </office:master-styles>
 <office:body>
   
<xsl:apply-templates select="sheet" />

 </office:body>
</office:document>
</xsl:template>

<xsl:template match="sheet">
  <office:spreadsheet>
   <table:table table:style-name="ta1" >
    <xsl:attribute name="table:name"><xsl:value-of select="name" /></xsl:attribute>
    
    <xsl:apply-templates select="colh" />
    
    <xsl:apply-templates select="row" />

   </table:table>
   <table:named-expressions/>
  </office:spreadsheet>
</xsl:template>

<xsl:template match="colh">
  <table:table-header-columns>
    <table:table-column table:style-name="namecol" table:default-cell-style-name="celltitle"/>
    <table:table-column table:style-name="namecol" table:default-cell-style-name="celltitle"/>
    <table:table-column table:style-name="regcol" table:default-cell-style-name="celltitle"/>
    <table:table-column table:style-name="paymcol" table:default-cell-style-name="celltitle"/>
    <table:table-column table:style-name="pobcol" table:default-cell-style-name="celltitle"/>
    <table:table-column table:style-name="emailcol" table:default-cell-style-name="celltitle"/>
    <table:table-column table:style-name="regcol" table:default-cell-style-name="celltitle"/>
  </table:table-header-columns>

  <table:table-row table:style-name="ro1">
    <xsl:for-each select="colheader">
   <table:table-cell table:style-name="celltitle" office:value-type="string">
    <text:p><xsl:value-of select="." /></text:p>
   </table:table-cell>
   </xsl:for-each>
  </table:table-row>
</xsl:template>

<xsl:template match="row">
  <table:table-row table:style-name="ro1">

    <table:table-cell table:style-name="strdata" office:value-type="string" >
     <text:p><xsl:value-of select="mname" /></text:p>
    </table:table-cell>

    <table:table-cell table:style-name="strdata" office:value-type="string" >
     <text:p><xsl:value-of select="lname" /></text:p>
    </table:table-cell>

    <table:table-cell table:style-name="strdata" office:value-type="float" >
        <xsl:attribute name="office:value"><xsl:value-of select="mbal"/></xsl:attribute>
     <text:p><xsl:value-of select="mbal" /></text:p>
    </table:table-cell>
    
    <table:table-cell table:style-name="strdata" office:value-type="string" >
     <text:p><xsl:value-of select="paym" /></text:p>
    </table:table-cell>

    <table:table-cell table:style-name="strdata" office:value-type="float" >
          <xsl:attribute name="office:value"><xsl:value-of select="pob"/></xsl:attribute>
     <text:p><xsl:value-of select="pob" /></text:p>
    </table:table-cell>

    <table:table-cell table:style-name="strdata" office:value-type="string" >
     <text:p><xsl:value-of select="email" /></text:p>
    </table:table-cell>

    <table:table-cell table:style-name="<!$OPEN_OFFICE_DATE_FORMAT$!>" office:value-type="date">
        <xsl:attribute name="office:date-value"><xsl:value-of select="djoin_v"/></xsl:attribute>
       <text:p><xsl:value-of select="djoin" /></text:p>
     </table:table-cell>
    
  </table:table-row>
</xsl:template>

</xsl:stylesheet>
