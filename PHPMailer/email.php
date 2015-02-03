<?php
  require 'PHPMailer/PHPMailerAutoload.php';

  $to = setTo;
  $from = setFrom;
  $subject = 'Tickler Notification';
  $username = setUsername;
  $password = setPassword;

  $case_number = "M-20-00003";
  $case_link = "http://pika.com/" . $case_number;
  $begin_date = time();
  $end_date = time();

  function loadEmailContent($case_number, $case_link, $begin_date, $end_date){
    date_default_timezone_set('UTC');
    $begin_date = date("Ymd\THis\Z", $begin_date);
    $end_date = date("Ymd\THis\Z", $end_date);

    $url = "http://www.google.com/calendar/event?action=TEMPLATE".
            "&text={$case_number}".
            "&dates={$begin_date}/{$end_date}".
            "&details={$case_link}";

    $keys = array(
        '%action_url%',
        '%case_id%'
    );
    $values = array(
        $url,
        $case_number
    );

    $email = file_get_contents('contents.html');
    
    return str_replace($keys,$values,$email);
  }

  $mail = new PHPMailer;

  $mail->isSMTP();
  $mail->SMTPDebug = 2;
  //Enable SMTP debugging [ 0 = off (for production use) | 1 = client messages | 2 = client and server messages ]
  $mail->Debugoutput = 'html';

  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 25;
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth = false;
  $mail->Username = $username;
  $mail->Password = $password;

  $mail->setFrom($from, ''); 
  $mail->addReplyTo($from, '');
  $mail->addAddress($to, '');
  $mail->Subject = $subject;

  $mail->msgHTML(loadEmailContent($case_number, $case_link, $begin_date, $end_date), dirname(__FILE__));

  if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
  } else {
    echo "Message sent!";
  } 
?>