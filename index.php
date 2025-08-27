<?php
// Ensure cache directory exists
$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Function to fetch data from API
function fetch_api_data($api_url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
        ],
    ]);
    $results = @file_get_contents($api_url, false, $context);
    if ($results === false) return null;
    return json_decode($results);
}

// Function to display server info
function display_server_info($type, $server, $api_url) {
    $status = fetch_api_data($api_url);

    $host = strpos($server, ':') !== false ? substr($server, 0, strpos($server, ':')) : $server;
    $ipAddress = ip2long($host) ? $server : gethostbyname($host);
    $hostname = ip2long($host) ? gethostbyaddr($host) : $host;

    echo '<div class="server-card">';

    if (!$status || isset($status->error) || !$status->online) {
        echo '<div class="status"><span class="dot offline-dot"></span>' . strtoupper(htmlspecialchars($hostname)) . '</div>';
        echo '<p>IP: '.htmlspecialchars($ipAddress).'<br>Hostname: '.htmlspecialchars($hostname).'</p>';
    } else {
        echo '<div class="status"><span class="dot online-dot"></span>' . strtoupper(htmlspecialchars($server)) . '</div>';

        $motdHtml = is_array($status->motd->html) ? implode('<br>', $status->motd->html) : $status->motd->html;
        echo '<div class="motd">'.$motdHtml.'</div>';

        echo '<p>IP: '.htmlspecialchars($ipAddress).'<br>';
        echo 'Hostname: '.htmlspecialchars($hostname).'<br>';
        echo 'Port: '.htmlspecialchars($status->port).'<br>';

        $version_name = isset($status->version->name_clean) ? htmlspecialchars($status->version->name_clean) : (isset($status->version->name) ? htmlspecialchars($status->version->name) : 'N/A');
        $version_protocol = isset($status->version->protocol) ? htmlspecialchars($status->version->protocol) : 'N/A';
        echo 'Version: '.$version_name.'<br>';
        echo 'Protocol: '.$version_protocol.'<br>';

        $onlinePlayers = isset($status->players->online) ? $status->players->online : 0;
        $maxPlayers = isset($status->players->max) ? $status->players->max : 0;
        $fillPercent = $maxPlayers > 0 ? round(($onlinePlayers / $maxPlayers) * 100) : 0;

        if ($onlinePlayers == 0) {
            $colorClass = 'red';
        } elseif ($onlinePlayers / $maxPlayers < 0.5) {
            $colorClass = 'yellow';
        } else {
            $colorClass = 'green';
        }

        echo '<div class="player-bar tooltip">
                <div class="player-bar-fill '.$colorClass.'" style="width:'.$fillPercent.'%;"></div>
                <div class="player-bar-text">'.$onlinePlayers.' / '.$maxPlayers.'</div>
                <span class="tooltiptext">Players online: '.$onlinePlayers.'/'.$maxPlayers.'</span>
              </div>';
    }

    echo '</div>'; // close server-card
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
button#refresh-all.loading::after { content: ''; position: absolute; top: 50%; left: 50%; width: 16px; height: 16px; margin: -8px 0 0 -8px; border: 2px solid #fff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
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
.motd { padding:5px 10px; border-radius:5px; margin-bottom:5px; }
.player-bar, .latency-bar {
    height: 20px;
    border-radius: 10px;
    background-color: #444;
    margin: 5px 0 10px 0;
    position: relative;
    min-width: 120px;   /* prevents tiny wrapping */
}
.player-bar-fill.green, .latency-bar-fill.green { background-color:#00ff00; }
.player-bar-fill.yellow, .latency-bar-fill.yellow { background-color:#ffff00; color:#000; }
.player-bar-fill.red, .latency-bar-fill.red { background-color:#ff0000; }
.player-bar-text, .latency-bar-text {
    position: absolute;
    width: 100%;
    text-align: center;
    line-height: 20px; /* match bar height */
    color: #fff;
    font-weight: bold;
    font-size: 0.85em;
    pointer-events: none;
    white-space: nowrap;      /* prevent line breaks */
}
.tooltip { position: relative; display: inline-block; cursor: default; }
.tooltip .tooltiptext { visibility: hidden; width: max-content; max-width: 200px; background-color: rgba(0,0,0,0.8); color: #fff; text-align: center; padding: 4px 8px; border-radius: 5px; font-size: 0.75em; position: absolute; z-index: 10; bottom: 125%; left: 50%; transform: translateX(-50%); opacity: 0; transition: opacity 0.3s; white-space: nowrap; }
.tooltip:hover .tooltiptext { visibility: visible; opacity: 1; }
@media (max-width:768px){ .server-container{ flex-direction:column; } .server-column{ max-width:100%; } }
</style>
</head>
<body>
<h1>Minecraft Server Status Dashboard</h1>
<div class="server-container">
    <!-- Bedrock Servers -->
    <div class="server-column">
        <h2>Bedrock Servers</h2>
        <?php
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

    <!-- Java Servers -->
    <div class="server-column">
        <h2>Java Servers</h2>
        <?php
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
