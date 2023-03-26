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
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
  <body>
    <div class="text">
      <h3>Current Minecraft Server Status</h3>
      <p>

<?php

$url = "https://api.mcstatus.io/v2/status/bedrock/" . $_ENV["MINECRAFT_SERVER"];
$results = file_get_contents($url);
$status = json_decode($results);

if (ip2long($_ENV["MINECRAFT_SERVER"])) {
	$ipAddress = $_ENV["MINECRAFT_SERVER"];
} else {
	$ipAddress = gethostbyname($_ENV["MINECRAFT_SERVER"]);
}


if ($status->online) {
  echo "MOTD          : " . $status->motd->html . "<br>\n";
  echo "IP            : " . $ipAddress . "<br>\n";
  echo "Port          : " . $status->port . "<br>\n";
  echo "Version       : " . $status->version->name . "<br>\n";
  echo "Protocol      : " . $status->version->protocol . "<br>\n";
  echo "Server ID     : " . $status->server_id . "<br>\n";
  echo "Game Mode     : " . $status->gamemode . "<br>\n";
  echo "Players Online: " . $status->players->online . "<br>\n";
  echo "Max Players   : " . $status->players->max . "<br>\n";
} else {
  echo "Server " . $_ENV["MINECRAFT_SERVER"] . " at " . $ipAddress . " is currently offline!\n";
}

?>

      </p>
    </div>
  </body>
</html>
