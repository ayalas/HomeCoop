<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//this class currently displays only balance validation notice
//in the future may display other notices as well
class Notifications {

  public function DisplayNotifications()
  {
    global $g_oMemberSession, $g_dNow;
    
    //dynamic notification for new member's 0 balance
    if (    $g_oMemberSession->Balance == 0 
            && $g_oMemberSession->PaymentMethod != Consts::PAYMENT_METHOD_AT_PICKUP )
    {
        echo '<tr><td>As of now you do not have a balanace associated with your newly created login credentials.</td></tr>',
         '<tr><td>Without a balance, you will not be able to make orders.</td></tr>',
        
         '<tr><td>' ,  sprintf('To update your balance contact us at %s', COOP_ADDRESS_MEMBER_BALANCE )  ,  '</td></tr>';
    }    
  }
}

?>
