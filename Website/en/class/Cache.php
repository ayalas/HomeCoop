<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//original source: http://devgrow.com/simple-cache-class/
class Cache {
  
    const PROPERTY_IS_CACHING = "IsCaching";
    const PROPERTY_CAN_CACHE = "CanCache";
    const PROPERTY_CACHE_TIME = "CacheTimeInSeconds";

    // General Config Vars
    protected $cacheTime = 0; 
    protected $cacheDir = NULL;
    protected $caching = false;
    protected $cancache = false;
    protected $cacheFile;
    protected $cacheFileName;
    protected $cacheLogFile;
    protected $cacheLog;

    function __construct(){
      global $g_sRootRelativePath;
      
      $this->cacheDir = $g_sRootRelativePath . Consts::URL_CACHE_DIR;
      
      $this->cacheFile = base64_encode($_SERVER['REQUEST_URI']);
      $this->cacheFileName = $this->cacheDir . '/' . $this->cacheFile. '.txt';
      $this->cacheLogFile = $this->cacheDir . "/log.txt";
      if(!is_dir($this->cacheDir)) mkdir($this->cacheDir, 0755);
      if(file_exists($this->cacheLogFile))
          $this->cacheLog = unserialize(file_get_contents($this->cacheLogFile));
      else
          $this->cacheLog = array();
    }
    
    public function __get( $name ) {
      switch ($name)
      {
        case self::PROPERTY_IS_CACHING:
          return $this->caching;
        case self::PROPERTY_CAN_CACHE:
          return $this->cancache;
        default:
          $trace = debug_backtrace();
          throw new Exception(
              'Undefined property via __get(): ' . $name .
              ' in class '. get_class() .', file ' . $trace[0]['file'] .
              ' on line ' . $trace[0]['line']);
      }
    }
    
   public function __set( $name, $value ) {
      switch ($name)
      {
        case self::PROPERTY_CACHE_TIME:
          $this->cacheTime = $value;
          break;
        default:
          $trace = debug_backtrace();
          trigger_error(
            'unsupported property via __set(): ' . $name .
            ' in class '. get_class() .', file ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
      }
    }
   
    //either starts recording what is to be cached or outputs already cached content
    //if there is no write permission to the cache dir, no action is done, and CanCache remains FALSE
    function start(){
        if(file_exists($this->cacheFileName) && (time() - filemtime($this->cacheFileName)) < $this->cacheTime && $this->cacheLog[$this->cacheFile] == 1){
            $this->caching = false;
            echo file_get_contents($this->cacheFileName);
            $this->cancache = TRUE;
        }
        else if (is_writable($this->cacheDir))
        {
            $this->caching = true;
            ob_start();
            $this->cancache = TRUE;
        }
    }
   
    //stops recording what is to be cached and saves the data in a file, in case such recording has started
    function end(){
        if($this->caching){
            file_put_contents($this->cacheFileName,ob_get_contents());
            ob_end_flush();
            $this->cacheLog[$this->cacheFile] = 1;
            if(file_put_contents($this->cacheLogFile,serialize($this->cacheLog)))
                return true;
        }
    }
   
    //not in use and not tested
    function purge($location){
        $location = base64_encode($location);
        $this->cacheLog[$location] = 0;
        if(file_put_contents($this->cacheLogFile,serialize($this->cacheLog)))
            return true;
        else
            return false;
    }
   
    //not in use and not tested
    function purge_all(){
        if(file_exists($this->cacheLogFile)){
            foreach($this->cacheLog as $key=>$value) $this->cacheLog[$key] = 0;
            if(file_put_contents($this->cacheLogFile,serialize($this->cacheLog)))
                return true;
            else
                return false;
        }
    }

}

?>
