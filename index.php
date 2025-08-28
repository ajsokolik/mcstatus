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
    $status = fetch_api_data($api_url);

    // Parse host and port
    $host = strpos($server, ':') !== false ? explode(':', $server)[0] : $server;
    $port = strpos($server, ':') !== false ? explode(':', $server)[1] : ($type === 'bedrock' ? 19132 : 25565);
    $ipAddress = gethostbyname($host);
    $hostname = @gethostbyaddr($ipAddress) ?: $host;

    $statusDot = ($status && isset($status->online) && $status->online) ? '<span class="dot online-dot"></span>' : '<span class="dot offline-dot"></span>';

    echo '<div class="server-card">';
    echo '<div class="status">' . $statusDot . strtoupper(htmlspecialchars($server)) . '</div>';

    if (!$status || isset($status->error) || !$status->online) {
        echo '<p>IP: ' . htmlspecialchars($ipAddress) . '<br>';
        echo 'Hostname: ' . htmlspecialchars($hostname) . '<br>';
        echo 'Port: ' . htmlspecialchars($port) . '<br>';
        echo '<em>Offline or failed to retrieve data</em></p>';
    } else {
        // MOTD
        $motdHtml = is_array($status->motd->clean) ? implode('<br>', $status->motd->clean) : $status->motd->clean;
        echo '<p class="motd">' . $motdHtml . '</p>';

        echo '<p>IP: ' . htmlspecialchars($ipAddress) . '<br>';
        echo 'Hostname: ' . htmlspecialchars($hostname) . '<br>';
        echo 'Port: ' . htmlspecialchars($status->port) . '<br>';
        $version_name = $status->version->name_clean ?? $status->version->name ?? 'N/A';
        echo 'Version: ' . htmlspecialchars($version_name) . '<br>';
        echo 'Protocol: ' . htmlspecialchars($status->version->protocol ?? 'N/A') . '</p>';

        // Player bar
        $onlinePlayers = $status->players->online ?? 0;
        $maxPlayers = $status->players->max ?? 1;
        $fillPercent = min(100, ($onlinePlayers / $maxPlayers) * 100);
        $colorClass = $fillPercent < 50 ? 'red' : ($fillPercent < 80 ? 'yellow' : 'green');

        echo '<div class="player-bar tooltip">';
        echo '<div class="player-bar-fill ' . $colorClass . '" style="width:' . $fillPercent . '%;"></div>';
        echo '<div class="player-bar-text">' . $onlinePlayers . ' / ' . $maxPlayers . '</div>';
        echo '<span class="tooltiptext">Players online: ' . $onlinePlayers . '/' . $maxPlayers . '</span>';
        echo '</div>';
    }

    echo '</div>'; // server-card
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
</script>
</body>
</html>
