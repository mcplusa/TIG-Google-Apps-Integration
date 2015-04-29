<?php
  require "email.php";
  /**
  *
  * @author  Neomar Marcos Bassani <neomar.bassani@e-storageonline.com.br>
  * @param   array  $activity  Contains database values for the tickler record.
  * @return  return true if success, false if not.
  */

  function create_tickler($activity){
//print_r($activity);
//die();

if(!empty($activity['tickler_email'])){
    $mail = new PikaEmail;
$begin = '';  $end = '';
    if(isset($activity['act_date']) && !empty($activity['act_date'])){
      $begin = strtotime($activity['act_date']." ".$activity['act_time']);
      if(isset($activity['act_end_date'])){
        $end = strtotime($activity['act_end_date']." ".$activity['act_end_time']);
      }else $end = $begin;
    }

    $mail->generateEmailContent($activity, $begin, $end);
    $mail->formatSubject($activity);
    return $mail->send($activity['tickler_email']);
}else return true;
  }

?>
