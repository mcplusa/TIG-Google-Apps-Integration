<?php
  
  $SMTPConfig['user'] = "";
  $SMTPConfig['password'] = "";
  
  $EmailSubject = "Tickler Notification - %clientLastName%";
  $CalendarSubject = "%clientLastName% - %subject% (%user%)";

  $FromConfig['mail'] = "no-reply@pikasoftware.com";
  $FromConfig['name'] = "Pika Software";

?>