<?php
// Ensure cache directory exists
$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Example function to display one server (you can call it multiple times)
function display_server_info($hostname, $apiUrl, $serverName) {
    // Fetch server data
    $response = @file_get_contents($apiUrl);
    $data = $response ? json_decode($response, true) : null;

    $online = $data && isset($data['online']) && $data['online'] ? true : false;
    $onlinePlayers = $online && isset($data['players']['online']) ? $data['players']['online'] : 0;
    $maxPlayers = $online && isset($data['players']['max']) ? $data['players']['max'] : 0;
    $version = $online && isset($data['version']['name']) ? $data['version']['name'] : 'N/A';
    $protocol = $online && isset($data['version']['protocol']) ? $data['version']['protocol'] : 'N/A';
    $motd = $online && isset($data['motd']['clean'][0]) ? $data['motd']['clean'][0] : 'Server offline';
    $latency = $online && isset($data['debug']['ping']) ? $data['debug']['ping'] : 0;

    // Player bar %
    $fillPercent = ($maxPlayers > 0) ? ($onlinePlayers / $maxPlayers) * 100 : 0;
    $fillPercent = min(100, max(0, $fillPercent));

    // Latency %
    $latencyPercent = ($latency > 0) ? min(100, $latency) : 0;

    // Colors
    $playerColor = ($fillPercent < 50) ? 'green' : (($fillPercent < 80) ? 'yellow' : 'red');
    $latencyColor = ($latency < 100) ? 'green' : (($latency < 200) ? 'yellow' : 'red');

    echo '<div class="server-card">';
    echo '<h2>' . strtoupper($serverName) . '</h2>';
    echo '<div class="motd">' . htmlspecialchars($motd) . '</div>';
    echo '<p><strong>IP:</strong> ' . $_SERVER['SERVER_ADDR'] . '</p>';
    echo '<p><strong>Hostname:</strong> ' . htmlspecialchars($hostname) . '</p>';
    echo '<p><strong>Port:</strong> 19132</p>';
    echo '<p><strong>Version:</strong> ' . $version . '</p>';
    echo '<p><strong>Protocol:</strong> ' . $protocol . '</p>';

    // Player bar
    echo '<div class="player-bar tooltip">';
    echo '<div class="player-bar-fill ' . $playerColor . '" style="width:' . $fillPercent . '%;"></div>';
    echo '<div class="player-bar-text">' . $onlinePlayers . ' / ' . $maxPlayers . '</div>';
    echo '<span class="tooltiptext">Players online: ' . $onlinePlayers . '/' . $maxPlayers . '</span>';
    echo '</div>';

    // Latency bar
    echo '<div class="latency-bar tooltip">';
    echo '<div class="latency-bar-fill ' . $latencyColor . '" style="width:' . $latencyPercent . '%;"></div>';
    echo '<div class="latency-bar-text">' . $latency . ' ms</div>';
    echo '<span class="tooltiptext">Latency: ' . $latency . ' ms</span>';
    echo '</div>';

    echo '</div>'; // end card
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
button#refresh-all { display:block; margin:10px auto; padding:10px 20px; font-size:1em; border:none; border-radius:5px; cursor:pointer; background-color:#007bff; color:#fff; }
button#refresh-all:hover { background-color:#0056b3; }
button#refresh-all.loading { position: relative; pointer-events: none; opacity:0.7; }
button#refresh-all.loading::after {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    width: 16px; height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid #fff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
.server-container { display:flex; justify-content:space-between; padding:20px; gap:10px; flex-wrap:wrap; }
.server-column { flex:1; min-width:300px; max-width:48%; background-color:rgba(0,0,0,0.6); padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.8); }
.server-card { margin:15px 0; padding:15px; border-radius:8px; background-color:rgba(0,0,0,0.5); box-shadow:0 0 8px rgba(0,0,0,0.7); }
.status { font-weight:bold; margin-bottom:10px; }
.dot { height:12px; width:12px; border-radius:50%; display:inline-block; margin-right:10px; vertical-align:middle; }
.dot:hover { opacity:0.7; }
.online-dot { background-color:#00ff00; }
.offline-dot { background-color:#ff0000; }
.last-updated { font-size:0.85em; color:#ccc; margin-top:10px; }

/* Bars */
.player-bar, .latency-bar {
    height:20px;
    border-radius:10px;
    background-color:#444;
    margin:5px 0 10px 0;
    position: relative;
}
.player-bar-fill, .latency-bar-fill {
    height:100%;
    border-radius:10px;
    width:0%;
    transition: width 0.5s;
}
.player-bar-fill.green, .latency-bar-fill.green { background-color:#00ff00; }
.player-bar-fill.yellow, .latency-bar-fill.yellow { background-color:#ffff00; color:#000; }
.player-bar-fill.red, .latency-bar-fill.red { background-color:#ff0000; }
.player-bar-text, .latency-bar-text {
    position: absolute;
    width:100%;
    text-align:center;
    line-height:20px;
    color:#fff;
    font-weight:bold;
    font-size:0.85em;
    pointer-events: none;
}

/* Tooltip */
.tooltip { position: relative; display: inline-block; cursor: default; }
.tooltip .tooltiptext { visibility: hidden; width: max-content; max-width: 200px; background-color: rgba(0,0,0,0.8); color: #fff; text-align: center; padding: 4px 8px; border-radius: 5px; font-size: 0.75em; position: absolute; z-index: 10; bottom: 125%; left: 50%; transform: translateX(-50%); opacity: 0; transition: opacity 0.3s; white-space: nowrap; }
.tooltip:hover .tooltiptext { visibility: visible; opacity: 1; }

@media (max-width:768px){ .server-container{ flex-direction:column; } .server-column{ max-width:100%; } }
</style>
</head>
<body>
<h1>Minecraft Server Status</h1>
<button id="refresh-all">Refresh All</button>
<div class="server-container">
    <div class="server-column">
        <?php display_server_info("minecraft.sokolik.info", "https://api.mcsrvstat.us/2/minecraft.sokolik.info", "Sokolik Minecraft"); ?>
    </div>
    <!-- Add more server-column blocks here if you want multiple -->
</div>
<script>
document.getElementById('refresh-all').addEventListener('click', function() {
    this.classList.add('loading');
    location.reload();
});
</script>
</body>
</html>
