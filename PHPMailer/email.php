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

    function generateEmailContent($v, $beginDate, $endDate){
      global $CalendarSubject;

      date_default_timezone_set('UTC');
      $beginDate = date("Ymd\THis\Z", $beginDate);
      $endDate = date("Ymd\THis\Z", $endDate);

      $calendarSubject = replaceVars($CalendarSubject, $v);

      $url = "http://www.google.com/calendar/event?action=TEMPLATE".
              "&text={$calendarSubject}".
              "&dates={$beginDate}/{$endDate}".
              "&details={$v['case_link']}";

      $msg = replaceVars(file_get_contents(dirname(__FILE__).'/contents.html'), $v);

      $this->msg = str_replace('%eventUrl%',$url, $msg);
    }

    function formatSubject($v){
      global $EmailSubject;

      $this->subject = replaceVars($EmailSubject, $v);
    }

    function replaceVars($str, $v){
      $keys = array(
          '%case%',
          '%clientLastName%',
          '%clientFirstName%',
          '%subject%',
          '%user%',
          '%caseLink%'
      );

      $c = preg_split('/\s+/', trim($v['client_name']));

      $values = array(
          $v['case_number'],
          array_pop($c),
          array_shift($c),
          $v['summary'],
          '',
          $v['case_link']
      );

      return str_replace($keys,$values,$str);
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