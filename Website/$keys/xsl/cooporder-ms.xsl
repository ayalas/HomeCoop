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
      <o:Color>
        <o:Index>5</o:Index>
        <o:RGB><!$COOP_ORDER_EXPORT_ROW_ALTERNATE_BG_COLOR$!></o:RGB>
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
    <Interior ss:Color="<!$COOP_ORDER_EXPORT_ROW_ALTERNATE_BG_COLOR$!>" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="ce4">
    <Alignment ss:Horizontal="Center" ss:Indent="0"/>
    <Interior ss:Color="<!$COOP_ORDER_EXPORT_ROW_ALTERNATE_BG_COLOR$!>" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="ce5">
    <Alignment ss:Indent="0">
      <xsl:if test="orientation = 'rtl' ">
        <xsl:attribute name="ss:Horizontal">Right</xsl:attribute>
      </xsl:if>
    </Alignment>
  </Style>
  <Style ss:ID="ce6">
    <Alignment ss:Horizontal="Center" ss:Indent="0"/>
  </Style>
  <Style ss:ID="ce7">
    <Font ss:Bold="1" />
    <Alignment ss:Horizontal="Center" ss:Indent="0"/>
    <Interior ss:Color="<!$COOP_ORDER_EXPORT_ROW_ALTERNATE_BG_COLOR$!>" ss:Pattern="Solid"/>
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
    
    <xsl:apply-templates select="sum" />

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
  <Column ss:Width="129.6" />
  <Column ss:Width="43.2" />
  <Column ss:Width="43.2" />
  <Column ss:Width="72" />

  <xsl:for-each select="memh">
    <Column ss:Width="43.2" />
  </xsl:for-each>
  
  <Column ss:Width="72" />

  <Row ss:Height="15.84">
    <Cell ss:StyleID="ce1">
      <Data ss:Type="String"><xsl:value-of select="prdh"/></Data>
    </Cell>
    <Cell ss:StyleID="ce1">
      <Data ss:Type="String"><xsl:value-of select="priceh"/></Data>
    </Cell>
    <Cell ss:StyleID="ce1">
      <Data ss:Type="String"><xsl:value-of select="quantityh"/></Data>
    </Cell>
    <Cell ss:StyleID="ce1">
      <Data ss:Type="String"><xsl:value-of select="packageh"/></Data>
    </Cell>
    
    <xsl:for-each select="memh">
      <Cell ss:StyleID="ce2">
        <Data ss:Type="String"><xsl:value-of select="."/></Data>
      </Cell>
    </xsl:for-each>

   <xsl:for-each select="totalh">
   <Cell ss:StyleID="ce1">
    <Data ss:Type="String"><xsl:value-of select="."/></Data>
   </Cell>
   </xsl:for-each>
  </Row>
</xsl:template>


<xsl:template match="row">
  <Row ss:Height="15.84">
    <xsl:choose> 
    <xsl:when test="position() mod 2 = 0">
       <xsl:attribute name="ss:StyleID">ce3</xsl:attribute>
    </xsl:when>
    <xsl:otherwise>
       <xsl:attribute name="ss:StyleID">ce5</xsl:attribute>
    </xsl:otherwise>
    </xsl:choose>
   <Cell>
      <Data ss:Type="String"><xsl:value-of select="prd"/></Data>
   </Cell>
   <Cell>
      <xsl:choose> 
      <xsl:when test="position() mod 2 = 0">
         <xsl:attribute name="ss:StyleID">ce4</xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
         <xsl:attribute name="ss:StyleID">ce6</xsl:attribute>
      </xsl:otherwise>
      </xsl:choose>
      <Data ss:Type="Number"><xsl:value-of select="price"/></Data>
   </Cell>
   <Cell>
      <Data ss:Type="String"><xsl:value-of select="quantity"/></Data>
   </Cell>
   <Cell>
      <Data ss:Type="String"><xsl:value-of select="package"/></Data>
   </Cell>
    
   <xsl:for-each select="mem">
    <xsl:choose>
     <xsl:when test=". != ''" >
       <Cell>
          <xsl:choose> 
          <xsl:when test="(count(../preceding-sibling::row) + 1) mod 2 = 0">
             <xsl:attribute name="ss:StyleID">ce4</xsl:attribute>
          </xsl:when>
          <xsl:otherwise>
             <xsl:attribute name="ss:StyleID">ce6</xsl:attribute>
          </xsl:otherwise>
          </xsl:choose>
          <Data ss:Type="Number"><xsl:value-of select="."/></Data>
       </Cell>
     </xsl:when>
     <xsl:otherwise>
       <Cell/>
     </xsl:otherwise>
    </xsl:choose>
   </xsl:for-each>
   
   <xsl:for-each select="totalb">
    <Cell>
        <xsl:choose> 
        <xsl:when test="(count(../preceding-sibling::row) + 1) mod 2 = 0">
           <xsl:attribute name="ss:StyleID">ce7</xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
           <xsl:attribute name="ss:StyleID">ce1</xsl:attribute>
        </xsl:otherwise>
        </xsl:choose>
        <Data ss:Type="Number"><xsl:value-of select="."/></Data>
     </Cell>
    </xsl:for-each>

   <xsl:for-each select="total">
     <Cell>
        <xsl:choose> 
        <xsl:when test="(count(../preceding-sibling::row) + 1) mod 2 = 0">
           <xsl:attribute name="ss:StyleID">ce7</xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
           <xsl:attribute name="ss:StyleID">ce1</xsl:attribute>
        </xsl:otherwise>
        </xsl:choose>
        <Data ss:Type="Number"><xsl:value-of select="."/></Data>
     </Cell>
    </xsl:for-each>
  </Row>
</xsl:template>

<xsl:template match="sum">
  <Row ss:Height="15.84">
     <Cell ss:StyleID="ce1">
       <Data ss:Type="String"><xsl:value-of select="sumlabel"/></Data>
     </Cell>
     <Cell ss:StyleID="ce1">
      <Data ss:Type="Number"><xsl:value-of select="sumtotal"/></Data>
     </Cell>
     <Cell/>
     <Cell/>
    
     <xsl:for-each select="summem">
     <Cell ss:StyleID="ce1">
        <Data ss:Type="Number"><xsl:value-of select="."/></Data>
     </Cell>
     </xsl:for-each>
     
     <Cell/>
  </Row>
</xsl:template>
</xsl:stylesheet>