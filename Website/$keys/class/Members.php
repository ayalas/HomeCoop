<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//for coord/members.php page and 
class Members extends SQLBase {
    
 const POST_ACTION_LIST_SELECT = 11;
 const POST_ACTION_SEARCH = 12;
 
 const EXPORT_LIST_ITEM_ALL_MEMBERS_DATA = 1;
 const EXPORT_LIST_ITEM_SELECTED_MEMBERS_EMAILS = 2;
 
 const PROPERTY_SEARCH_PHRASE = "SearchPhrase";
 const PROPERTY_MEMBER_IDS_FOR_MAIL_EXPORT = "MemberIDs";
 
 protected $m_sMailList = NULL;
 protected $m_oXmlDoc = NULL;
 
 public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_SEARCH_PHRASE => NULL, self::PROPERTY_MEMBER_IDS_FOR_MAIL_EXPORT => NULL);
  }

 public function GetTable()
  {
      global $g_oMemberSession;
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
            
      if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      
      $sSearchPhrase = $this->m_aData[self::PROPERTY_SEARCH_PHRASE];

      $sSQL =   " SELECT M.MemberID, M.sName, M.sLoginName, M.sEMail, M.PaymentMethodKeyID, M.dJoined, M.mBalance, M.fPercentOverBalance, M.sEMail2, " . 
                       " M.sEMail3, M.sEMail4, " .
                       $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PAYMENT_METHODS, 'sPaymentMethod') .
                " FROM T_Member M INNER JOIN T_PaymentMethod PM ON M.PaymentMethodKeyID = PM.PaymentMethodKeyID " . 
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PAYMENT_METHODS);
      if ($sSearchPhrase != NULL && !empty($sSearchPhrase) )
      {
        //replace * by %
        $sSearchPhrase = str_replace('*','%',$sSearchPhrase);
        
        //add % if not present
        if (FALSE === stripos($sSearchPhrase, '%'))
            $sSearchPhrase .= '%';

        $sLike = " like '" . $sSearchPhrase . "' ";

        $sSQL .= " WHERE ( M.sLoginName " . $sLike .
                    " OR M.sName " . $sLike . 
                    " OR M.sEMail " . $sLike .
                    " OR M.sEMail2 " . $sLike .
                    " OR M.sEMail3 " . $sLike .
                    " OR M.sEMail4 " . $sLike 
                  . " ) ";
      }
      
      $sSQL .= " ORDER BY M.sName; ";

      $this->RunSQL( $sSQL );

      return $this->fetch();
  }
  
  public function GetExportList()
 {
   return array( self::EXPORT_LIST_ITEM_ALL_MEMBERS_DATA => '<!$EXPORT_LIST_ITEM_ALL_MEMBERS_DATA$!>',
       self::EXPORT_LIST_ITEM_SELECTED_MEMBERS_EMAILS => '<!$EXPORT_LIST_ITEM_SELECTED_MEMBERS_EMAILS$!>'
    );
 }
  
 public function GetMembersListForOrder($CoopOrderID, $OrderID)
 {
    if (!$this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    


    $sSQL = "SELECT M.MemberID, M.sName FROM T_Member M " . 
            " LEFT JOIN T_Order O ON O.CoopOrderKeyID = " . $CoopOrderID .
            " AND O.MemberID = M.MemberID ";
    if ($OrderID > 0)
      $sSQL .= " WHERE (O.OrderID = " . $OrderID . " OR O.OrderID IS NULL) ";
    else
      $sSQL .= " WHERE O.OrderID IS NULL ";
    $sSQL .= " ORDER BY M.sName asc;";

    $this->RunSQL( $sSQL );

    return $this->fetchAllKeyPair();
  }
  
 public function GetMembersListForOrders()
 {    
    if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      return NULL;

    $sSQL = "SELECT M.MemberID, M.sName FROM T_Member M ORDER BY M.sName asc;";

    $this->RunSQL( $sSQL );

    return $this->fetchAllKeyPair();
  }
  
  //export Open Office Calc flat xml (fods) of all members
  public function EchoXML()
  {
    global $g_sRootRelativePath;
    global $g_dNow;
    $sXslPath = NULL;
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
      return;
        
    //file name starts with delivery date
    $sFileName = $g_dNow->format('Y_m_d') . '_<!$MEMBERS_EXPORT_FILE_NAME_SUFFIX$!>.<!$ORDER_EXPORT_FILE_EXTENTION$!>';
    
    $sXslPath = $g_sRootRelativePath . 'xsl/members.xsl';
    
    $this->BuildXmlDoc();
    
    if ($this->m_oXmlDoc != NULL)
    {
      header('content-disposition: attachment;filename=' . $sFileName);
      $this->Transform($sXslPath);
    }
  }
  
  protected function BuildXmlDoc()
  {
    $this->m_oXmlDoc = new DOMDocument('1.0', 'utf-8');
    
    $document = $this->m_oXmlDoc->createElement('document');
    
    $sDir = LanguageSupport::GetCurrentHtmlDir();
    if ($sDir == NULL)
      $sDir = 'ltr';
    
    $orientation = $this->m_oXmlDoc->createElement('orientation', $sDir);
    $document->appendChild($orientation);
        
    $sheet = $this->m_oXmlDoc->createElement('sheet');
    
    $sheetname = $this->m_oXmlDoc->createElement('name', '<!$MEMBERS_EXPORT_FILE_NAME_SUFFIX$!>');
    $sheet->appendChild($sheetname);
    
    $colh = $this->m_oXmlDoc->createElement('colh');
    
    $ch = $this->m_oXmlDoc->createElement('colheader', '<!$FIELD_MEMBER_NAME$!>');
    $colh->appendChild($ch);
    
    $ch = $this->m_oXmlDoc->createElement('colheader', '<!$FIELD_LOGIN_NAME$!>');
    $colh->appendChild($ch);
    
    $ch = $this->m_oXmlDoc->createElement('colheader', '<!$FIELD_BALANCE$!>');
    $colh->appendChild($ch);
    
    $ch = $this->m_oXmlDoc->createElement('colheader', '<!$FIELD_PAYMENT_METHOD$!>');
    $colh->appendChild($ch);
    
    $ch = $this->m_oXmlDoc->createElement('colheader', '<!$FIELD_PERCENT_OVER_BALANCE$!>');
    $colh->appendChild($ch);
    
    $ch = $this->m_oXmlDoc->createElement('colheader', '<!$FIELD_EMAIL$!>');
    $colh->appendChild($ch);
    
    $ch = $this->m_oXmlDoc->createElement('colheader', '<!$FIELD_JOINED_ON$!>');
    $colh->appendChild($ch);
    
    $sheet->appendChild($colh);
    
    
    
    $recMember = $this->GetTable();
    
    $dJoined= NULL;
    $sEMails = NULL;
    $rd = NULL;
    while($recMember)
    {
      $row = $this->m_oXmlDoc->createElement('row');
      
      $rd = $this->m_oXmlDoc->createElement('mname', $recMember["sName"]);
      $row->appendChild($rd);
      
      $rd = $this->m_oXmlDoc->createElement('lname', $recMember["sLoginName"]);
      $row->appendChild($rd);
      
      $rd = $this->m_oXmlDoc->createElement('mbal', $recMember["mBalance"]);
      $row->appendChild($rd);
      
      $rd = $this->m_oXmlDoc->createElement('paym', $recMember["sPaymentMethod"]);
      $row->appendChild($rd);
      
      $rd = $this->m_oXmlDoc->createElement('pob', $recMember["fPercentOverBalance"]);
      $row->appendChild($rd);
      
      $sEMails = $recMember["sEMail"];
      if ($recMember["sEMail2"] != NULL)
        $sEMails .= ', ' . $recMember["sEMail2"];
      if ($recMember["sEMail3"] != NULL)
        $sEMails .= ', ' . $recMember["sEMail3"];
      if ($recMember["sEMail4"] != NULL)
        $sEMails .= ', ' . $recMember["sEMail4"];

      $rd = $this->m_oXmlDoc->createElement('email', $sEMails);
      $row->appendChild($rd);
      
      $dJoined = new DateTime($recMember["dJoined"]);
      
      $rd = $this->m_oXmlDoc->createElement('djoin_v', $dJoined->format(Consts::OPEN_OFFICE_DATE_VALUE_FORMAT) );
      $row->appendChild($rd);
      
      $rd = $this->m_oXmlDoc->createElement('djoin', $dJoined->format('<!$DATE_PICKER_DATE_FORMAT$!>') );
      $row->appendChild($rd);
      
      $sheet->appendChild($row);
      
      $recMember = $this->fetch();
    }
    
    $document->appendChild($sheet);
    
    $this->m_oXmlDoc->appendChild($document);
  }
  
  protected function Transform($sXslPath) {   
     $oXsl = new DOMDocument;
     $oXsl->load($sXslPath);

     $xslt = new XSLTProcessor;
     $xslt->importStylesheet($oXsl);
     echo $xslt->transformToXml($this->m_oXmlDoc);
  }
  
  public function GetMailingList()
  { 
    if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    if ($this->m_aData[self::PROPERTY_MEMBER_IDS_FOR_MAIL_EXPORT] == NULL)
      return NULL;

    $sSQL = "SELECT M.sEMail, M.sEmail2, M.sEmail3, M.sEmail4 FROM T_Member M WHERE M.MemberID IN (" . 
            $this->m_aData[self::PROPERTY_MEMBER_IDS_FOR_MAIL_EXPORT] . ");";              

    $this->RunSQL($sSQL);
    $recMail = $this->fetch();
    while($recMail)
    {
      $this->AddMail($recMail["sEMail"]);
      if ($recMail["sEmail2"] != NULL)
        $this->AddMail($recMail["sEmail2"]);
      if ($recMail["sEmail3"] != NULL)
        $this->AddMail($recMail["sEmail3"]);
      if ($recMail["sEmail4"] != NULL)
        $this->AddMail($recMail["sEmail4"]);
      $recMail = $this->fetch();
    }

    return $this->m_sMailList;
  }
  
  protected function AddMail($sEMailAddress)
  {
    if ($sEMailAddress == NULL) return;
    
    if ($this->m_sMailList == NULL)
      $this->m_sMailList = $sEMailAddress;
    else
      $this->m_sMailList .= ', ' . $sEMailAddress;
  }
  
  
}

?>
