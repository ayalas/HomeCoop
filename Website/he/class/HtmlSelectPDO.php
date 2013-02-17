<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to html document a select element, based on a PDO recordset
class HtmlSelectPDO {
  
  const PREFIX= 'sel';
  const EMPTY_VALUE = 0;
  
  const PROPERTY_ID = "ID";
  const PROPERTY_LABEL = "Label";
  const PROPERTY_DATA_ROW = "DataRow";
  const PROPERTY_SQL_BASE_OBJECT = "SqlBaseObj";
  const PROPERTY_EMPTY_TEXT = "EmptyText";
  const PROPERTY_VALUE = "Value";
  const PROPERTY_TEXT_FIELD = "TextField";
  const PROPERTY_VALUE_FIELD = "ValueField";
  const PROPERTY_REQUIRED = "Required";
  const PROPERTY_READ_ONLY = "ReadOnly";
  const PROPERTY_ON_CHANGE = "OnChange";
  const PROPERTY_REQUIRED_IF_ONE_OPTION = "RequiredIfOneOption";
  const PROPERTY_FETCHED = "Fetched";
  const PROPERTY_SELECT_FIRST_IF_ONE_OPTION = "SelectFirstIfOneOption";
  
  protected $m_aData = array( self::PROPERTY_LABEL => NULL,
                              self::PROPERTY_DATA_ROW => NULL,
                              self::PROPERTY_SQL_BASE_OBJECT => NULL,
                              self::PROPERTY_EMPTY_TEXT => '',
                              self::PROPERTY_VALUE => 0,
                              self::PROPERTY_TEXT_FIELD => NULL,
                              self::PROPERTY_VALUE_FIELD => NULL,
                              self::PROPERTY_REQUIRED => FALSE,
                              self::PROPERTY_READ_ONLY => FALSE,
                              self::PROPERTY_ON_CHANGE => NULL,
                              self::PROPERTY_REQUIRED_IF_ONE_OPTION => FALSE,
                              self::PROPERTY_FETCHED => 0,
                              self::PROPERTY_SELECT_FIRST_IF_ONE_OPTION => FALSE
                      );
  
  public function __construct($sLabel, &$row, &$oSQLBase, $nValue, $sTextField, $sValueField) {
    $this->m_aData[self::PROPERTY_LABEL] = $sLabel;
    $this->m_aData[self::PROPERTY_DATA_ROW] = $row;
    $this->m_aData[self::PROPERTY_SQL_BASE_OBJECT] = $oSQLBase;
    $this->m_aData[self::PROPERTY_VALUE] = $nValue;
    $this->m_aData[self::PROPERTY_TEXT_FIELD] = $sTextField;
    $this->m_aData[self::PROPERTY_VALUE_FIELD] = $sValueField;
  }
  
  public function __get( $name ) {
      switch ($name)
      {
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
      switch ($name)
      {
        case self::PROPERTY_ON_CHANGE:
          if ($value == NULL)
            $this->m_aData[$name] = $value;
          else
            $this->m_aData[$name] = str_replace('"', '\'', $value); //replace double quotes to avoid javascript error
          break;
        case self::PROPERTY_ID;
          $trace = debug_backtrace();
          throw new Exception(
              'Undefined property via __set(): ' . $name .
              ' in class '. get_class() .', file ' . $trace[0]['file'] .
              ' on line ' . $trace[0]['line']);
          break;
        default:
        if (array_key_exists( $name, $this->m_aData))
        {
            $this->m_aData[$name] = $value;
             return;
        }
        $trace = debug_backtrace();
        throw new Exception(
            'Undefined property via __set(): ' . $name .
            ' in class '. get_class() .', file ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line']);
        break;
      }
    }
    
  //echo directly to html document to save some string concats/retrieval
  public function EchoHtml()
  {
    $this->m_aData[self::PROPERTY_ID]= self::PREFIX . $this->m_aData[self::PROPERTY_VALUE_FIELD];
    
    $sOptions = '';
    
    $bReplacePlaceholder = FALSE;
    
    //options are constructed first to determine if there is just one item in the list
    //and, in that case, implement the RequiredIfOneOption property (of course, the list count is easily accesible in an array
    //so this functionality is not needed in the equivalent HtmlSelectArray)
    while( $this->m_aData[self::PROPERTY_DATA_ROW] )
    {
      //count fetched
      $this->m_aData[self::PROPERTY_FETCHED] = $this->m_aData[self::PROPERTY_FETCHED] + 1;
      
      $sOptions .= '<option value="' . $this->m_aData[self::PROPERTY_DATA_ROW][$this->m_aData[self::PROPERTY_VALUE_FIELD]] . '" ';
      
      if ($this->m_aData[self::PROPERTY_VALUE] == $this->m_aData[self::PROPERTY_DATA_ROW][$this->m_aData[self::PROPERTY_VALUE_FIELD]])
        $sOptions .= ' selected="1" ';
      else if ($this->m_aData[self::PROPERTY_FETCHED] == 1)
      {
        $sOptions .= ' [@#$SELECTED_PLACEHOLDER@#$] ';
        $bReplacePlaceholder = TRUE;
      }
      
      $sOptions .=  ' >' . htmlspecialchars($this->m_aData[self::PROPERTY_DATA_ROW][$this->m_aData[self::PROPERTY_TEXT_FIELD]], ENT_NOQUOTES) . '</option>';
      $this->m_aData[self::PROPERTY_DATA_ROW] = $this->m_aData[self::PROPERTY_SQL_BASE_OBJECT]->fetch();
    }
    
    if ($this->m_aData[self::PROPERTY_FETCHED] == 1)
    {
      if ($this->m_aData[self::PROPERTY_SELECT_FIRST_IF_ONE_OPTION])
      {
        $sOptions = str_replace ('[@#$SELECTED_PLACEHOLDER@#$]', 'selected="1"', $sOptions);
        $bReplacePlaceholder = FALSE;
      }
      
      if( $this->m_aData[self::PROPERTY_REQUIRED_IF_ONE_OPTION])
        $this->m_aData[self::PROPERTY_REQUIRED] = TRUE;
    }
    
    if ($bReplacePlaceholder)
      $sOptions = str_replace ('[@#$SELECTED_PLACEHOLDER@#$]', 'selected="1"', $sOptions);
    
    if ((!$this->m_aData[self::PROPERTY_SELECT_FIRST_IF_ONE_OPTION] && $this->m_aData[self::PROPERTY_VALUE] == self::EMPTY_VALUE)
       || !$this->m_aData[self::PROPERTY_REQUIRED])
    {
      if( $this->m_aData[self::PROPERTY_EMPTY_TEXT] !== NULL)
        $sOptions = '<option value="' . self::EMPTY_VALUE . '" >' . $this->m_aData[self::PROPERTY_EMPTY_TEXT] . '</option>'
              . $sOptions;
    }

    echo '<td nowrap><label ';
    if ($this->m_aData[self::PROPERTY_REQUIRED])
        echo ' class="required" ';
    echo ' for="' , $this->m_aData[self::PROPERTY_ID] , '" >' , $this->m_aData[self::PROPERTY_LABEL] ,
           '‏:‏</label></td>';
    echo '<td><select id="' , $this->m_aData[self::PROPERTY_ID] , '" name="' , $this->m_aData[self::PROPERTY_ID] , '"';
    
    if ($this->m_aData[self::PROPERTY_REQUIRED])
      echo ' class="requiredselect" ';
    else
      echo ' class="selectentry" ';
    
    if ($this->m_aData[self::PROPERTY_READ_ONLY])
      echo ' disabled="1" ';
    
    if ( $this->m_aData[self::PROPERTY_ON_CHANGE] != NULL)
      echo ' onchange="' ,  $this->m_aData[self::PROPERTY_ON_CHANGE] , '" ';
    
    echo ' >' , $sOptions , '</select></td>';
  }
  
}

?>
