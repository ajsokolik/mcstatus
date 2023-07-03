<!DOCTYPE html>
<style>

html {
  background: url(img/background.jpg) no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}

.container {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    
    .box {
      width: 200px;
      height: 150px;
      background-color: black;
      color: white;
      padding: 10px;
    }
    
    .lower-right {
      text-align: right;
      background-color: black;
      color: white;
    }
    
    .lower-left {
      text-align: left;
      background-color: black;
      color: white;
    }

</style>

<?php

function show_java_info($server) {
  echo '<h3>Status for Java Server<br />' . $server . '</h3<hr><p>';

  $url = "https://api.mcstatus.io/v2/status/java/" . $server;
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
    echo "Players Online: " . $status->players->online . "<br>\n";
    echo "Max Players   : " . $status->players->max . "<br></p>";
  } else {
    echo "Server " . $hostname . " at " . $ipAddress . " is currently offline!</p>";
  }
}


function show_info($server) {
  echo '<h3>Status for Bedrock Server<br />' . $server . '</h3><hr><p>';

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
echo '<body><div class="container">';

$maxServers = 10;

echo '<div class="lower-right">';

for ($i = 1; $i <= $maxServers; $i++) {
  if (isset($_ENV["MINECRAFT_SERVER" . $i])) {
    show_info($_ENV["MINECRAFT_SERVER" . $i]);
  }
}

echo '</div><div class="lower-left">';

for ($i = 1; $i <= $maxServers; $i++) {
  if (isset($_ENV["JAVA_MINECRAFT_SERVER" . $i])) {
    show_java_info($_ENV["JAVA_MINECRAFT_SERVER" . $i]);
  }
}

echo "</div></div></body></html>";
?>