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
if ($user) {
   $messages = $facebook->api('/me/inbox');
}

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

         <?php if ($user): ?>
         <h3>Messages</h3>
         <table class="table">
            <thead>
               <tr>
                  <th>User
                  </th>
               </tr>
            </thead>
            <tbody>
            <?php
               foreach ($messages["data"] as $thread) {
                  $all = array();
                  foreach ($thread["to"]["data"] as $participant) {
                     $all[] = $participant["name"];
                  }
                  echo "<tr><td><a href=\"messages.php?id=" . $thread["id"] . "\">" . implode(", ", $all) . "</a></td></tr>";
               }
            ?>
         </table>
         <?php endif ?>
         <?php if ($user): ?>
            <h3>You</h3>
            <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">

            <h3>Your User Object (/me)</h3>
            <pre>
               <?php 
                $user_profile = $facebook->api('/me');
                print_r($user_profile); ?>
            </pre>
         <?php else: ?>
            <strong><em>You are not Connected.</em></strong>
         <?php endif ?>
      </div>
  </body>
</html>
