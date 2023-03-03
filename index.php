<!DOCTYPE html>
<style>

html {
  background: url(img/background.jpg) no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}
/* Bottom right text */
.text {
  position: absolute;
  bottom: 20px;
  right: 20px;
  background-color: black;
  color: white;
  padding-left: 20px;
  padding-right: 20px;
}

</style>

<html>
  <head>
    <title>Minecraft Server Status</title>
  <body>
    <div class="text">
      <h3>Current Minecraft Server Status</h3>
      <p>

<?php

$url = 'https://api.mcsrvstat.us/bedrock/2/" . $_ENV["MINECRAFT_SERVER"];
$response = file_get_contents($url); // put the contents of the file into a variable
$status = json_decode($response);

if ($status->online) {
  echo "IP            : " . $status->ip . "<br>\n";
  echo "Port          : " . $status->port . "<br>\n";
  echo "Version       : " . $status->version . "<br>\n";
  echo "Map           : " . $status->map . "<br>\n";
  echo "Game Mode     : " . $status->gamemode . "<br>\n";
  echo "Players Online: " . $status->players->online . "<br>\n";
  echo "Max Players   : " . $status->players->max . "<br>\n";
} else {
  echo "Server is currently offline!\n";
}

?>

      </p>
    </div>
  </body>
</html>
