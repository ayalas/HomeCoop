<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//used to upload products image files
//requires write permissions for the uploads dir
class FileUploader {
  const PROPERTY_FILE = "File";
  const PROPERTY_UNIQUE_FILE_NAME = "UniqueFileName";
  const PROPERTY_MAX_FILE_SIZE = "MaxFileSize";
  const PROPERTY_ACCEPTED_FILE_TYPES = "AcceptedFileTypes";
  const PROPERTY_RESULT_FILE_NAME = "ResultFileName";
  const PROPERTY_ORIGINAL_FILE_NAME = "OriginalFileName";
    
  const RESPONSE_SUCCESS = 0;
  const RESPONSE_FILE_OBJECT_NOT_SET = 1;
  const RESPONSE_MAX_FILE_SIZE_EXCEEDED = 2;
  const RESPONSE_FILE_TYPE_UNSUPPORTED = 3;
  const RESPONSE_FILE_OBJECT_ERROR = 4;
  const RESPONSE_CANT_WRITE = 5;
  const RESPONSE_UPLOAD_FAILED = 6;
  const RESPONSE_UPLOADS_DIR_WRITE_FAILED = 7;
  const RESPONSE_NO_TMP_DIR = 8;

  protected $m_aData = NULL;
  protected $m_oFile = NULL;
  protected $m_sOriginalFileName = NULL;

  public function __construct($oFile, $sUniqueFileName,$nMaxFileSize, $arrAcceptedFileTypes)
  {
    $this->m_oFile = $oFile;
    $this->m_aData = array( self::PROPERTY_UNIQUE_FILE_NAME => $sUniqueFileName, 
        self::PROPERTY_MAX_FILE_SIZE=> $nMaxFileSize,
        self::PROPERTY_ACCEPTED_FILE_TYPES => $arrAcceptedFileTypes,
        self::PROPERTY_RESULT_FILE_NAME => NULL);
  }

  public function __get( $name ) {
      switch ($name)
      {
        case self::PROPERTY_ORIGINAL_FILE_NAME:
          return $this->m_sOriginalFileName;
        default:
          if ( array_key_exists( $name, $this->m_aData) )
            return $this->m_aData[$name];
          $trace = debug_backtrace();
          throw new Exception(
              'Undefined property via __get(): ' . $name .
              ' in class '. get_class() .', file ' . $trace[0]['file'] .
              ' on line ' . $trace[0]['line']);
          break;
      }
    }

    public function __set( $name, $value ) {        
      if ($name == self::PROPERTY_FILE)
        $this->m_oFile = $value;
      else if (array_key_exists( $name, $this->m_aData))
      {
          $this->m_aData[$name] = $value;
           return;
      }
      $trace = debug_backtrace();
      trigger_error(
          'Undefined property via __set(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line'],
          E_USER_NOTICE);
    }

    public function Upload()
    {
      if ($this->m_oFile == NULL)
        return self::RESPONSE_FILE_OBJECT_NOT_SET;

      //size validation
      if ( $this->m_oFile['size'] > $this->m_aData[self::PROPERTY_MAX_FILE_SIZE] )
        return self::RESPONSE_MAX_FILE_SIZE_EXCEEDED;

      //type validation
      $bFoundMimeType = false;
      foreach ($this->m_aData[self::PROPERTY_ACCEPTED_FILE_TYPES] as $mime_type) 
      {
          if ( $this->m_oFile['type'] == $mime_type)
          {
              $bFoundMimeType = true;
              break;
          }
      }

      if ( !$bFoundMimeType )  
        return self::RESPONSE_FILE_TYPE_UNSUPPORTED;

      //other file errors
      if ( isset($this->m_oFile['error']) )
      {
          $errcount = count( $this->m_oFile['error'] );

          for($i = 0; $i < $errcount; $i++) 
          {
              switch( $this->m_oFile['error'][$i] )
              {
                  case UPLOAD_ERR_OK:
                      break; //don't return
                  case UPLOAD_ERR_FORM_SIZE:
                      return self::RESPONSE_MAX_FILE_SIZE_EXCEEDED;
                  case UPLOAD_ERR_NO_TMP_DIR:
                    return self::RESPONSE_NO_TMP_DIR;
                  case UPLOAD_ERR_CANT_WRITE:
                    return self::RESPONSE_CANT_WRITE;
                  default:
                    return self::RESPONSE_FILE_OBJECT_ERROR;
              }
          }
      }
      
      if ( ! is_uploaded_file($this->m_oFile['tmp_name'] ) ) 
       return self::RESPONSE_UPLOAD_FAILED;
      
      $this->m_sOriginalFileName = $this->m_oFile['name'];
      //extract extention, if exists
      $sFileExtension = '';
      $nExtensionPos = strrpos( $this->m_sOriginalFileName, '.' );
      if ( $nExtensionPos > 0 )
          $sFileExtension = mb_substr($this->m_sOriginalFileName, $nExtensionPos);
      
      if ($this->m_aData[self::PROPERTY_RESULT_FILE_NAME] == NULL)
        $this->m_aData[self::PROPERTY_RESULT_FILE_NAME] = $this->m_aData[self::PROPERTY_UNIQUE_FILE_NAME] . $sFileExtension;
      
      $sCurrentPath = str_replace($_SERVER['PHP_SELF'], '', $_SERVER['SCRIPT_FILENAME']);
      
      $sPath = $this->GetUploadsDirFullPath() . $this->m_aData[self::PROPERTY_RESULT_FILE_NAME];

      //move file to uploads folder (requires write permissions for the uploads dir)
      if (!move_uploaded_file( $this->m_oFile['tmp_name'], $sPath) ) 
          return self::RESPONSE_UPLOADS_DIR_WRITE_FAILED;

      
      return self::RESPONSE_SUCCESS;
    }
    
    protected function GetUploadsDirFullPath()
    {
      global $g_sRootRelativePath;
      global $_SERVER;
      
      $nPos = strripos( $_SERVER['SCRIPT_FILENAME'], '/');
      
      if ($nPos === FALSE || $nPos == 0)
        return '';
      
      //+1: to include_once the '/'
      $sResult = mb_substr($_SERVER['SCRIPT_FILENAME'], 0, $nPos + 1) . $g_sRootRelativePath . URL_UPLOAD_DIR;
      
      return realpath($sResult) . '/';
    }
}
?>
