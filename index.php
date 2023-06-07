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

<?php

echo "<html><head><title>" . $_ENV["MINECRAFT_SERVER"] . " Status</title>";
echo '<link rel="icon" type="image/x-icon" href="/img/favicon.ico">';
echo '<body><div class="text"><h3>' . $_ENV["MINECRAFT_SERVER"] . ' Status</h3><p>';

$url = "https://api.mcsrvstat.us/bedrock/2/" . $_ENV["MINECRAFT_SERVER"];
$results = file_get_contents($url);
$status = json_decode($results);

if (ip2long($_ENV["MINECRAFT_SERVER"])) {
	$ipAddress = $_ENV["MINECRAFT_SERVER"];
  $hostname = gethostbyaddr($_ENV["MINECRAFT_SERVER"]);
} else {
	$ipAddress = gethostbyname($_ENV["MINECRAFT_SERVER"]);
  $hostname = $_ENV["MINECRAFT_SERVER"];
}


  if ($status->online) {
  echo "MOTD          : " . $status->motd->html[0] . "<br>\n";
  echo "IP            : " . $ipAddress . "<br>\n";
  echo "Hostname      : " . $hostname . "<br>\n";
  echo "Port          : " . $status->port . "<br>\n";
  echo "Version       : " . $status->version . "<br>\n";
  echo "Protocol      : " . $status->protocol . "<br>\n";
  echo "Server ID     : " . $status->serverid . "<br>\n";
  echo "Game Mode     : " . $status->gamemode . "<br>\n";
  echo "Map           : " . $status->map . "<br>\n";
  echo "Players Online: " . $status->players->online . "<br>\n";
  echo "Max Players   : " . $status->players->max . "<br>\n";
} else {
  echo "Server " . $hostname . " at " . $ipAddress . " is currently offline!\n";
}

echo "</p></div></body></html>";
?>