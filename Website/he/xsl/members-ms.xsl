<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet" 
  xmlns:html="http://www.w3.org/TR/REC-html40" 
  xmlns:o="urn:schemas-microsoft-com:office:office" 
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
  xmlns="urn:schemas-microsoft-com:office:spreadsheet" 
  xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml" 
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" 
  xmlns:x="urn:schemas-microsoft-com:office:excel"
  version='1.0'>
<xsl:output method="xml"/>

<xsl:template match="/">
<xsl:apply-templates select="document" />
</xsl:template>

<xsl:template match="document">
<Workbook xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel">
 <o:OfficeDocumentSettings>
    <o:Colors>
      <o:Color>
        <o:Index>3</o:Index>
        <o:RGB>#c0c0c0</o:RGB>
      </o:Color>
      <o:Color>
        <o:Index>4</o:Index>
        <o:RGB>#ff0000</o:RGB>
      </o:Color>
    </o:Colors>
 </o:OfficeDocumentSettings>
<x:ExcelWorkbook>
  <x:WindowHeight>9000</x:WindowHeight>
  <x:WindowWidth>13860</x:WindowWidth>
  <x:WindowTopX>240</x:WindowTopX>
  <x:WindowTopY>75</x:WindowTopY>
  <x:ProtectStructure>False</x:ProtectStructure>
  <x:ProtectWindows>False</x:ProtectWindows>
</x:ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Default"/>
  <Style ss:ID="ta1"/>
  <Style ss:ID="ce1">
    <Font ss:Bold="1" />
    <Alignment ss:Horizontal="Center" ss:Indent="0"/>
  </Style>
  <Style ss:ID="ce2">
    <Font ss:Bold="1" />
    <Alignment ss:Indent="0">
      <xsl:if test="orientation = 'rtl' ">
        <xsl:attribute name="ss:Horizontal">Right</xsl:attribute>
      </xsl:if>
    </Alignment>
  </Style>
  <Style ss:ID="ce3">
    <Alignment ss:Indent="0">
      <xsl:if test="orientation = 'rtl' ">
        <xsl:attribute name="ss:Horizontal">Right</xsl:attribute>
      </xsl:if>
    </Alignment>
  </Style>
  <Style ss:ID="ce4">
    <Alignment ss:Horizontal="Center" ss:Indent="0"/>
  </Style>
  <Style ss:ID="ce5">
    <Alignment ss:Indent="0" ss:Horizontal="Left" />
  </Style>
  <Style ss:ID="ce6">
    <Alignment ss:Indent="0" ss:Horizontal="Center" />
    <NumberFormat ss:Format="Short Date"/>
  </Style>
</Styles>

<xsl:apply-templates select="sheet" />

</Workbook>
</xsl:template>

<xsl:template match="sheet">
  <ss:Worksheet>
    <xsl:attribute name="ss:Name"><xsl:value-of select="name" /></xsl:attribute>
   <xsl:if test="orientation = 'rtl' ">
     <xsl:attribute name="ss:RightToLeft">1</xsl:attribute>
   </xsl:if>
   
   <Table ss:StyleID="ta1">
    
    <xsl:apply-templates select="colh" />
    
    <xsl:apply-templates select="row" />

   </Table>
   <x:WorksheetOptions>
   <xsl:if test="orientation = 'rtl' ">
    <x:DisplayRightToLeft/>
   </xsl:if>
   <x:ProtectObjects>False</x:ProtectObjects>
   <x:ProtectScenarios>False</x:ProtectScenarios>
  </x:WorksheetOptions>
  </ss:Worksheet>
</xsl:template>

<xsl:template match="colh">
  <Column ss:Width="115.2" />
  <Column ss:Width="115.2" />
  <Column ss:Width="72" />
  <Column ss:Width="136.8" />
  <Column ss:Width="86.4" />
  <Column ss:Width="177.6" />
  <Column ss:Width="72" />
  <Column ss:Width="72" />
  <Column ss:Width="72" />
    
  <Row ss:Height="15.84">
    <xsl:for-each select="colheader">
      <Cell ss:StyleID="ce1">
        <Data ss:Type="String"><xsl:value-of select="."/></Data>
      </Cell>
   </xsl:for-each>
  </Row>
</xsl:template>

<xsl:template match="row">
  <Row ss:Height="15.84">

   <Cell ss:StyleID="ce3">
      <Data ss:Type="String"><xsl:value-of select="mname"/></Data>
   </Cell>
   
   <Cell ss:StyleID="ce3">
      <Data ss:Type="String"><xsl:value-of select="lname"/></Data>
   </Cell>
   
   <Cell ss:StyleID="ce4">
      <Data ss:Type="Number"><xsl:value-of select="mbal"/></Data>
   </Cell>
   
   <Cell ss:StyleID="ce4">
      <Data ss:Type="Number"><xsl:value-of select="mbalh"/></Data>
   </Cell>
   
   <Cell ss:StyleID="ce4">
      <Data ss:Type="Number"><xsl:value-of select="mbali"/></Data>
   </Cell>
   
   <Cell ss:StyleID="ce3">
      <Data ss:Type="String"><xsl:value-of select="paym"/></Data>
   </Cell>
    
   <Cell ss:StyleID="ce4">
      <Data ss:Type="Number"><xsl:value-of select="pob"/></Data>
   </Cell>
   
   <Cell ss:StyleID="ce5">
      <Data ss:Type="String"><xsl:value-of select="email"/></Data>
   </Cell>

   <Cell ss:StyleID="ce6">
      <Data ss:Type="DateTime"><xsl:value-of select="djoin_v"/></Data>
   </Cell>
  </Row>
</xsl:template>

</xsl:stylesheet>
