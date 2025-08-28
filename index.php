<?php
// Ensure cache directory exists
$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Function to fetch API data with caching
function fetch_api_data($api_url) {
    global $cache_dir;
    $cache_file = $cache_dir . '/' . md5($api_url) . '.json';
    $cache_time = 30; // cache for 30 seconds

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        $results = file_get_contents($cache_file);
    } else {
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $results = @file_get_contents($api_url, false, $context);
        if ($results !== false) {
            file_put_contents($cache_file, $results);
        }
    }

    return $results ? json_decode($results) : null;
}

// Function to display server info
function display_server_info($type, $server, $api_url) {
    // Fetch status from API
    $status = fetch_api_data($api_url);

    // Parse host and port
    if (strpos($server, ':') !== false) {
        list($host, $portOverride) = explode(':', $server, 2);
        $port = intval($portOverride);
    } else {
        $host = $server;
        $port = ($type === 'bedrock') ? 19132 : 25565; // default ports
    }

    // Determine display hostname
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $reverse = @gethostbyaddr($host);
        $displayName = $reverse ? $reverse : $host;
    } else {
        $displayName = $host;
    }

    // Determine online/offline
    $online = ($status && isset($status->online) && $status->online);

    $statusDot = $online ? '<span class="dot online-dot"></span>' : '<span class="dot offline-dot"></span>';

    echo '<div class="server-card">';
    echo '<div class="status">' . $statusDot . strtoupper(htmlspecialchars($displayName)) . '</div>';

    if ($online) {
        // MOTD handling
        if (isset($status->motd->html)) {
            $motdHtml = is_array($status->motd->html) ? implode('<br>', $status->motd->html) : $status->motd->html;
            echo '<div class="motd">' . $motdHtml . '</div>';
        }

        // Determine display IP
        $ipAddress = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        // IP and hostname
        echo "IP: " . htmlspecialchars($ipAddress) . "<br>";
        echo "Hostname: " . htmlspecialchars($displayName) . "<br>";
        echo "Port: " . htmlspecialchars($status->port ?? $port) . "<br>";

        // Version info
        $version_name = $status->version->name_clean ?? ($status->version->name ?? 'N/A');
        $version_protocol = $status->version->protocol ?? 'N/A';
        echo "Version: " . htmlspecialchars($version_name) . "<br>";
        echo "Protocol: " . htmlspecialchars($version_protocol) . "<br>";

        // Player info with bar
        $onlinePlayers = $status->players->online ?? 0;
        $maxPlayers = $status->players->max ?? 0;
        $fillPercent = $maxPlayers > 0 ? round(($onlinePlayers / $maxPlayers) * 100) : 0;

        // Determine color for player bar
        if ($fillPercent < 50) {
            $colorClass = 'green';
        } elseif ($fillPercent < 80) {
            $colorClass = 'yellow';
        } else {
            $colorClass = 'red';
        }

        echo '<div class="player-bar tooltip">
                <div class="player-bar-fill ' . $colorClass . '" style="width:' . $fillPercent . '%;"></div>
                <div class="player-bar-text">' . $onlinePlayers . ' / ' . $maxPlayers . '</div>
                <span class="tooltiptext">Players online: ' . $onlinePlayers . '/' . $maxPlayers . '</span>
              </div>';

    } else {
        // Offline info
        echo "IP: " . htmlspecialchars($host) . "<br>";
        echo "Hostname: " . htmlspecialchars($displayName) . "<br>";
        echo "Port: " . htmlspecialchars($port) . "<br>";
        echo '<p class="offline">Server offline</p>';
    }

    echo '</div>'; // end server-card
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minecraft Server Status</title>
<link rel="icon" href="img/favicon.ico" type="image/x-icon">
<style>
body { font-family: Arial,sans-serif; margin:0; padding:0; background:url('img/background.jpg') no-repeat center center fixed; background-size:cover; color:#fff; }
h1 { text-align:center; padding:20px; background-color:rgba(0,0,0,0.8); color:#fff; margin:0; }
.server-container { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; }
.server-card { background-color: rgba(0,0,0,0.6); padding:15px; border-radius:8px; min-width:350px; max-width:380px; box-shadow:0 0 10px rgba(0,0,0,0.8); word-wrap: break-word; }
.status { font-weight:bold; margin-bottom:10px; }
.dot { height:12px; width:12px; border-radius:50%; display:inline-block; margin-right:10px; vertical-align:middle; }
.online-dot { background-color:#00ff00; }
.offline-dot { background-color:#ff0000; }
.player-bar { height:20px; border-radius:10px; background-color:#444; margin:5px 0 10px 0; position: relative; min-width:150px; }
.player-bar-fill.green { background-color:#0f0; }
.player-bar-fill.yellow { background-color:#ff0; color:#000; }
.player-bar-fill.red { background-color:#f00; }
.player-bar-text { position:absolute; width:100%; text-align:center; line-height:20px; color:#fff; font-weight:bold; font-size:0.85em; pointer-events:none; }
.tooltip { position: relative; display: block; }
.tooltip .tooltiptext { visibility: hidden; background: rgba(0,0,0,0.8); color:#fff; text-align:center; padding:4px 8px; border-radius:5px; position:absolute; z-index:10; bottom:125%; left:50%; transform:translateX(-50%); opacity:0; transition:opacity 0.3s; white-space:nowrap; }
.tooltip:hover .tooltiptext { visibility:visible; opacity:1; }
button#refresh-all { display:block; margin:15px auto; padding:10px 20px; font-size:1em; border:none; border-radius:5px; cursor:pointer; background-color:#007bff; color:#fff; }
button#refresh-all:hover { background-color:#0056b3; }
button#refresh-all.loading { position:relative; pointer-events:none; opacity:0.7; }
button#refresh-all.loading::after { content:''; position:absolute; top:50%; left:50%; width:16px; height:16px; margin:-8px 0 0 -8px; border:2px solid #fff; border-top:2px solid transparent; border-radius:50%; animation:spin 0.8s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
@media (max-width:768px){ .server-container{ flex-direction:column; } .server-card{ max-width:100%; } }
</style>
</head>
<body>
<h1>Minecraft Server Status Dashboard</h1>
<button id="refresh-all">Refresh All</button>
<div class="server-container">
<?php
// Display Bedrock servers
for ($i=1;$i<=9;$i++) {
    $env_var = "MINECRAFT_SERVER$i";
    if ($server = getenv($env_var)) {
        $api_url = "https://api.mcstatus.io/v2/status/bedrock/$server";
        display_server_info("bedrock",$server,$api_url);
    }
}

// Display Java servers
for ($i=1;$i<=9;$i++) {
    $env_var = "JAVA_MINECRAFT_SERVER$i";
    if ($server = getenv($env_var)) {
        $api_url = "https://api.mcstatus.io/v2/status/java/$server";
        display_server_info("java",$server,$api_url);
    }
}
?>
</div>

<script>
document.getElementById('refresh-all').addEventListener('click', function() {
    const btn = this;
    btn.classList.add('loading');
    fetch('refresh.php')
        .then(r=>r.text())
        .then(()=>location.reload())
        .finally(()=>btn.classList.remove('loading'));
});

    // Auto-refresh every 60 seconds (60000 ms)
    setInterval(() => {
        document.getElementById('refresh-all').click();
    }, 60000);
</script>
</body>
</html>
