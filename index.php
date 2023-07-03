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
.text1 {
  background-color: black;
  color: white;
  margin-right: 5.5px;
  margin-left: 5.5px;
  margin-bottom: 5.5px;
  margin-top: 5.5px;
  display: table-cell;
  vertical-align: bottom;
}

</style>

<?php

function show_java_info($server) {
  echo '<h3>Status for Java' . $server . '</h3><p>';

  $url = "https://api.mcsrvstat.us/java/" . $server;
  $results = file_get_contents($url);
  $status = json_decode($results);
  if (strpos($server, ':') !== false) {
    $host = substr($server, 0, strpos($server, ':'));
  } else {
    $host = $server;
  }

  if (ip2long($host)) {
	  $ipAddress = $server;
    $hostname = gethostbyaddr($host);
  } else {
	  $ipAddress = gethostbyname($host);
    $hostname = $host;
  }

  if ($status->online) {
    echo "MOTD          : " . $status->motd->html[0] . "<br>\n";
    echo "IP            : " . $ipAddress . "<br>\n";
    echo "Hostname      : " . $hostname . "<br>\n";
    echo "Port          : " . $status->port . "<br>\n";
    echo "Version       : " . $status->version->name_html[0] . "<br>\n";
    echo "Protocol      : " . $status->version->protocol . "<br>\n";
    echo "Server ID     : " . $status->serverid . "<br>\n";
    echo "Game Mode     : " . $status->gamemode . "<br>\n";
    echo "Map           : " . $status->map . "<br>\n";
    echo "Players Online: " . $status->players->online . "<br>\n";
    echo "Max Players   : " . $status->players->max . "<br></p>";
  } else {
    echo "Server " . $hostname . " at " . $ipAddress . " is currently offline!</p>";
  }
}


function show_info($server) {
  echo '<h3>Status for Bedrock' . $server . '</h3><p>';

  $url = "https://api.mcsrvstat.us/bedrock/2/" . $server;
  $results = file_get_contents($url);
  $status = json_decode($results);
  if (strpos($server, ':') !== false) {
    $host = substr($server, 0, strpos($server, ':'));
  } else {
    $host = $server;
  }

  if (ip2long($host)) {
	  $ipAddress = $server;
    $hostname = gethostbyaddr($host);
  } else {
	  $ipAddress = gethostbyname($host);
    $hostname = $host;
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
    echo "Max Players   : " . $status->players->max . "<br></p>";
  } else {
    echo "Server " . $hostname . " at " . $ipAddress . " is currently offline!</p>";
  }
}

echo "<html><head><title>Minecraft Server Status</title>";
echo '<link rel="icon" type="image/x-icon" href="/img/favicon.ico">';
echo '<body><div class="text">';

$maxServers = 10;

for ($i = 1; $i <= $maxServers; $i++) {
  if (isset($_ENV["MINECRAFT_SERVER" . $i])) {
    show_info($_ENV["MINECRAFT_SERVER" . $i]);
  }
}

for ($i = 1; $i <= $maxServers; $i++) {
  if (isset($_ENV["JAVA_MINECRAFT_SERVER" . $i])) {
    show_info($_ENV["JAVA_MINECRAFT_SERVER" . $i]);
  }
}

echo "</div></body></html>";
?>