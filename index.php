<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minecraft Server Status</title>
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url(img/background.jpg) no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }

        .container {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            gap: 20px;
        }

        .box {
            width: 300px;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }

        .box h3 {
            margin-top: 0;
            font-size: 1.2rem;
            border-bottom: 1px solid #fff;
            padding-bottom: 10px;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.8);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }

            .box {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; padding: 20px;">Minecraft Server Status Dashboard</h1>
    </header>
    <main>
        <div class="container">
            <?php
            // Function to fetch and decode JSON data with error handling
            function fetch_status($url) {
                $response = @file_get_contents($url);
                if ($response === FALSE) {
                    return null;
                }
                return json_decode($response);
            }

            // Function to sanitize server names
            function sanitize_server_name($server) {
                return htmlspecialchars($server, ENT_QUOTES, 'UTF-8');
            }

            // Function to display server information
            function display_server_info($type, $server, $api_url) {
                echo '<div class="box">';
                echo '<h3>' . ucfirst($type) . ' Server: ' . sanitize_server_name($server) . '</h3>';
                $status = fetch_status($api_url . $server);

                if (!$status) {
                    echo "<p>Unable to fetch server status. Please try again later.</p>";
                    echo '</div>';
                    return;
                }

                $host = strpos($server, ':') !== false ? strstr($server, ':', true) : $server;
                $ipAddress = ip2long($host) ? $server : gethostbyname($host);
                $hostname = ip2long($host) ? gethostbyaddr($host) : $host;

                if (!empty($status->online)) {
                    echo "<p>MOTD          : " . ($type === 'java' ? implode("", $status->motd->html) : $status->motd->html[0]) . "</p>";
                    echo "<p>IP            : " . $ipAddress . "</p>";
                    echo "<p>Hostname      : " . $hostname . "</p>";
                    echo "<p>Port          : " . $status->port . "</p>";
                    echo "<p>Version       : " . ($type === 'java' ? $status->version->name_html : $status->version) . "</p>";
                    echo "<p>Players Online: " . $status->players->online . "/" . $status->players->max . "</p>";
                } else {
                    echo "<p>Server " . $hostname . " at " . $ipAddress . " is currently offline.</p>";
                }
                echo '</div>';
            }

            // Display Java and Bedrock servers
            $maxServers = 10;

            for ($i = 1; $i <= $maxServers; $i++) {
                if (!empty($_ENV["JAVA_MINECRAFT_SERVER" . $i])) {
                    display_server_info('java', $_ENV["JAVA_MINECRAFT_SERVER" . $i], "https://api.mcstatus.io/v2/status/java/");
                }
                if (!empty($_ENV["MINECRAFT_SERVER" . $i])) {
                    display_server_info('bedrock', $_ENV["MINECRAFT_SERVER" . $i], "https://api.mcsrvstat.us/bedrock/2/");
                }
            }
            ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Minecraft Server Status Dashboard</p>
    </footer>
</body>
</html>
