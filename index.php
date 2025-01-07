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
            flex-wrap: wrap; /* Ensure columns wrap on smaller screens */
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
        .online {
            color: green;
        }
        .offline {
            color: red;
        }
        .server-name {
            font-weight: bold;
        }
        .dot {
            height: 12px; /* Increased size for better visibility */
            width: 12px; /* Increased size for better visibility */
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle; /* Align the dot with the text */
        }
        .online-dot {
            background-color: green;
        }
        .offline-dot {
            background-color: red;
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
                $status = fetch_api_data($api_url);
                if (!$status || isset($status->error)) {
                    echo "Failed to retrieve data for $server.</p>";
                    return;
                }

                // Shared logic for displaying server details
                $host = strpos($server, ':') !== false ? substr($server, 0, strpos($server, ':')) : $server;
                $ipAddress = ip2long($host) ? $server : gethostbyname($host);
                $hostname = ip2long($host) ? gethostbyaddr($host) : $host;
                
                echo "<hr><h3>Status for " . ucfirst($type) . " Server<br />";
                
                // Check server status (online or offline)
                if ($status->online) {
                    echo '<span class="dot online-dot"></span><span class="server-name">' . htmlspecialchars($server) . '</span></h3><hr><p>';
                    // Display online details
                    $motdHtml = is_array($status->motd->html) ? implode('<br>', $status->motd->html) : $status->motd->html;
                    echo "MOTD: $motdHtml<br>";
                    echo "IP: " . htmlspecialchars($ipAddress) . "<br>";
                    echo "Hostname: " . htmlspecialchars($hostname) . "<br>";
                    echo "Port: " . htmlspecialchars($status->port) . "<br>";

                    // Check if version and protocol exist
                    $version_name = isset($status->version->name) ? htmlspecialchars($status->version->name) : 'N/A';
                    $version_protocol = isset($status->version->protocol) ? htmlspecialchars($status->version->protocol) : 'N/A';
                    $version_name= isset($status->version->name_clean) ? htmlspecialchars($status->version->name_clean) : $version_name;

                    echo "Version: $version_name<br>";
                    echo "Protocol: $version_protocol<br>";
                    echo "Players Online: " . htmlspecialchars($status->players->online) . "<br>";
                    echo "Max Players: " . htmlspecialchars($status->players->max) . "<br>";
                } else {
                    echo '<span class="dot offline-dot"></span><span class="server-name">' . htmlspecialchars($hostname) . '</span></h3><hr><p>';
                    // Display offline details (hostname and IP only)
                    echo "IP: " . htmlspecialchars($ipAddress) . "<br>";
                    echo "Hostname: " . htmlspecialchars($hostname) . "<br>";
                }
            }

            // Display Bedrock servers
            for ($i = 1; $i <= 10; $i++) {
                $env_var = "MINECRAFT_SERVER$i";
                if (getenv($env_var)) {
                    $bedrock_server = getenv($env_var);
                    $bedrock_api_url = "https://api.mcstatus.io/v2/status/bedrock/" . $bedrock_server;
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
