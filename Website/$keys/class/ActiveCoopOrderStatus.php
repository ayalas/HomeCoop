<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//used in home page, returns the date-dependant status of a coop order with its status field set to "active"
class ActiveCoopOrderStatus {
  
 const PROPERTY_STATUS = "Status";
 const PROPERTY_STATUS_NAME = "StatusName";
 const None = 0;
 const Open = 1;
 const Closed = 2;
 const Arrived = 3;
 const ArrivingToday = 4;
 
 protected $m_nStatus = self::None;
 protected $m_sStatusName = NULL;
   
 public function __construct($dEnd, $dDelivery, $nCoopOrderStatus)
 {
   global $g_dNow; //now in the coop time zone
   $dNow = $g_dNow;
   
   //if close date is not now yet, coop order is open
   $diInterval = $dNow->diff($dEnd);
   if ($diInterval->invert == 0)
   {
      $this->m_nStatus = self::Open;
      $this->m_sStatusName = '<!$HOME_ORDER_OPEN$!>';
   }
   else
   {
      //close date has passed
      $diInterval = $dNow->diff($dDelivery);
      //get how many days in the interval between now and delivery date
      if ($diInterval->format('%R%a') + 0 == 0) 
      {
          //if 0 and the actual day component is equal 
          //(so not just less than a day gap between now and delivery, like between Jan 1 23:00 and Jan 2 22:00) - 
          //the delivery is today
         if ($dNow->format('j') == $dDelivery->format('j'))
         {
            $this->m_nStatus = self::ArrivingToday;
            $this->m_sStatusName = '<!$HOME_ORDER_ARRIVING_TODAY$!>';
         }
      }

      if ($this->m_nStatus == self::None) //if status was not already set (we covered "arriving today" and "open")
      {
        if ($diInterval->invert == 1) //if delivery date is earlier then now (and not arriving today) - order has arrived
        {
          $this->m_nStatus = self::Arrived;
          $this->m_sStatusName = '<!$HOME_ORDER_ARRIVED$!>';
        }
        else //otherwise, order is just closed, but has not yet arrived (and not arriving today) 
        {
          $this->m_nStatus = self::Closed;
          $this->m_sStatusName = '<!$HOME_ORDER_CLOSED$!>';
        }
      }
    }      
 } 
 
 public function __get( $name ) {
    switch ($name)
    {
      case self::PROPERTY_STATUS:
        return $this->m_nStatus;
      case self::PROPERTY_STATUS_NAME:
        return $this->m_sStatusName;
      default:
        $trace = debug_backtrace();
        throw new Exception(
            'Undefined property via __get(): ' . $name .
            ' in class '. get_class() .', file ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line']);
    }
  }
  
}

?>
