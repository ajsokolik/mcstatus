<?php
// Function to display server information
function display_server_info($type, $server, $api_url) {
    echo '<hr><h3>Status for ' . ucfirst($type) . ' Server<br />' . htmlspecialchars($server) . '</h3><hr><p>';

    $results = file_get_contents($api_url);
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
        if ($type === "bedrock") {
            $motdHtml = is_array($status->motd->html) ? implode('<br>', $status->motd->html) : $status->motd->html;

            echo "MOTD          : " . $motdHtml . "<br>\n";
            echo "IP            : " . htmlspecialchars($ipAddress) . "<br>\n";
            echo "Hostname      : " . htmlspecialchars($hostname) . "<br>\n";
            echo "Port          : " . htmlspecialchars($status->port) . "<br>\n";
            echo "Version       : " . htmlspecialchars($status->version) . "<br>\n";
            echo "Protocol      : " . htmlspecialchars($status->protocol) . "<br>\n";
            echo "Game Mode     : " . htmlspecialchars($status->gamemode) . "<br>\n";
            echo "Players Online: " . htmlspecialchars($status->players->online) . "<br>\n";
            echo "Max Players   : " . htmlspecialchars($status->players->max) . "<br></p>";
        } else if ($type === "java") {
            $motdHtml = is_string($status->motd->html) ? $status->motd->html : implode('<br>', (array)$status->motd->html);

            echo "MOTD          : " . $motdHtml . "<br>\n";
            echo "IP            : " . htmlspecialchars($ipAddress) . "<br>\n";
            echo "Hostname      : " . htmlspecialchars($hostname) . "<br>\n";
            echo "Port          : " . htmlspecialchars($status->port) . "<br>\n";
            echo "Version       : " . htmlspecialchars($status->version->name_clean) . "<br>\n";
            echo "Protocol      : " . htmlspecialchars($status->version->protocol) . "<br>\n";
            echo "Players Online: " . htmlspecialchars($status->players->online) . "<br>\n";
            echo "Max Players   : " . htmlspecialchars($status->players->max) . "<br></p>";
        }
    } else {
        echo "Server " . htmlspecialchars($hostname) . " at " . htmlspecialchars($ipAddress) . " is currently offline!</p>";
    }
}

// Render the HTML page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minecraft Server Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        h1 {
            text-align: center;
            padding: 20px;
            background-color: #000;
            color: #fff;
            margin: 0;
        }
        h3 {
            color: #444;
        }
        p {
            padding: 0 20px;
        }
        hr {
            border: none;
            border-top: 2px solid #ccc;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Minecraft Server Status Dashboard</h1>
    <?php
    // Get Bedrock server environment variables and display their status
    for ($i = 1; $i <= 10; $i++) {
        $env_var = "MINECRAFT_SERVER$i";
        if (getenv($env_var)) {
            $bedrock_server = getenv($env_var);
            $bedrock_api_url = "https://api.mcsrvstat.us/bedrock/2/" . $bedrock_server;
            display_server_info("bedrock", $bedrock_server, $bedrock_api_url);
        }
    }

    // Get Java server environment variables and display their status
    for ($i = 1; $i <= 10; $i++) {
        $env_var = "JAVA_MINECRAFT_SERVER$i";
        if (getenv($env_var)) {
            $java_server = getenv($env_var);
            $java_api_url = "https://api.mcstatus.io/v2/status/java/" . $java_server;
            display_server_info("java", $java_server, $java_api_url);
        }
    }
    ?>
</body>
</html>
