<?php

  if(isset($_GET['email'])){
    require "create_tickler.php";

    $activity = array(
      'case_number'   => $_GET['case'],
      'tickler_email' => $_GET['email'],
      'client_name'   => $_GET['client'],
      'act_date'      => date("Y/m/d"),
      'act_time'      => date("H:i:s"),
      'case_link'     => ''
    );

    echo create_tickler($activity);
  }else{
?>
    <html>
      <head>
        <style type="text/css">
          input {
            width: 100%;
          }

          iframe {
            width: 100%;
            min-height: 500px;
          }
        </style>
      </head>
      <body>
        <h1>E-mail tester</h1>
        <form action="test.php" method="GET" target="result">
          <table border="1" width="100%">
            <tr>
              <td>
                <strong>
                  Send To
                </strong>
              </td>
              <td>
                <input id="email" name="email" type="email" >
              </td>
            </tr>
            <tr>
              <td>
                <strong>
                  Case Number
                </strong>
              </td>
              <td>
                <input id="case" name="case" type="text" >
              </td>
            </tr>
            <tr>
              <td>
                <strong>
                  Client Name
                </strong>
              </td>
              <td>
                <input id="client" name="client" type="text" >
              </td>
            </tr>
          </table>
          <br>
          <button type="submit">Send E-mail</button>
        </form>

        <h1>Logs:</h1>
        <iframe name="result" id="result" src=""></iframe>
      </body>
    </html>
<?php } ?>