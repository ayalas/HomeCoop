<?

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//basic db access class
class DBAccess
{
        const PROPERTY_CONNECTION = "Connection";

        protected $m_oConnection = NULL;

        public function __get( $name ) 
        {
            switch ( $name ) 
            {
                case  self::PROPERTY_CONNECTION;
                    return  $this->m_oConnection;
                default:
                    $trace = debug_backtrace();
                    throw new Exception(
                        'Undefined property via __get(): ' . $name .
                        ' in class '. get_class() .', file ' . $trace[0]['file'] .
                        ' on line ' . $trace[0]['line']);
                break;
            }
        }

        public function Connect()
        {
            if ($this->m_oConnection === NULL)
            {
                $this->m_oConnection = new PDO( DB_CONNECTION_STRING, DB_USERNAME, DB_PASSWORD,
                        array(  PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                                PDO::ATTR_PERSISTENT => FALSE, //CANCELLED web hosts may not support this
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //any error is exception
                                PDO::ATTR_AUTOCOMMIT => 0 ) //allows transactions
                        );
            }
        }
        
        public function ConnectNonPersist()
        {
            if ($this->m_oConnection === NULL)
            {
                $this->m_oConnection = new PDO( DB_CONNECTION_STRING, DB_USERNAME, DB_PASSWORD,
                        array(  PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //any error is exception
                                PDO::ATTR_AUTOCOMMIT => 0) //allows transactions
                    );
            }
        }
        
        public function Close()
        {
          unset($this->m_oConnection);
        }
}
