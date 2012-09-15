<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//handles coord/product.php page operations
class Product extends SQLBase {
  
  const PROPERTY_PRODUCT_ID = "ID";
  const PROPERTY_PRODUCT_NAMES = "ProductNames";
  const PROPERTY_PRODUCT_NAME = "ProductName";
  const PROPERTY_PRODUCER_ID = "ProducerID";
  const PROPERTY_PRODUCER_NAME = "ProducerName";
  const PROPERTY_SPEC_STRING_ID = "SpecStringID";
  const PROPERTY_SPEC_STRINGS = "SpecStrings";
  const PROPERTY_UNIT_ID = "UnitID";
  const PROPERTY_UNIT_INTERVAL = "UnitInterval";
  const PROPERTY_MAX_USER_ORDER = "MaxUserOrder";
  const PROPERTY_IS_DISABLED = "IsDisabled";
  const PROPERTY_PRODUCER_PRICE = "ProducerPrice";
  const PROPERTY_COOP_PRICE = "CoopPrice";
  const PROPERTY_BURDEN = "Burden";
  const PROPERTY_IMAGE1_FILE = "Image1File";
  const PROPERTY_IMAGE2_FILE = "Image2File";
  const PROPERTY_IMAGE1_FILE_NAME = "Image1FileName";
  const PROPERTY_IMAGE2_FILE_NAME = "Image2FileName";
  const PROPERTY_IMAGE1_REMOVE = "Image1Remove";
  const PROPERTY_IMAGE2_REMOVE = "Image2Remove";
  
  const PROPERTY_QUANTITY = "Quantity";
  const PROPERTY_PACKAGE_SIZE = "PackageSize";
  const PROPERTY_ITEM_QUANTITY = "ItemQuantity";
  const PROPERTY_ITEMS_IN_PACKAGE = "Items";
  const PROPERTY_ITEM_UNIT_ID = "ItemUnitID";
  const PROPERTY_SORT_ORDER = "SortOrder";
  const PROPERTY_JOIN_TO_PRODUCT_ID = "JoinToProductID";
  
  const PERMISSION_UPLOAD = 10;
  
  const DEFAULT_BURDEN = 1;
  const DEFAULT_UNIT_INTERVAL = 1;
  const DEFAULT_ITEMS_IN_PACKAGE = 1;
  const DEFAULT_QUANTITY = 1;
  
  protected $m_arrImageMimeTypes = NULL;
  
  public function __construct()
  {
    $this->m_aDefaultData = array( self::PROPERTY_PRODUCT_ID => 0,
                            self::PROPERTY_PRODUCT_NAMES => NULL,
                            self::PROPERTY_PRODUCER_ID => 0,
                            self::PROPERTY_PRODUCER_NAME => NULL,
                            self::PROPERTY_SPEC_STRING_ID => 0,
                            self::PROPERTY_SPEC_STRINGS => NULL,
                            self::PROPERTY_UNIT_ID => Consts::UNIT_ITEMS,
                            self::PROPERTY_UNIT_INTERVAL => self::DEFAULT_UNIT_INTERVAL,
                            self::PROPERTY_MAX_USER_ORDER => NULL,
                            self::PROPERTY_IS_DISABLED => FALSE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_PRODUCER_PRICE => NULL,
                            self::PROPERTY_COOP_PRICE => NULL,
                            self::PROPERTY_BURDEN => self::DEFAULT_BURDEN,
                            self::PROPERTY_QUANTITY => self::DEFAULT_QUANTITY,                    
                            self::PROPERTY_ITEMS_IN_PACKAGE => self::DEFAULT_ITEMS_IN_PACKAGE,
                            self::PROPERTY_ITEM_QUANTITY => NULL,
                            self::PROPERTY_ITEM_UNIT_ID => NULL,
                            self::PROPERTY_PACKAGE_SIZE => NULL,
                            self::PROPERTY_SORT_ORDER => NULL,
                            self::PROPERTY_JOIN_TO_PRODUCT_ID => NULL,
                            self::PROPERTY_IMAGE1_FILE => NULL,
                            self::PROPERTY_IMAGE2_FILE => NULL,
                            self::PROPERTY_IMAGE1_FILE_NAME => NULL,
                            self::PROPERTY_IMAGE2_FILE_NAME => NULL,
                            self::PROPERTY_IMAGE1_REMOVE => FALSE,
                            self::PROPERTY_IMAGE2_REMOVE => FALSE
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData; 
  }
  
  public function __get( $name ) {
    global $g_sLangDir;
    switch ($name)
    {
      case self::PROPERTY_PRODUCT_NAME:
        if ($g_sLangDir == '')
          return $this->m_aData[self::PROPERTY_PRODUCT_NAMES][0];
        else
          return $this->m_aData[self::PROPERTY_PRODUCT_NAMES][$g_sLangDir];
      default:
        return parent::__get($name);
    }
  }
  
  public function CheckAccess()
  {
     if ($this->HasPermission(self::PERMISSION_EDIT))
      $bEdit = TRUE;
     else
      $bEdit = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
     
     if ($this->HasPermission(self::PERMISSION_VIEW))
      $bView = TRUE;
     else
      $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_VIEW, 
       Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
     
     return ($bEdit || $bView);
  }
  
  public function LoadRecord($nID)
  {
    global $g_oMemberSession;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //permission check
    if ( !$this->CheckAccess() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $nID <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
     
    $this->m_aData[self::PROPERTY_PRODUCT_ID] = $nID;
    
    $sSQL =   " SELECT PRD.ProductKeyID, PRD.ProducerKeyID, PRD.JoinToProductKeyID, PRD.UnitKeyID, PRD.fUnitInterval, PRD.fMaxUserOrder, PRD.mProducerPrice, PRD.mCoopPrice," .
              " PRD.fQuantity, PRD.nItems, PRD.ItemUnitKeyID, PRD.fItemQuantity, PRD.nSortOrder, PRD.fPackageSize, " .
              " PRD.fBurden, PRD.sImage1FileName, PRD.sImage2FileName, PRD.SpecStringKeyID, PRD.bDisabled, P.CoordinatingGroupID " .
              " FROM T_Product PRD INNER JOIN T_Producer P ON P.ProducerKeyID = PRD.ProducerKeyID " . 
              " WHERE PRD.ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] . ';';

    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!is_array($rec) || count($rec) == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = 0;
    if ($rec["CoordinatingGroupID"])
        $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];

    //coordinating group permission check
    if ( !$this->AddPermissionBridgeGroupID(self::PERMISSION_EDIT, FALSE) &&
         !$this->AddPermissionBridgeGroupID(self::PERMISSION_VIEW, FALSE) )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_PRODUCER_ID] = $rec["ProducerKeyID"];
    $this->m_aData[self::PROPERTY_UNIT_ID] = $rec["UnitKeyID"];
    $this->m_aData[self::PROPERTY_UNIT_INTERVAL] = $rec["fUnitInterval"];    
    $this->m_aData[self::PROPERTY_MAX_USER_ORDER] = $rec["fMaxUserOrder"];
    $this->m_aData[self::PROPERTY_PRODUCER_PRICE] = $rec["mProducerPrice"];
    $this->m_aData[self::PROPERTY_COOP_PRICE] = $rec["mCoopPrice"];
    $this->m_aData[self::PROPERTY_BURDEN] = $rec["fBurden"];
    $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME] = $rec["sImage1FileName"];
    $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME] = $rec["sImage2FileName"];
    $this->m_aData[self::PROPERTY_SPEC_STRING_ID] = $rec["SpecStringKeyID"];
    $this->m_aData[self::PROPERTY_IS_DISABLED] = $rec["bDisabled"];
    
    $this->m_aData[self::PROPERTY_QUANTITY]= $rec["fQuantity"];
    $this->m_aData[self::PROPERTY_PACKAGE_SIZE]= $rec["fPackageSize"];
    $this->m_aData[self::PROPERTY_ITEMS_IN_PACKAGE]= $rec["nItems"];
    $this->m_aData[self::PROPERTY_ITEM_QUANTITY]= $rec["fItemQuantity"];
    $this->m_aData[self::PROPERTY_ITEM_UNIT_ID]= $rec["ItemUnitKeyID"];
    $this->m_aData[self::PROPERTY_SORT_ORDER]= $rec["nSortOrder"];
    
    $this->m_aData[self::PROPERTY_JOIN_TO_PRODUCT_ID] = $rec["JoinToProductKeyID"];
    
        
    $this->m_aData[self::PROPERTY_PRODUCT_NAMES] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_PRODUCT_ID]);
    $this->m_aData[self::PROPERTY_SPEC_STRINGS] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_SPEC_STRING_ID]);
    
    $this->m_aOriginalData = $this->m_aData;
        
    return TRUE;
  }
  
  
  
  public function Add()
  {
      global $g_oMemberSession;
      global $g_sLangDir;

      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

      //general permission check
      if ( !$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) )
      {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          return FALSE;
      }
      
      if (!$this->Validate())
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
        return FALSE;
      }
      
      try
      {
        $this->m_bUseClassConnection = TRUE;
        $this->BeginTransaction();

        //create new string key for the new product
        $nKeyID = $this->NewKey();

        //insert product names     
        $this->InsertStrings($this->m_aData[self::PROPERTY_PRODUCT_NAMES], $nKeyID);
        //insert spec strings
        $this->m_aData[self::PROPERTY_SPEC_STRING_ID] = $this->NewKey();
        $this->InsertStrings($this->m_aData[self::PROPERTY_SPEC_STRINGS], $this->m_aData[self::PROPERTY_SPEC_STRING_ID]);


        //insert the record
        $sSQL =  " INSERT INTO T_Product( ProductKeyID, ProducerKeyID, SpecStringKeyID, UnitKeyID, mProducerPrice, mCoopPrice, " . 
                " fBurden, bDisabled, nItems, fQuantity " .
                $this->ConcatColIfNotNull(self::PROPERTY_ITEM_QUANTITY, "fItemQuantity") .
                $this->ConcatColIfNotNull(self::PROPERTY_ITEM_UNIT_ID, "ItemUnitKeyID") .
                $this->ConcatColIfNotNull(self::PROPERTY_MAX_USER_ORDER, "fMaxUserOrder") .
                $this->ConcatColIfNotNull(self::PROPERTY_SORT_ORDER, "nSortOrder") .                
                $this->ConcatColIfNotNull(self::PROPERTY_PACKAGE_SIZE, "fPackageSize") .                
                $this->ConcatColIfNotNull(self::PROPERTY_UNIT_INTERVAL, "fUnitInterval") .
                $this->ConcatColIfNotValue(self::PROPERTY_JOIN_TO_PRODUCT_ID, "JoinToProductKeyID", 0) .
                " ) VALUES (" . $nKeyID . "," . $this->m_aData[self::PROPERTY_PRODUCER_ID] . "," . $this->m_aData[self::PROPERTY_SPEC_STRING_ID] . 
                "," . $this->m_aData[self::PROPERTY_UNIT_ID] . "," . $this->m_aData[self::PROPERTY_PRODUCER_PRICE] .
                "," . $this->m_aData[self::PROPERTY_COOP_PRICE]
                . "," . $this->m_aData[self::PROPERTY_BURDEN]
                . "," . intval($this->m_aData[self::PROPERTY_IS_DISABLED])
                . "," . $this->m_aData[self::PROPERTY_ITEMS_IN_PACKAGE]
                . "," . $this->m_aData[self::PROPERTY_QUANTITY]
                . $this->ConcatValIfNotNull(self::PROPERTY_ITEM_QUANTITY)
                . $this->ConcatValIfNotNull(self::PROPERTY_ITEM_UNIT_ID) 
                . $this->ConcatValIfNotNull(self::PROPERTY_MAX_USER_ORDER)
                . $this->ConcatValIfNotNull(self::PROPERTY_SORT_ORDER)
                . $this->ConcatValIfNotNull(self::PROPERTY_PACKAGE_SIZE)
                . $this->ConcatValIfNotNull(self::PROPERTY_UNIT_INTERVAL) 
                . $this->ConcatValIfNotValue(self::PROPERTY_JOIN_TO_PRODUCT_ID, 0)
                . " );";

        $this->RunSQL($sSQL);

        $this->m_aData[self::PROPERTY_PRODUCT_ID] = $nKeyID;

        $sSQL = '';
        $arrArgs = array();
        //attempt to upload files, if exist
        if ($this->m_aData[self::PROPERTY_IMAGE1_FILE] != NULL)
        {
          if ($this->UploadImage($this->m_aData[self::PROPERTY_IMAGE1_FILE], 1, self::PROPERTY_IMAGE1_FILE_NAME))
          {

            $sSQL .= " sImage1FileName = ? ";
            $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME];
          }
        }
        //perhaps setting file name only?
        else if ($this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME] != NULL)
        {
          $sSQL .= " sImage1FileName = ? ";
          $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME];
        }

        if ($this->m_aData[self::PROPERTY_IMAGE2_FILE] != NULL)
        {
          if ($this->UploadImage($this->m_aData[self::PROPERTY_IMAGE2_FILE], 2, self::PROPERTY_IMAGE2_FILE_NAME))
          {
            if ($sSQL != '')
              $sSQL .= ", ";
            $sSQL .= " sImage2FileName = ? ";
            $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME];
          }
        }
        //perhaps setting file name only?
        else if ($this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME] != NULL)
        {
          if ($sSQL != '')
            $sSQL .= ", ";
          $sSQL .= " sImage2FileName = ? ";
          $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME];
        }
        
        if ($sSQL != '')
        {
          $sSQL = "UPDATE T_Product SET " . $sSQL . " WHERE ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] . ";";
          $this->RunSQLWithParams($sSQL, $arrArgs);
        }
        
        $this->CommitTransaction();
      }
      catch(Exception $e)
      {
        $this->RollbackTransaction();
        $this->CloseConnection();
        $this->m_bUseClassConnection = FALSE;
        throw $e;
      }
      $this->CloseConnection();
      $this->m_bUseClassConnection = FALSE;

      $this->m_aOriginalData = $this->m_aData;

      return TRUE;
  }

  
  
  public function Edit()
  {
    global $g_oMemberSession;
    global $g_sLangDir;

    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID];

    //general permission check
    if ( !$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE) )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_PRODUCT_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //copy values lost after postback
    $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME] = $this->m_aOriginalData[self::PROPERTY_IMAGE1_FILE_NAME];
    $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME] = $this->m_aOriginalData[self::PROPERTY_IMAGE2_FILE_NAME];

    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    $arrArgs = array(
        $this->m_aData[self::PROPERTY_UNIT_INTERVAL],
        $this->m_aData[self::PROPERTY_MAX_USER_ORDER],
        $this->m_aData[self::PROPERTY_ITEM_QUANTITY],
        $this->m_aData[self::PROPERTY_ITEM_UNIT_ID],
        $this->m_aData[self::PROPERTY_SORT_ORDER],
        $this->m_aData[self::PROPERTY_PACKAGE_SIZE]
            );
    
     try
      {
        $this->BeginTransaction();

        //attempt to upload files, if exist
        $sSetImagesSQL = '';
        if ($this->m_aData[self::PROPERTY_IMAGE1_REMOVE])
        {
          $sSetImagesSQL .= " ,sImage1FileName = NULL ";
          $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME] = NULL;
        }
        else if ($this->m_aData[self::PROPERTY_IMAGE1_FILE] != NULL)
        {
          if ($this->UploadImage($this->m_aData[self::PROPERTY_IMAGE1_FILE], 1, self::PROPERTY_IMAGE1_FILE_NAME))
          {
            $sSetImagesSQL .= " ,sImage1FileName = ? ";
            $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME];
          }
        }
        //perhaps setting file name only?
        else if ($this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME] != NULL && 
                $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME] != $this->m_aOriginalData[self::PROPERTY_IMAGE1_FILE_NAME])
        {
          $sSetImagesSQL .= " ,sImage1FileName = ? ";
            $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE1_FILE_NAME];
        }

        if ($this->m_aData[self::PROPERTY_IMAGE2_REMOVE])
        {
          $sSetImagesSQL .= " ,sImage2FileName = NULL ";
          $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME] = NULL;
        }
        else if ($this->m_aData[self::PROPERTY_IMAGE2_FILE] != NULL)
        {
          if ($this->UploadImage($this->m_aData[self::PROPERTY_IMAGE2_FILE], 2, self::PROPERTY_IMAGE2_FILE_NAME))
          {
            $sSetImagesSQL .= " ,sImage2FileName = ? ";
            $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME];
          }
        }
        //perhaps setting file name only?
        else if ($this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME] != NULL && 
                $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME] != $this->m_aOriginalData[self::PROPERTY_IMAGE2_FILE_NAME]
                )
        {
          $sSetImagesSQL .= " ,sImage2FileName = ? ";
          $arrArgs[] = $this->m_aData[self::PROPERTY_IMAGE2_FILE_NAME];
        }

        $sSQL =   " UPDATE T_Product " .
                  " SET ProducerKeyID = " . $this->m_aData[self::PROPERTY_PRODUCER_ID] .
                  " , UnitKeyID = " . $this->m_aData[self::PROPERTY_UNIT_ID] .
                  " , fUnitInterval = ? , fMaxUserOrder = ?, fItemQuantity = ?, ItemUnitKeyID = ?, nSortOrder = ?, fPackageSize = ? " .
                  " , mProducerPrice = " . $this->m_aData[self::PROPERTY_PRODUCER_PRICE] .
                  " , mCoopPrice = " . $this->m_aData[self::PROPERTY_COOP_PRICE] .
                  " , fBurden = " . $this->m_aData[self::PROPERTY_BURDEN] .
                  " , fQuantity = " . $this->m_aData[self::PROPERTY_QUANTITY] .
                  " , nItems = " . $this->m_aData[self::PROPERTY_ITEMS_IN_PACKAGE] .
                  " , bDisabled = " . intval($this->m_aData[self::PROPERTY_IS_DISABLED]) . $sSetImagesSQL;
        if ($this->m_aData[self::PROPERTY_JOIN_TO_PRODUCT_ID] > 0)
          $sSQL .= " , JoinToProductKeyID =  " . $this->m_aData[self::PROPERTY_JOIN_TO_PRODUCT_ID];

        $sSQL .=  " WHERE ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] . ';';  

        $this->RunSQLWithParams( $sSQL, $arrArgs );

        $this->UpdateStrings(self::PROPERTY_PRODUCT_NAMES, $this->m_aData[self::PROPERTY_PRODUCT_ID]);

        $this->UpdateStrings(self::PROPERTY_SPEC_STRINGS, $this->m_aOriginalData[self::PROPERTY_SPEC_STRING_ID]);

        $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    $this->m_aData[self::PROPERTY_SPEC_STRING_ID] = $this->m_aOriginalData[self::PROPERTY_SPEC_STRING_ID];
    
    $this->m_aOriginalData = $this->m_aData;
        
    return TRUE;
  }
  
  public function Delete()
  {
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //general permission check
    if ( !$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE) )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_PRODUCT_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();
    
      $sSQL =   " DELETE FROM T_Product " .
                 " WHERE ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] . ';';

      $this->RunSQL($sSQL);

      $this->DeleteKey($this->m_aData[self::PROPERTY_PRODUCT_ID]); //deletes all associated strings
      $this->DeleteKey($this->m_aOriginalData[self::PROPERTY_SPEC_STRING_ID]); //deletes all associated strings
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
    
    return TRUE;
  }
  
  public function CheckImageUploadsPermission()
  {
    if ($this->HasPermission(self::PERMISSION_UPLOAD))
       return TRUE;
    
    return $this->AddPermissionBridge(self::PERMISSION_UPLOAD, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_UPLOAD_FILE, 
       Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
  }
  
  protected function UploadImage($sFileCtl, $nIndex, $sProperty)
  {   
    global $g_oError;
    global $_FILES;
    if (!$this->CheckImageUploadsPermission())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if (!isset($_FILES[$sFileCtl]['name']) || empty($_FILES[$sFileCtl]['name'])) //no file was chosen
      return FALSE;
    
    $sUniqueFileName = sprintf(PRODUCT_IMAGE_UPLOAD_FILE_NAME_TEMPLATE, $this->m_aData[self::PROPERTY_PRODUCT_ID], $nIndex);
    
    $oUploader = new FileUploader($_FILES[$sFileCtl], $sUniqueFileName, PRODUCT_IMAGE_MAX_FILE_SIZE, $this->GetImageMimeTypes() );
    if ($this->m_aData[$sProperty] != NULL)
      $oUploader->ResultFileName = $this->m_aData[$sProperty];
    
    $nResponse = $oUploader->Upload();
    if ($nResponse == FileUploader::RESPONSE_SUCCESS)
    {
      $this->m_aData[$sProperty] = $oUploader->ResultFileName;
      return TRUE;
    }
    else
    {
      $sMessage = sprintf('<!$ERR_COULD_NOT_UPLOAD_FILE$!>', $oUploader->OriginalFileName);
      //analize errors
      switch($nResponse)
      {
        case FileUploader::RESPONSE_FILE_OBJECT_NOT_SET:
          $sMessage .= ' <!$ERR_UPLOAD_REASON_POSSIBLE_BUG$!>';
          break;
        case FileUploader::RESPONSE_MAX_FILE_SIZE_EXCEEDED:
          $sMessage .= sprintf(' <!$ERR_UPLOAD_REASON_MAX_SIZE_EXCEEDED$!>', (PRODUCT_IMAGE_MAX_FILE_SIZE/1024)); //1024 bytes = 1 KB
          break;
        case FileUploader::RESPONSE_FILE_TYPE_UNSUPPORTED:
          $sMessage .= ' <!$ERR_UPLOAD_REASON_FILE_TYPE_UNSUPPORTED$!>';
          break;
        case FileUploader::RESPONSE_CANT_WRITE:
          $sMessage .= ' <!$ERR_UPLOAD_REASON_PERMISSION$!>';
          break;
        case FileUploader::RESPONSE_NO_TMP_DIR:
          $sMessage .= ' <!$ERR_UPLOAD_REASON_NO_TMP_DIR$!>';
          break;
        case FileUploader::RESPONSE_UPLOADS_DIR_WRITE_FAILED:
          $sMessage .= ' <!$ERR_UPLOAD_REASON_UPLOADS_DIR_WRITE_FAILED$!>';
          break;
        case FileUploader::RESPONSE_FILE_OBJECT_ERROR:
          $sMessage .= sprintf(' <!$ERR_UPLOAD_REASON_OTHER_CODES$!>', implode(',', $oFile['error']));
          break;
      }
      
      $g_oError->AddError($sMessage);
    }
    
    return FALSE;
  }
  
  protected function GetImageMimeTypes()
  {
    if ($this->m_arrImageMimeTypes == NULL)    
      $this->m_arrImageMimeTypes = explode(';', PRODUCT_IMAGE_MIME_TYPES);
    
    return $this->m_arrImageMimeTypes;
  }
  
  
  public function Validate()
  {
    global $g_oError;
    global $g_sLangDir;
    
    $bValid = TRUE;
    
    if (!$this->ValidateRequiredNames(self::PROPERTY_PRODUCT_NAMES, '<!$FIELD_PRODUCT_NAME$!>'))
      $bValid = FALSE;
    
    if ($this->m_aData[self::PROPERTY_PRODUCER_ID] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_SELECT_REQUIRED$!>', '<!$FIELD_PRODUCER$!>'));
      $bValid = FALSE;
    }
    
   /* if (!$this->ValidateRequiredNames(self::PROPERTY_SPEC_STRINGS, '<!$FIELD_PRODUCT_SPEC$!>'))
      $bValid = FALSE;*/
    
    if ($this->m_aData[self::PROPERTY_UNIT_ID] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_SELECT_REQUIRED$!>', '<!$FIELD_UNIT$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_QUANTITY] === NULL)
    {
      $g_oError->AddError(sprintf('<!$FIELD_REQUIRED$!>', '<!$FIELD_QUANTITY$!>'));
      $bValid = FALSE;
    }
    else if ($this->m_aData[self::PROPERTY_QUANTITY] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE_OR_ZERO$!>', '<!$FIELD_QUANTITY$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_PACKAGE_SIZE] != NULL && $this->m_aData[self::PROPERTY_PACKAGE_SIZE] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE_OR_ZERO$!>', '<!$FIELD_PACKAGE_SIZE$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_ITEMS_IN_PACKAGE] === NULL)
    {
      $g_oError->AddError(sprintf('<!$FIELD_REQUIRED$!>', '<!$FIELD_PRODUCT_ITEMS_IN_PACKAGE$!>'));
      $bValid = FALSE;
    }
    else if ($this->m_aData[self::PROPERTY_ITEMS_IN_PACKAGE] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE_OR_ZERO$!>', '<!$FIELD_PRODUCT_ITEMS_IN_PACKAGE$!>'));
      $bValid = FALSE;
    }
    
    //allow null
    if ($this->m_aData[self::PROPERTY_ITEM_QUANTITY] !== NULL && $this->m_aData[self::PROPERTY_ITEM_QUANTITY] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE_OR_ZERO$!>', '<!$FIELD_PRODUCT_ITEM_QUANTITY$!>'));
      $bValid = FALSE;
    }     
    
    if ($this->m_aData[self::PROPERTY_ITEM_QUANTITY] != NULL)
    {
      if ( !is_numeric($this->m_aData[self::PROPERTY_ITEM_QUANTITY]))
      {
        $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_PRODUCT_ITEM_QUANTITY$!>'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_ITEM_UNIT_ID] == NULL)
      {
        $g_oError->AddError( sprintf('<!$FIELD1_MUST_BE_SET_WHEN_FIELD2_IS_SET$!>', '<!$FIELD_ITEM_UNIT$!>',
                '<!$FIELD_PRODUCT_ITEM_QUANTITY$!>'
                ));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_ITEM_UNIT_ID] != NULL && $this->m_aData[self::PROPERTY_ITEM_QUANTITY] == NULL)
    {
      $g_oError->AddError( sprintf('<!$FIELD1_MUST_BE_SET_WHEN_FIELD2_IS_SET$!>',
                '<!$FIELD_PRODUCT_ITEM_QUANTITY$!>', '<!$FIELD_ITEM_UNIT$!>'
                ));
        $bValid = FALSE;
    }
    
    //allow null
    if ($this->m_aData[self::PROPERTY_UNIT_INTERVAL] !== NULL && $this->m_aData[self::PROPERTY_UNIT_INTERVAL] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE_OR_ZERO$!>', '<!$FIELD_UNIT_INTERVAL$!>'));
      $bValid = FALSE;
    }
    
    //allow null
    if ($this->m_aData[self::PROPERTY_MAX_USER_ORDER] !== NULL && $this->m_aData[self::PROPERTY_MAX_USER_ORDER] < 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE$!>', '<!$FIELD_USER_MAX_ORDER$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_PRODUCER_PRICE] === NULL)
    {
      $g_oError->AddError(sprintf('<!$FIELD_REQUIRED$!>', '<!$FIELD_PRODUCER_PRICE$!>'));
      $bValid = FALSE;
    }
    else if ($this->m_aData[self::PROPERTY_PRODUCER_PRICE] < 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE$!>', '<!$FIELD_PRODUCER_PRICE$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_COOP_PRICE] === NULL)
    {
      $g_oError->AddError(sprintf('<!$FIELD_REQUIRED$!>', '<!$FIELD_COOP_PRICE$!>'));
      $bValid = FALSE;
    }
    else if ($this->m_aData[self::PROPERTY_COOP_PRICE] < 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_NON_NEGATIVE$!>', '<!$FIELD_COOP_PRICE$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_BURDEN] === NULL)
    {
      $g_oError->AddError(sprintf('<!$FIELD_REQUIRED$!>', '<!$FIELD_BURDEN$!>'));
      $bValid = FALSE;
    }
    
    //if joining to another product, this product must be in units
    if ($this->m_aData[self::PROPERTY_JOIN_TO_PRODUCT_ID] > 0 && $this->m_aData[self::PROPERTY_UNIT_ID] != Consts::UNIT_ITEMS )
    {
      $g_oError->AddError('<!$PRODUCT_VALIDATION_UNIT_MUST_BE_IN_ITEMS_WHEN_JOINING_PRODUCTS$!>');
      $bValid = FALSE;
    }
    
    return $bValid;
  }
  
  public function GetUnits()
  {
      if (!$this->CheckAccess())
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
    
      $sSQL =  " SELECT UT.UnitKeyID, " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNITS, 'sUnit');
      $sSQL .= " FROM T_Unit UT " . $this->ConcatStringsJoin(Consts::PERMISSION_AREA_UNITS);
      $sSQL .= $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_MEASURES, Consts::PERMISSION_AREA_UNITS);
      $sSQL .= " ORDER BY UT_S.sString; ";

      $this->RunSQL( $sSQL );

      return $this->fetchAllKeyPair();
  }
  
  //Used in coord/coproduct.php to load defaults of the product when creating a new coop order product.
  //Normally this code wouldn't run, as products data will be either copied from a source coop order
  //or produced automatically when adding a new coop order producer.
  public function LoadCOProductDefaults($nID)
  {   
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //permission check: must be able to add coop order product for this specific producer
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
         Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $nID <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
     
    $this->m_aData[self::PROPERTY_PRODUCT_ID] = $nID;
    
    $sSQL =   " SELECT PRD.fMaxUserOrder, PRD.mProducerPrice, PRD.mCoopPrice, PRD.fBurden, P.CoordinatingGroupID" .
              " FROM T_Product PRD INNER JOIN T_Producer P ON P.ProducerKeyID = PRD.ProducerKeyID " . 
              " WHERE PRD.ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] . ';';

    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!is_array($rec) || count($rec) == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = 0;
    if ($rec["CoordinatingGroupID"])
        $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];

    //coordinating group permission check
    if (!$this->AddPermissionBridgeGroupID(self::PERMISSION_EDIT, FALSE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_MAX_USER_ORDER] = $rec["fMaxUserOrder"];
    $this->m_aData[self::PROPERTY_PRODUCER_PRICE] = $rec["mProducerPrice"];
    $this->m_aData[self::PROPERTY_COOP_PRICE] = $rec["mCoopPrice"];
    $this->m_aData[self::PROPERTY_BURDEN] = $rec["fBurden"];
        
    return TRUE;
  }
  
}

?>
