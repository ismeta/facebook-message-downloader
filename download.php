<?php

require("php-sdk/facebook.php");

$config = array(
   'appId'  => '',
   'secret' => '',
   'allowSignedRequest' => false
);

$facebook = new Facebook($config);
$user = $facebook->getUser();

$params = array(
   'scope' => 'read_mailbox'
);

// Login or logout url will be needed depending on current user state.
if ($user) {
   $logoutUrl = $facebook->getLogoutUrl();
} else {
   $statusUrl = $facebook->getLoginStatusUrl();
   $loginUrl = $facebook->getLoginUrl($params);
}

$access_token = $facebook->getAccessToken();

?>

<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
   <head>
      <title>Creep</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
      <div class="navbar navbar-inverse" role="navigation">
         <div class="container">
            <div class="navbar-header">
               <a class="navbar-brand" href="index.php">Messages</a>
            </div>
         </div>
      </div>

      <div class="container">
      <?php
         # maximum of 30 comments...thanks
         if (array_key_exists("pageURL", $_POST)) {
            $nextURL = $_POST["pageURL"];
         } else {
            $nextURL = "/" . $_GET["id"] . "/comments?fields=message,from&limit=50";
         }
         $header = <<<_HTML
<html>
   <head>
      <title>$_GET[id]
      </title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="../css/bootstrap.min.css" rel="stylesheet">
      <link href="../css/bootstrap-theme.min.css" rel="stylesheet">
   </head>
   <body>
      <h1>$_GET[id] and you!</h1>
      <div class="container">
      <table class="table table-hover table-bordered">
         <tbody>

_HTML;

         $footer = <<<HTML
         </tbody>
      </table>
   </div>
   </body>
</html>
HTML;

         $count = 0;
         $allMessages = array();
         $currMessage = array();
         while (true) {
            try {
               $messages = $facebook->api($nextURL);
            } catch (FacebookApiException $e) {
               sleep(1);
               error_log("something went wrong, probably went over API call limit");
               error_log($nextURL);
               continue;
            }
            if (empty($messages["data"])) {
               error_log(print_r($messages, true));
               error_log($nextURL);
               error_log("No message here :(");
               break;
            }
            $currMessage = array();
            foreach ($messages["data"] as $comment) {
               if ($count % 10000 == 0 && $count != 0) {
                  error_log("at $count messages, next pagetoken is $nextURL");
                  $allMessages[] = array("<b>$count</b>", "", "");
                  $allMessages = array_merge($currMessage, $allMessages);

                  $concat = $header;
                  foreach ($allMessages as $message) {
                     $concat .= "         <tr><td>$message[1]</td><td>$message[0]</td><td>$message[2]</td></tr>\n";
                  }
                  $concat .= $footer;
                  file_put_contents("messages/$_GET[id]" . "_" . ($count / 10000) . "test.html", $concat);
                  $allMessages = array();
                  $currMessage = array();
               }
               $count++;
               if (!isset($comment["message"])) {
                  $comment["message"] = "";
               }
               $currMessage[] = array(
                  $comment["from"]["name"],
                  $comment["created_time"],
                  $comment["message"]
               );
               $nextURL = $messages["paging"]["next"] . "&access_token=$access_token";
               $nextURL = str_replace("https://graph.facebook.com", "", $nextURL);
            }
            $allMessages = array_merge($currMessage, $allMessages);
         }
         $concat = $header;
         foreach ($allMessages as $message) {
            $concat .= "         <tr><td>$message[1]</td><td>$message[0]</td><td>$message[2]</td></tr>\n";
         }
         $concat .= $footer;

         file_put_contents("messages/$_GET[id]" . "_" . (($count / 10000) + 1) . ".html", $concat);

         echo "<b>$count</b> messages written.\n";
      ?>
      </div>
   </body>
</html>
