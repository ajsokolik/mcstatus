<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minecraft Server Status</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('img/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        h1 {
            text-align: center;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            margin: 0;
        }
        .server-container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }
        .server-column {
            width: 48%;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
        }
        h3 {
            color: #f0f0f0;
        }
        p {
            margin: 0 0 10px;
        }
        hr {
            border: none;
            border-top: 2px solid #555;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Minecraft Server Status Dashboard</h1>
    <div class="server-container">
        <!-- Bedrock Servers Column -->
        <div class="server-column">
            <h2>Bedrock Servers</h2>
            <?php
            // Function to fetch data from an API
            function fetch_api_data($api_url) {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5, // Set a timeout for the request
                    ],
                ]);
                $results = @file_get_contents($api_url, false, $context); // Suppress warnings
                if ($results === false) {
                    return null;
                }
                return json_decode($results);
            }

            // Function to display server information
            function display_server_info($type, $server, $api_url) {
                echo '<hr><h3>Status for ' . ucfirst($type) . ' Server<br />' . htmlspecialchars($server) . '</h3><hr><p>';

                $status = fetch_api_data($api_url);

                if (!$status) {
                    echo "Failed to retrieve data from API for server " . htmlspecialchars($server) . "!</p>";
                    return;
                }

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

            // Display Bedrock servers
            for ($i = 1; $i <= 10; $i++) {
                $env_var = "MINECRAFT_SERVER$i";
                if (getenv($env_var)) {
                    $bedrock_server = getenv($env_var);
                    #$bedrock_api_url = "https://api.mcsrvstat.us/bedrock/2/" . $bedrock_server;
                    $bedrock_api_url = "https://api.mcsrvstat.us/bedrock/2/minecraft.sokolik.info"; // Hardcoded for testing
                    display_server_info("bedrock", $bedrock_server, $bedrock_api_url);
                }
            }
            ?>
        </div>

        <!-- Java Servers Column -->
        <div class="server-column">
            <h2>Java Servers</h2>
            <?php
            // Display Java servers
            for ($i = 1; $i <= 10; $i++) {
                $env_var = "JAVA_MINECRAFT_SERVER$i";
                if (getenv($env_var)) {
                    $java_server = getenv($env_var);
                    $java_api_url = "https://api.mcstatus.io/v2/status/java/" . $java_server;
                    display_server_info("java", $java_server, $java_api_url);
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
