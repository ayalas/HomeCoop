<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

//export coop order data underlying class
//exports data to Open Office Calc Flat-xml (fods format) file
class CoopOrderExport extends CoopOrderSubBase {
      
  const POST_ACTION_LIST_SELECT = 10;
  const POST_ACTION_DISPLAY_PRODUCTS_MAILS = 11;
  //these values are long, so record keys won't interrupt with their bitwising  
  const LIST_ITEM_TYPE_ORDER      = 0x1000000;
  const LIST_ITEM_TYPE_MAILS      = 0x2000000;
  const LIST_ITEM_PRODUCER        = 0x4000000;
  const LIST_ITEM_PICKUP_LOCATION = 0x8000000;  
  const LIST_ITEM_PRODUCTS        = 0x10000000;
  const LIST_ITEM_TYPE_SUMMARY    = 0x20000000;
  
  const PROPERTY_DATASET_LIST = "DataSetList";
  const PROPERTY_PRODUCT_IDS = "ProductIDs";
  const PROPERTY_PRODUCERS = "Producers";
  const PROPERTY_PICKUP_LOCATIONS = "PickupLocations";
  
  const PERMISSION_EXPORT_COOP_ORDER = 1;
  const PERMISSION_GET_PRODUCT_LIST = 2;
  const PERMISSION_PICKUP_LOCATION = 3;
  const PERMISSION_PRODUCER = 4;
  const PERMISSION_SUMS = 5;
  
  protected $m_nProducerID = NULL;
  protected $m_nPickupLocationID = NULL;
  protected $m_sMailList = NULL;
  protected $m_aCurrentProduct = NULL;
  protected $m_aCurrentItem = NULL;
  protected $m_aCurrentProducer = NULL;
  protected $m_aCurrentPickupLocation = NULL;
  protected $m_aProducts = NULL;
  protected $m_aOrders = NULL;
  protected $m_oXmlDoc = NULL;
  
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_ID => 0,
                            self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_DATASET_LIST => NULL,
                            self::PROPERTY_PRODUCT_IDS => NULL,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            self::PROPERTY_PRODUCERS => NULL,
                            self::PROPERTY_PICKUP_LOCATIONS => NULL
                            );
    
    $this->m_aOriginalData = $this->m_aData;
  }
  
  //preserve data after post back
  public function CopyData()
  {
    $this->CopyCoopOrderData();
    $this->m_aData[self::PROPERTY_DATASET_LIST] = $this->m_aOriginalData[self::PROPERTY_DATASET_LIST];
    $this->m_aData[self::PROPERTY_PRODUCERS] = $this->m_aOriginalData[self::PROPERTY_PRODUCERS];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATIONS] = $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATIONS];
  }
  
  //get the options to export based on the current coop order and the user's permissions
  public function GetDataSetsList()
  {
    global $g_oMemberSession;
     if (!$this->LoadCoopOrderData())
        return NULL;
     
     if (!$this->AddPermissionBridge(self::PERMISSION_EXPORT_COOP_ORDER, Consts::PERMISSION_AREA_COOP_ORDERS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], 
             FALSE))
     {
       $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
       return NULL;
     }
     
     $fIndex = 0;
     $nID = 0;

     $aReturn = array();
     
     $bHasOrdersPermission = $this->AddPermissionBridge(self::LIST_ITEM_TYPE_MAILS, Consts::PERMISSION_AREA_COOP_ORDER_ORDERS, 
             Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], 
             FALSE);

     if ($this->AddPermissionBridge(self::LIST_ITEM_TYPE_ORDER, Consts::PERMISSION_AREA_COOP_ORDER_SUMS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], 
             FALSE))
     {
        $this->CopyPermission(self::LIST_ITEM_TYPE_ORDER, self::LIST_ITEM_TYPE_ORDER + self::LIST_ITEM_TYPE_SUMMARY);
        $aReturn[( self::LIST_ITEM_TYPE_ORDER ) ] = 'Entire Data';
        $aReturn[( self::LIST_ITEM_TYPE_ORDER + self::LIST_ITEM_TYPE_SUMMARY ) ] = 'Cooperative Order Summary';
     
        if ($bHasOrdersPermission)
          $aReturn[ ( self::LIST_ITEM_TYPE_MAILS ) ] = 'E-mails of All Ordering Members';
     }
               
     $oPickupLocations = new CoopOrderPickupLocations;
     $recPickupLoc = $oPickupLocations->LoadList($this->m_aData[self::PROPERTY_COOP_ORDER_ID], 0);
     while($recPickupLoc)
     {
       $nID = $recPickupLoc["PickupLocationKeyID"];
       $fIndex = self::LIST_ITEM_PICKUP_LOCATION + self::LIST_ITEM_TYPE_ORDER + $nID;
       
       if ($this->AddPermissionBridge($fIndex, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, $recPickupLoc["CoordinatingGroupID"], 
             FALSE))
       {
         $this->m_aData[self::PROPERTY_PICKUP_LOCATIONS][$nID] = array( 
                    PickupLocation::PROPERTY_NAME => $recPickupLoc["sPickupLocation"], 
                    PickupLocation::PROPERTY_EXPORT_FILE_NAME => $recPickupLoc["sExportFileName"],
                    PickupLocation::PROPERTY_COORDINATING_GROUP_ID => $recPickupLoc["CoordinatingGroupID"]

                 );


         $aReturn[$fIndex ] = sprintf('Delivery to %s',htmlspecialchars($recPickupLoc["sPickupLocation"]));
         
         $fIndex = self::LIST_ITEM_PICKUP_LOCATION + self::LIST_ITEM_TYPE_MAILS + $nID;
         //must have permission to member orders to view member emails
         if ($this->AddPermissionBridge($fIndex, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS, 
             Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, $recPickupLoc["CoordinatingGroupID"], 
             FALSE))
          $aReturn[$fIndex ] = sprintf('E-mails of Ordering Members to %s',htmlspecialchars($recPickupLoc["sPickupLocation"]));
       }
       $recPickupLoc = $oPickupLocations->fetch();
     }
     
     
     if ($this->AddPermissionBridge(self::LIST_ITEM_PRODUCER, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, 0, 
             TRUE))
     {
       $recProducer = $this->GetProducers($this->GetPermissionScope(self::LIST_ITEM_PRODUCER));

       while($recProducer)
       {
         $nID = $recProducer["ProducerKeyID"];
         $fIndex = self::LIST_ITEM_PRODUCER + self::LIST_ITEM_TYPE_ORDER + $nID;
         $this->CopyPermission(self::LIST_ITEM_PRODUCER, $fIndex);

         $this->m_aData[self::PROPERTY_PRODUCERS][$nID] = array( 
                   Producer::PROPERTY_PRODUCER_NAME => $recProducer["sProducer"], 
                   Producer::PROPERTY_EXPORT_FILE_NAME => $recProducer["sExportFileName"],
                   CoopOrderProducer::PROPERTY_TOTAL_DELIVERY => $recProducer["mTotalDelivery"],
                   CoopOrderProducer::PROPERTY_PRODUCER_TOTAL => $recProducer["mProducerTotal"],
                   Producer::PROPERTY_COORDINATING_GROUP_ID => $recProducer["CoordinatingGroupID"]
                  );


         $aReturn[$fIndex ] = sprintf('Order from %s',  htmlspecialchars($recProducer["sProducer"]));
         $fIndex = self::LIST_ITEM_PRODUCER + self::LIST_ITEM_TYPE_MAILS + $nID;
         //must have permission to member orders to view member emails
         if ($bHasOrdersPermission)
          $aReturn[$fIndex ] = sprintf('E-mails of Ordering Members from %s',  htmlspecialchars ($recProducer["sProducer"]));

         $recProducer = $this->fetch();
       }
     }
     
     if ($bHasOrdersPermission)
      $aReturn[ (self::LIST_ITEM_PRODUCTS + self::LIST_ITEM_TYPE_MAILS)  ] = 'E-mails of Specific Products&#x27; Ordering Members';
     
     $this->m_aData[self::PROPERTY_DATASET_LIST] = $aReturn;
     
     $this->m_aOriginalData = $this->m_aData;
    
     return $aReturn;
  }
  
  //get a mailing list output
  public function GetMailingList()
  { 
    if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_TYPE_MAILS) !== CoopOrderExport::LIST_ITEM_TYPE_MAILS )
     return NULL;

    $sSQL = "SELECT M.sEMail, M.sEmail2, M.sEmail3, M.sEmail4 FROM T_Order O INNER JOIN T_Member M ON O.MemberID = M.MemberID " .               
             " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . " AND O.mCoopTotal > 0 ";
    if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_PRODUCER) === CoopOrderExport::LIST_ITEM_PRODUCER )
    {
     $nProducerID = $this->m_aData[self::PROPERTY_ID] - CoopOrderExport::LIST_ITEM_TYPE_MAILS - CoopOrderExport::LIST_ITEM_PRODUCER;

     $sSQL .= " AND (SELECT COUNT(1) FROM T_OrderItem OI INNER JOIN T_Product PRD ON OI.ProductKeyID = PRD.ProductKeyID " .
             " WHERE OI.OrderID = O.OrderID AND PRD.ProducerKeyID = " . $nProducerID . ") > 0;";
    }
    else if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_PICKUP_LOCATION) === CoopOrderExport::LIST_ITEM_PICKUP_LOCATION )
    {
     $nPickupLocationID = $this->m_aData[self::PROPERTY_ID] - CoopOrderExport::LIST_ITEM_TYPE_MAILS - CoopOrderExport::LIST_ITEM_PICKUP_LOCATION;
     $sSQL .= " AND O.PickupLocationKeyID = " . $nPickupLocationID . ";";
    }
    else if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_PRODUCTS) === CoopOrderExport::LIST_ITEM_PRODUCTS )
    {
    if ($this->m_aData[self::PROPERTY_PRODUCT_IDS] == NULL)
      return NULL;
     $sSQL .= " AND (SELECT COUNT(1) FROM T_OrderItem OI " .
             " WHERE OI.OrderID = O.OrderID AND OI.ProductKeyID IN (" .  $this->m_aData[self::PROPERTY_PRODUCT_IDS] . ")) > 0;";
    }
    else if ($this->m_aData[self::PROPERTY_ID] !== CoopOrderExport::LIST_ITEM_TYPE_MAILS) 
      throw new Exception ('unexpected call to GetMailingList. ID:' . $this->m_aData[self::PROPERTY_ID]);
    //else: entire order

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
  
  protected function GetProducers($nScope)
  {    
      global $g_oMemberSession;
      $sSQL =   " SELECT COP.ProducerKeyID, COP.mTotalDelivery, COP.mMaxProducerOrder, IfNull(COP.mProducerTotal,0) mProducerTotal,  " .
            " P.sExportFileName, IfNull(COP.fBurden,0) fBurden, COP.fMaxBurden, P.CoordinatingGroupID, " .
                   $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            " FROM T_CoopOrderProducer COP INNER JOIN T_Producer P ON COP.ProducerKeyID = P.ProducerKeyID " . 
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
            " WHERE COP.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID];
      if ($nScope == Consts::PERMISSION_SCOPE_GROUP_CODE)
        $sSQL .= " AND P.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . ") ";
      $sSQL .= " ORDER BY P_S.sString; ";

      $this->RunSQL( $sSQL );

      return $this->fetch();
  }
  
  public function GetProductList()
 {   
    global $g_oMemberSession;
    if (!$this->AddPermissionBridge(self::PERMISSION_GET_PRODUCT_LIST, Consts::PERMISSION_AREA_COOP_ORDERS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, NULL, 
             TRUE))
     {
       $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
       return NULL;
     }
    
     //if user has only limited permission to pickup locations - limit products to those pickup locations
     $this->AddPermissionBridge(self::PERMISSION_PICKUP_LOCATION, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, NULL, 
             TRUE);
     $nPLScope = $this->GetPermissionScope(self::PERMISSION_PICKUP_LOCATION);
     
     //if user has only limited permission to producers - limit according to producers
     $this->AddPermissionBridge(self::PERMISSION_PRODUCER, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, NULL, 
             TRUE);
     $nPRScope = $this->GetPermissionScope(self::PERMISSION_PRODUCER);
     
     //don't allow access if user has no permission to export
     if (!$this->AddPermissionBridge(self::PERMISSION_SUMS, Consts::PERMISSION_AREA_COOP_ORDER_SUMS, 
             Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, NULL, 
             TRUE) && $nPLScope == 0 && $nPRScope == 0)
     {
       $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
       return NULL;
     }

            
    $sSQL =   " SELECT DISTINCT COPRD.ProductKeyID, "
            . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
          " FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID ";
    if ($nPLScope == Consts::PERMISSION_SCOPE_GROUP_CODE)
    {
      $sSQL .= " INNER JOIN T_CoopOrderPickupLocationProduct COPLPRD ON COPLPRD.CoopOrderKeyID = COPRD.CoopOrderKeyID " .
             " AND COPLPRD.ProductKeyID = COPRD.ProductKeyID " .
             " INNER JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = COPLPRD.PickupLocationKeyID" .
             " AND PL.CoordinatingGroupID in (" . implode(",", $g_oMemberSession->Groups) . ") ";
    }
    if ($nPRScope == Consts::PERMISSION_SCOPE_GROUP_CODE)
    {
      $sSQL .= " INNER JOIN T_Producer P ON P.ProducerKeyID = PRD.ProducerKeyID " .
              " AND P.CoordinatingGroupID in (" . implode(",", $g_oMemberSession->Groups) . ") ";
    }
    $sSQL .=    $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID];

    if ($nPLScope == Consts::PERMISSION_SCOPE_GROUP_CODE)
      $sSQL .= " AND (COPLPRD.fTotalCoopOrder > 0 OR COPRD.nJoinedStatus > 0) ";
    else
      $sSQL .= " AND (COPRD.fTotalCoopOrder > 0 OR COPRD.nJoinedStatus > 0) ";
    
    $sSQL .= " ORDER BY PRD.nSortOrder; ";
    
    $this->RunSQL( $sSQL );
    return $this->fetchAllKeyPair();
 }
  
  //get the xml for the fods file, based on xsl transformation
  public function EchoXML()
  {
    global $g_sRootRelativePath;
    $sXslPath = NULL;
    
    if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_TYPE_ORDER) !== CoopOrderExport::LIST_ITEM_TYPE_ORDER )
      return;
    
    //checks permissions, provides some header information
    if ($this->GetDataSetsList() == NULL)
      return;
    
    if (!$this->HasPermission( $this->m_aData[self::PROPERTY_ID] ))
      return;
    
    //file name starts with delivery date
    $sFileName = $this->Delivery->format('Y_m_d');
    
    $sXslPath = $g_sRootRelativePath . 'xsl/cooporder.xsl';
    
    //strip ID
    if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_PRODUCER) === CoopOrderExport::LIST_ITEM_PRODUCER )
    {
     $this->m_nProducerID = $this->m_aData[self::PROPERTY_ID] - CoopOrderExport::LIST_ITEM_TYPE_ORDER - CoopOrderExport::LIST_ITEM_PRODUCER;
     $this->m_aCurrentProducer = $this->m_aData[self::PROPERTY_PRODUCERS][$this->m_nProducerID];
     
     $this->ProducerQueryAndBuildXML();
     
     if ($this->m_aCurrentProducer[Producer::PROPERTY_EXPORT_FILE_NAME] != NULL)
      $sFileName .= $this->m_aCurrentProducer[Producer::PROPERTY_EXPORT_FILE_NAME];

    }
    else if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_PICKUP_LOCATION) === CoopOrderExport::LIST_ITEM_PICKUP_LOCATION )
    {
     $this->m_nPickupLocationID = $this->m_aData[self::PROPERTY_ID] - CoopOrderExport::LIST_ITEM_TYPE_ORDER - CoopOrderExport::LIST_ITEM_PICKUP_LOCATION;
     $this->m_aCurrentPickupLocation = $this->m_aData[self::PROPERTY_PICKUP_LOCATIONS][$this->m_nPickupLocationID];
     
     $this->PickupLocationQueryAndBuildXML();
     
     if ($this->m_aCurrentPickupLocation[PickupLocation::PROPERTY_EXPORT_FILE_NAME] != NULL)
      $sFileName .= $this->m_aCurrentPickupLocation[PickupLocation::PROPERTY_EXPORT_FILE_NAME];
    }
    else if ( ($this->m_aData[self::PROPERTY_ID] & CoopOrderExport::LIST_ITEM_TYPE_SUMMARY) === CoopOrderExport::LIST_ITEM_TYPE_SUMMARY )
    {     
     $this->CoopOrderSummaryQueryAndBuildXML();
     
     $sFileName .= 'Coop';
    }
    else if ($this->m_aData[self::PROPERTY_ID] == CoopOrderExport::LIST_ITEM_TYPE_ORDER) 
    {
     $this->CoopOrderQueryAndBuildXML(); 
     
     $sFileName .= 'All';
    }
    else
      return;
    
    $sFileName .= '.ods';
    
    if ($this->m_oXmlDoc != NULL)
    {
      header('content-disposition: attachment;filename=' . $sFileName);
      $this->Transform($sXslPath);
    }
  }
  
  //transform xml and xsl
  protected function Transform($sXslPath) {   
     $oXsl = new DOMDocument;
     $oXsl->load($sXslPath);

     $xslt = new XSLTProcessor;
     $xslt->importStylesheet($oXsl);
     echo $xslt->transformToXml($this->m_oXmlDoc);
  }
  
  protected function AddMail($sEMailAddress)
  {
    if ($sEMailAddress == NULL) return;
    
    if ($this->m_sMailList == NULL)
      $this->m_sMailList = $sEMailAddress;
    else
      $this->m_sMailList .= ', ' . $sEMailAddress;
  }
  
  //helper functions to produce fods file based on xsl
  
  protected function ProducerQueryAndBuildXML()
  {
    $document = NULL;
    $this->BuildDocumentHeaderXML($document /*by ref*/);
    
    $producersheet = NULL;
    $this->BuildProducerSheetXML($producersheet /*by ref*/);
    $document->appendChild($producersheet);
    unset($producersheet);
    
    $this->m_oXmlDoc->appendChild($document);
  }
  
  protected function PickupLocationQueryAndBuildXML()
  {    
    $document = NULL;
    $this->BuildDocumentHeaderXML($document /*by ref*/);
    
    $ordsheet = NULL;
    $this->QueryCoopOrderPickupLocationProducts();
    $this->PickupLocationOrdersSheetXML($ordsheet /*by ref*/);
    
    $this->QueryCoopOrderPickupLocationProducers();
    
    $document->appendChild($ordsheet);
    
    foreach($this->m_aData[self::PROPERTY_PRODUCERS] as $this->m_nProducerID => $this->m_aCurrentProducer)
    {
      if ($this->m_aCurrentProducer[CoopOrderProducer::PROPERTY_PRODUCER_TOTAL] > 0)
      {
        $producersheet = NULL;
        $this->BuildProducerSheetXML($producersheet /*by ref*/);
        $document->appendChild($producersheet);
        unset($producersheet);
      }
    }
    
    $this->m_oXmlDoc->appendChild($document);
  }
  
  protected function PickupLocationOrdersSheetXML(&$sheet)
  {    
    $this->QueryOrders();
    
    $this->BuildNewSheetXML( $this->m_aCurrentPickupLocation[PickupLocation::PROPERTY_NAME], TRUE, $sheet /*by ref*/ );
    
    $this->QueryCoopOrderProductItems();

    $this->BuildSheetBodyXML( TRUE, FALSE, $sheet /*by ref*/);
    
    $this->BuildSheetSummaryRowXML( TRUE,'Total',
       NULL /* means calculate by orders totals*/, $sheet /*by ref*/);

  }
  
  protected function BuildProducerSheetXML(&$sheet)
  {
    if ($this->m_nPickupLocationID > 0)
      $this->QueryCoopOrderPickupLocationProducts();
    else
      $this->QueryCoopOrderProducts();
    
    $this->BuildNewSheetXML( $this->m_aCurrentProducer[Producer::PROPERTY_PRODUCER_NAME], FALSE, $sheet /*by ref*/ );
    
    $this->BuildSheetBodyXML( FALSE, TRUE, $sheet /*by ref*/);

    if ($this->m_aCurrentProducer[CoopOrderProducer::PROPERTY_TOTAL_DELIVERY] > 0) //if has delivery costs
    {
      $this->BuildSheetSummaryRowXML( FALSE,'Total Products',
                $this->m_aCurrentProducer[CoopOrderProducer::PROPERTY_PRODUCER_TOTAL], $sheet /*by ref*/);
      
      $this->BuildSheetSummaryRowXML( FALSE,'Delivery',
         $this->m_aCurrentProducer[CoopOrderProducer::PROPERTY_TOTAL_DELIVERY], $sheet /*by ref*/);

      $this->BuildSheetSummaryRowXML( FALSE,'Total',
         $this->m_aCurrentProducer[CoopOrderProducer::PROPERTY_PRODUCER_TOTAL] +  
              $this->m_aCurrentProducer[CoopOrderProducer::PROPERTY_TOTAL_DELIVERY], $sheet /*by ref*/);
    }
    else
      $this->BuildSheetSummaryRowXML( FALSE,'Total',
                $this->m_aCurrentProducer[CoopOrderProducer::PROPERTY_PRODUCER_TOTAL], $sheet /*by ref*/);

  }
  
  protected function CoopOrderQueryAndBuildXML()
  {
    $document = NULL;
    $this->BuildDocumentHeaderXML($document /*by ref*/);

    $sheet = NULL;
    $this->CoopOrderSummarySheetXML($sheet);
    $document->appendChild($sheet);
       
    //producers
    foreach($this->m_aData[self::PROPERTY_PRODUCERS] as $this->m_nProducerID => $this->m_aCurrentProducer)
    {      
      $producersheet = NULL;
      $this->BuildProducerSheetXML($producersheet /*by ref*/);
      $document->appendChild($producersheet);
      unset($producersheet);
    }
    
    //clear current producer
    $this->m_nProducerID = 0;
    
    //pickup locations
    foreach($this->m_aData[self::PROPERTY_PICKUP_LOCATIONS] as $this->m_nPickupLocationID => $this->m_aCurrentPickupLocation)
    {
      $plsheet = NULL;
      $this->QueryCoopOrderPickupLocationProducts();
      $this->PickupLocationOrdersSheetXML( $plsheet /*by ref*/);
      $document->appendChild($plsheet);
      unset($plsheet);
    }
    
    $this->m_oXmlDoc->appendChild($document);
  }
  
  protected function CoopOrderSummaryQueryAndBuildXML()
  {    
    $document = NULL;
    $this->BuildDocumentHeaderXML($document /*by ref*/);

    $sheet = NULL;
    $this->CoopOrderSummarySheetXML($sheet);
 
    $document->appendChild($sheet);
    
    $this->m_oXmlDoc->appendChild($document);
  }
  
  protected function CoopOrderSummarySheetXML(&$sheet)
  {
    $this->QueryOrders();
    
    $this->BuildNewSheetXML($this->Name, TRUE, $sheet /*by ref*/);
    
    $this->QueryCoopOrderProducts();
    
    $this->QueryCoopOrderProductItems();

    $this->BuildSheetBodyXML( TRUE, FALSE, $sheet /*by ref*/);
    
    $this->BuildSheetSummaryRowXML( TRUE,'Total',
       $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL], $sheet /*by ref*/);
  }
  
  protected function BuildDocumentHeaderXML(&$document)
  {
    $this->m_oXmlDoc = new DOMDocument('1.0', 'utf-8');
    
    $document = $this->m_oXmlDoc->createElement('document');
    
    $sDir = LanguageSupport::GetCurrentHtmlDir();
    if ($sDir == NULL)
      $sDir = 'ltr';
    
    $orientation = $this->m_oXmlDoc->createElement('orientation', $sDir);
    $document->appendChild($orientation);
  }
  
  protected function BuildNewSheetXML($sSheetName, $bIncludeOrders, &$sheet)
  {
    $sheet = $this->m_oXmlDoc->createElement('sheet');
    
    $sheetname = $this->m_oXmlDoc->createElement('name', $sSheetName);
    $sheet->appendChild($sheetname);
    
    $colh = $this->m_oXmlDoc->createElement('colh');
    
    $prdh = $this->m_oXmlDoc->createElement('prdh', 'Product');
    $colh->appendChild($prdh);
    
    $quantityh = $this->m_oXmlDoc->createElement('quantityh', 'Item');
    $colh->appendChild($quantityh);
    
    $priceh = $this->m_oXmlDoc->createElement('priceh', 'Price');
    $colh->appendChild($priceh);
    
    $packageh = $this->m_oXmlDoc->createElement('packageh', 'Package Size');
    $colh->appendChild($packageh); 
    
    if ($bIncludeOrders)
    {
      foreach($this->m_aOrders as $order)
      {
        $memh = $this->m_oXmlDoc->createElement('memh', $order["sName"]);
        $colh->appendChild($memh);
      }
    }
    
    $totalh = $this->m_oXmlDoc->createElement('totalh', 'Total Quantity');
    $colh->appendChild($totalh);
    
    if ($this->m_nProducerID > 0)
    {
      $totalph = $this->m_oXmlDoc->createElement('totalh', 'Total Sum');
      $colh->appendChild($totalph);
    }
    
    $sheet->appendChild($colh);
  }
  
  protected function BuildSheetBodyXML($bIncludeOrders,$bProducerPrice, &$sheet)
  {
   foreach($this->m_aProducts as $this->m_aCurrentProduct)
    {
      $row = $this->m_oXmlDoc->createElement('row');
      
     /* for some reason this code doesn't work here
        $oProductPackage = new ProductPackage($this->m_aCurrentProduct["nItems"], $this->m_aCurrentProduct["fItemQuantity"], 
        $this->m_aCurrentProduct["sItemUnitAbbrev"], $this->m_aCurrentProduct["fUnitInterval"], $this->m_aCurrentProduct["sUnitAbbrev"], 
        $this->m_aCurrentProduct["fPackageSize"], $this->m_aCurrentProduct["fQuantity"]);*/
      
      $prd = $this->m_oXmlDoc->createElement('prd', $this->m_aCurrentProduct["sProduct"]);
      $row->appendChild($prd);
            
      $quantity = $this->m_oXmlDoc->createElement('quantity', $this->m_aCurrentProduct["fQuantity"] . ' ' . $this->m_aCurrentProduct["sUnitAbbrev"]);
      $row->appendChild($quantity);
      
      if ($bProducerPrice)
        $price = $this->m_oXmlDoc->createElement('price', $this->m_aCurrentProduct["mProducerPrice"]);
      else
        $price = $this->m_oXmlDoc->createElement('price', $this->m_aCurrentProduct["mCoopPrice"]);
      $row->appendChild($price);
      
      $sPackageSize = '';
      if ($this->m_aCurrentProduct["fPackageSize"] != NULL && $this->m_aCurrentProduct["fPackageSize"] != $this->m_aCurrentProduct["fQuantity"])
        $sPackageSize = $this->m_aCurrentProduct["fPackageSize"] . ' ' . $this->m_aCurrentProduct["sUnitAbbrev"];
      
      $package = $this->m_oXmlDoc->createElement('package', $sPackageSize);
      $row->appendChild($package);
            
      if ($bIncludeOrders)
      {
        while($this->m_aCurrentItem)
        {
          if ($this->m_aCurrentItem["ProductKeyID"] != $this->m_aCurrentProduct["ProductKeyID"])
            break; //moving to next product so exit loop for this one
          $mem = NULL;
          if ($this->m_aCurrentItem["fQuantity"] != NULL)
            $mem = $this->m_oXmlDoc->createElement('mem', $this->m_aCurrentItem["fQuantity"]);
          else
            $mem = $this->m_oXmlDoc->createElement('mem');
          $row->appendChild($mem);

          $this->m_aCurrentItem = $this->fetch();
        }
      }
      
      $totalb = $this->m_oXmlDoc->createElement('totalb', $this->m_aCurrentProduct["fTotalCoopOrder"]);
      $row->appendChild($totalb);
      
      if ($this->m_nProducerID > 0)
      {
        $totalpr = $this->m_oXmlDoc->createElement('total', $this->m_aCurrentProduct["mProducerTotal"]);
        $row->appendChild($totalpr);
      }
      
      $sheet->appendChild($row);
    } 
  }
  
  protected function BuildSheetSummaryRowXML($bIncludeOrders, $sLabel, $mSummary, &$sheet)
  {
    $mTotal = 0;
    $mTotalFee = 0;
    
    //summary row
    $sum = $this->m_oXmlDoc->createElement('sum');
    $sumlabel = $this->m_oXmlDoc->createElement('sumlabel', $sLabel);
    $sum->appendChild($sumlabel);
    
    //fee row
    $fee = $this->m_oXmlDoc->createElement('sum');
    
    if ($bIncludeOrders)
    {
      foreach($this->m_aOrders as $order)
      {
        if ($order["mCoopFee"] != NULL)
        {
          $mTotalFee += $order["mCoopFee"];
          $feemem = $this->m_oXmlDoc->createElement('summem', $order["mCoopFee"]);
          $fee->appendChild($feemem);
        }
        $mTotal += $order["OrderCoopTotal"];
        $summem = $this->m_oXmlDoc->createElement('summem', $order["OrderCoopTotal"]);
        $sum->appendChild($summem);
      }
      
      if ($mSummary == NULL)
        $mSummary = $mTotal;
      
      //has fee?
      if ($fee->hasChildNodes())
      {
        $feetotal = $this->m_oXmlDoc->createElement('sumtotal', $mTotalFee);
        $fee->appendChild($feetotal);
      }
    }
    
    if ($mSummary != NULL)
    {
      $sumtotal = $this->m_oXmlDoc->createElement('sumtotal', $mSummary);
      $sum->appendChild($sumtotal);
    }
    
    if ($fee->hasChildNodes())
    {
      //add fee row
      $feelabel = $this->m_oXmlDoc->createElement('sumlabel', 'Coop Fee');
      $fee->appendChild($feelabel);
      $sheet->appendChild($fee);
    }
    
    $sheet->appendChild($sum);    
  }
  
  protected function QueryCoopOrderProducts()
  {    
   $sSQL =   " SELECT COPRD.ProductKeyID, PRD.fQuantity, PRD.nItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, " .
             " COPRD.fTotalCoopOrder, COPRD.mProducerPrice, COPRD.mCoopPrice, COPRD.mProducerTotal, " .
             $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
             "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
             "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .
             " FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " . 
             " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
             " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
             $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
             $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
             $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
             " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID];
    if ($this->m_nProducerID > 0)
      $sSQL .= " AND PRD.ProducerKeyID = " . $this->m_nProducerID;
    $sSQL .= " AND (COPRD.fTotalCoopOrder > 0 OR COPRD.nJoinedStatus > 0) " .
             " ORDER BY PRD.nSortOrder, COPRD.ProductKeyID; ";
    $this->RunSQL( $sSQL );
    $this->m_aProducts = $this->fetchAll();
  }
  
  protected function QueryCoopOrderPickupLocationProducts()
  {    
   $sSQL =   " SELECT COPLPRD.ProductKeyID, PRD.fQuantity, PRD.nItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, " .
             " COPLPRD.fTotalCoopOrder, COPRD.mProducerPrice, COPRD.mCoopPrice, COPLPRD.mProducerTotal, " .
             $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
             "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
             "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .
             " FROM T_CoopOrderPickupLocationProduct COPLPRD INNER JOIN T_CoopOrderProduct COPRD " . 
             " ON COPLPRD.CoopOrderKeyID = COPRD.CoopOrderKeyID " .
             " AND COPLPRD.ProductKeyID = COPRD.ProductKeyID " .
             " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " . 
             " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
             " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
             $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
             $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
             $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
             " WHERE COPLPRD.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID] .
             " AND COPLPRD.PickupLocationKeyID = " . $this->m_nPickupLocationID;
    if ($this->m_nProducerID > 0)
      $sSQL .= " AND PRD.ProducerKeyID = " . $this->m_nProducerID;
    $sSQL .= " AND (COPLPRD.fTotalCoopOrder > 0 OR COPRD.nJoinedStatus > 0) " .
             " ORDER BY PRD.nSortOrder, COPRD.ProductKeyID; ";
    $this->RunSQL( $sSQL );
    $this->m_aProducts = $this->fetchAll();
  }
  
  protected function QueryCoopOrderPickupLocationProducers()
  {
    $sSQL =   " SELECT COP.ProducerKeyID, IfNull(COPLP.mProducerTotal,0) mProducerTotal " .
          " FROM T_CoopOrderProducer COP LEFT JOIN T_CoopOrderPickupLocationProducer COPLP " .
          " ON COP.CoopOrderKeyID = COPLP.CoopOrderKeyID AND COP.ProducerKeyID = COPLP.ProducerKeyID " .
          " AND COPLP.PickupLocationKeyID = " . $this->m_nPickupLocationID .
          " WHERE COP.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID] . ";";

    $this->RunSQL( $sSQL );
    
    $rec = $this->fetch();
    
    while ($rec)
    {
      $this->m_aData[self::PROPERTY_PRODUCERS][$rec["ProducerKeyID"]][CoopOrderProducer::PROPERTY_PRODUCER_TOTAL] = $rec["mProducerTotal"]; 
      
      $rec = $this->fetch();
    }
  }
  
  protected function QueryCoopOrderProductItems()
  {
   $sSQL =   " SELECT COPRD.ProductKeyID, O.OrderID, OI.fQuantity "  .
             " FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " .
             " INNER JOIN T_Order O ON O.CoopOrderKeyID = COPRD.CoopOrderKeyID ";
    if ($this->m_nPickupLocationID > 0)      
    {
      $sSQL .=  " INNER JOIN T_CoopOrderPickupLocationProduct COPLPRD ON COPLPRD.CoopOrderKeyID = COPRD.CoopOrderKeyID " .
             " AND COPLPRD.ProductKeyID = COPRD.ProductKeyID AND COPLPRD.PickupLocationKeyID = O.PickupLocationKeyID " .
             " AND O.PickupLocationKeyID = " . $this->m_nPickupLocationID;
    }
    $sSQL .=  " LEFT JOIN T_OrderItem OI ON OI.OrderID = O.OrderID AND OI.ProductKeyID = COPRD.ProductKeyID " . 
              " WHERE O.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID] . 
                " AND O.mCoopTotal > 0 ";
   if ($this->m_nProducerID > 0)
      $sSQL .= " AND PRD.ProducerKeyID = " . $this->m_nProducerID; 
   $sSQL .=  " AND (COPRD.nJoinedStatus > 0 OR ";
   if ($this->m_nPickupLocationID > 0)
      $sSQL .=  " COPLPRD.fTotalCoopOrder > 0 ) ";
   else
      $sSQL .=  " COPRD.fTotalCoopOrder > 0 ) ";
    $sSQL .=  " ORDER BY PRD.nSortOrder, PRD.ProductKeyID, O.dCreated, O.OrderID; ";
    $this->RunSQL( $sSQL );
    $this->m_aCurrentItem = $this->fetch();
  }
  
  protected function QueryOrders()
  {
    $sSQL =     " SELECT O.OrderID, O.MemberID, M.sName, O.PickupLocationKeyID, O.mCoopFee, " . 
                " O.mCoopTotal, (IfNull(O.mCoopFee,0) + O.mCoopTotal) as OrderCoopTotal " .
                " FROM T_Order O " .
                " INNER JOIN T_Member M ON O.MemberID = M.MemberID " .
                " WHERE O.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID] . 
                " AND O.mCoopTotal > 0 ";
    if ($this->m_nPickupLocationID > 0)
      $sSQL .=  " AND O.PickupLocationKeyID = " . $this->m_nPickupLocationID;     
    $sSQL .=    " ORDER BY O.dCreated, O.OrderID; "; //must have unequivocal order to match orders with items (hence the use of ids)
    $this->RunSQL( $sSQL );
    $this->m_aOrders = $this->fetchAll();
  }
  
  //source: http://snipplr.com/view/52144/
  public static function remove_filename_special_char($string) {
    $ts = array("/\~/", "/\`/", "/\@/", "/\#/", "/\\$/", "/\%/", "/\^/", "/\&/", "/\*/", "/\(/", "/\)/", "/\:/", "/\:/", "/\;/", "/\</", "/\>/", "/\?/", "/\//", "/\,/", "/\{/", "/\}/", "/\[/", "/\]/", "/\|/", "/\+/", "/\=/", "/\!/", "/\'/" );
    $string = preg_replace($ts,'', $string);
    return $string;
  }

}

?>
