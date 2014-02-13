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
         <?php if ($user): ?>
            <h3>You</h3>
            <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">
         <?php else: ?>
            <strong><em>You are not Connected.</em></strong>
         <?php endif ?>

         <?php if ($user): ?>
            <a href="<?php echo $logoutUrl; ?>">Logout</a>
         <?php else: ?>
            <div>
               Check the login status using OAuth 2.0 handled by the PHP SDK:
               <a href="<?php echo $statusUrl; ?>">Check the login status</a>
            </div>
            <div>
               Login using OAuth 2.0 handled by the PHP SDK:
               <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
            </div>
         <?php endif ?>

         <h3>PHP Session</h3>
         <pre><?php print_r($_SESSION); ?></pre>

         <h3>Messages</h3>
         <table class="table table-bordered table-hover">
            <thead>
               <tr>
                  <th>
                     User
                  </th>
                  <th>
                     Date
                  </th>
                  <th>
                     Message
                  </th>
               </tr>
            </thead>
            <tbody>
            <?php
               # maximum of 30 comments...thanks
               if (array_key_exists("pageURL", $_POST)) {
                  $nextURL = $_POST["pageURL"];
               } else {
                  $nextURL = "/" . $_GET["id"] . "/comments?fields=message,from&limit=50";
               }
               
               $count = 0;
               $allMessages = array();
               while ($count < 100) {
                  $messages    = $facebook->api($nextURL);
                  if (empty($messages["data"])) {
                     echo "No messages here :(\n";
                     break;
                  }
                  $currMessage = array();
                  foreach ($messages["data"] as $comment) {
                     $count++;
                     if ($count % 100 == 0) {
                        $allMessages[] = array("<b>$count</b>", "", "");
                     }
                     $currMessage[] = array(
                        $comment["from"]["name"],
                        $comment["created_time"],
                        $comment["message"]
                     );
                     $nextURL = $messages["paging"]["next"] . "&access_token=$access_token";
                     $nextURL = str_replace("https://graph.facebook.com", "", $nextURL);
                  }
                  $allMessages = array_merge($allMessages, array_reverse($currMessage));
               }
               $allMessages = array_reverse($allMessages);
               foreach ($allMessages as $message) { ?>
                  <tr>
                     <td><?php echo $message[0] ?></td>
                     <td><?php echo $message[1] ?></td>
                     <td><?php echo $message[2] ?></td>
                  </tr>
               <?php
               } ?>
            </tbody>
         </table>
         <?php 
            echo "<b>$count</b> messages found.\n";
         ?>
            <form method="post" action="messages.php?id=<?php echo $_GET["id"]; ?>">
               <input type="hidden" name="pageURL" value="<?php echo $nextURL; ?>">
               <input type="submit" class="btn btn-primary" value="Next page!">
            </form>
            <form method="post" action="download.php?id=<?php echo $_GET["id"]; ?>">
               <input type="submit" class="btn btn-primary" value="Download conversation!">
            </form>
      </div>
   </body>
</html>
