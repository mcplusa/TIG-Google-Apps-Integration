<?php
  require 'PHPMailer/PHPMailerAutoload.php';
  require 'config.php';

  class PikaEmail {
    private $subject;
    private $mail;
    private $msg;

    function __construct(){
      global $SMTPConfig;
      global $FromConfig;

      $this->mail = new PHPMailer;
      $this->mail->isSMTP();
      $this->mail->SMTPDebug = 2;
      //Enable SMTP debugging [ 0 = off (for production use) | 1 = client messages | 2 = client and server messages ]
      $this->mail->Debugoutput = 'html';

      $this->mail->Host = 'smtp.gmail.com';
      $this->mail->Port = 587;
      $this->mail->SMTPSecure = 'tls';
      $this->mail->SMTPAuth = true;
      $this->mail->Username = $SMTPConfig['user'];
      $this->mail->Password = $SMTPConfig['password'];

      if(!isset($FromConfig['mail']) || empty($FromConfig['mail'])){
        $FromConfig['mail'] = $SMTPConfig['user'];
      }
      
      $this->mail->setFrom($FromConfig['mail'], $FromConfig['name']);
      $this->mail->addReplyTo($FromConfig['mail'], $FromConfig['name']);
    }

    function setFrom($from, $name = ''){
      $this->mail->setFrom($from, $name);
      $this->mail->addReplyTo($from, $name);
    }

    function generateEmailContent($caseNumber, $caseLink, $beginDate, $endDate){
      date_default_timezone_set('UTC');
      $beginDate = date("Ymd\THis\Z", $beginDate);
      $endDate = date("Ymd\THis\Z", $endDate);

      $url = "http://www.google.com/calendar/event?action=TEMPLATE".
              "&text={$caseNumber}".
              "&dates={$beginDate}/{$endDate}".
              "&details={$caseLink}";

      $keys = array(
          '%eventUrl%',
          '%case%',
          '%caseLink%'
      );
      $values = array(
          $url,
          $caseNumber,
          $caseLink
      );

      $this->msg = str_replace($keys,$values,file_get_contents(dirname(__FILE__).'/contents.html'));
    }

    function formatSubject($v){
      global $EmailSubject;

      $keys = array(
          '%case%',
          '%client%'
      );

      print_r($v);
      $values = array(
          $v['case_number'],
          $v['client_name']
      );
      echo $EmailSubject;
      $this->subject = str_replace($keys,$values,$EmailSubject);
    }

    function config(){
      return $mail;
    }

    function send($to){
      if(is_array($to)){
        foreach ($to as $e) {
          $this->mail->addAddress($e, '');
        }
      }else {
        $this->mail->addAddress($to, '');
      }

      $this->mail->Subject = $this->subject;

      $this->mail->msgHTML($this->msg, dirname(__FILE__));

      if (!$this->mail->send()){
        echo "Mailer Error: " . $this->mail->ErrorInfo;
        return false;
      } else {
        echo "Message sent!";
        return true;
      } 
    }
  }
?>