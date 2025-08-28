<?php
// Ensure cache directory exists
$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

function parse_server_env($prefix, $type) {
    $servers = [];
    for ($i = 1; $i <= 9; $i++) {
        $env = getenv($prefix . $i);
        if ($env && trim($env) !== '') {
            if (strpos($env, ':') !== false) {
                [$host, $port] = explode(':', $env, 2);
            } else {
                $host = $env;
                $port = ($type === "bedrock") ? 19132 : 25565;
            }
            $servers[] = [
                'host' => $host,
                'port' => (int)$port,
                'type' => $type
            ];
        }
    }
    return $servers;
}

$bedrock_servers = parse_server_env("MINECRAFT_SERVER", "bedrock");
$java_servers = parse_server_env("JAVA_MINECRAFT_SERVER", "java");
$all_servers = array_merge($bedrock_servers, $java_servers);

function fetch_server_data($server) {
    global $cache_dir;
    $host = $server['host'];
    $port = $server['port'];
    $type = $server['type'];

    $cache_file = "$cache_dir/{$type}_{$host}_{$port}.json";
    $cache_ttl = 60; // 1 min

    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
        $json = file_get_contents($cache_file);
    } else {
        $url = "https://api.mcstatus.io/v2/status/{$type}/{$host}:{$port}";
        $json = @file_get_contents($url);
        if ($json) {
            file_put_contents($cache_file, $json);
        } else {
            $json = '{"online":false}';
        }
    }
    return json_decode($json, true);
}

function display_server_info($server, $data) {
    $host = htmlspecialchars($server['host']);
    $port = $server['port'];
    $type = ucfirst($server['type']);

    echo '<div class="server-card">';
    echo "<h2>{$host}:{$port} ({$type})</h2>";

    if (!empty($data['online']) && $data['online']) {
        echo '<p><span class="dot online-dot"></span> Online</p>';

        // MOTD
        if (!empty($data['motd']['raw'])) {
            echo '<div class="motd">' . htmlspecialchars($data['motd']['raw']) . '</div>';
        }

        // Version & Protocol
        if (!empty($data['version']['name'])) {
            echo '<p>Version: ' . htmlspecialchars($data['version']['name']) . '</p>';
        }
        if (!empty($data['version']['protocol'])) {
            echo '<p>Protocol: ' . htmlspecialchars($data['version']['protocol']) . '</p>';
        }

        // Player bar
        $online = $data['players']['online'] ?? 0;
        $max = $data['players']['max'] ?? 0;
        $fillPercent = $max > 0 ? ($online / $max * 100) : 0;
        $colorClass = $fillPercent < 50 ? 'green' : ($fillPercent < 80 ? 'yellow' : 'red');
        echo '<div class="player-bar tooltip">';
        echo '<div class="player-bar-fill '.$colorClass.'" style="width:'.$fillPercent.'%;"></div>';
        echo '<div class="player-bar-text">'.$online.' / '.$max.'</div>';
        echo '<span class="tooltiptext">Players online: '.$online.'/'.$max.'</span></div>';

        // Latency bar
        if (isset($data['latency'])) {
            $latency = $data['latency'];
            $latencyPercent = min(100, max(0, 100 - ($latency / 5)));
            $latencyColor = $latency < 150 ? 'green' : ($latency < 300 ? 'yellow' : 'red');
            echo '<div class="latency-bar tooltip">';
            echo '<div class="latency-bar-fill '.$latencyColor.'" style="width:'.$latencyPercent.'%;"></div>';
            echo '<div class="latency-bar-text">'.$latency.' ms</div>';
            echo '<span class="tooltiptext">Latency: '.$latency.' ms</span></div>';
        }
    } else {
        echo '<p><span class="dot offline-dot"></span> Offline</p>';
    }

    echo '<button class="refresh-btn" data-host="'.$host.'" data-port="'.$port.'" data-type="'.$server['type'].'">Refresh</button>';
    echo '</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Minecraft Server Status</title>
<style>
body { font-family: Arial, sans-serif; background:#222; color:#fff; }
h1 { text-align:center; }
.server-container { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; }
.server-card { background:#333; padding:15px; border-radius:8px; width:300px; }
.player-bar, .latency-bar {
  height:20px; border-radius:10px; background:#444; margin:5px 0; position:relative; min-width:150px;
}
.player-bar-fill.green, .latency-bar-fill.green { background:#0f0; }
.player-bar-fill.yellow, .latency-bar-fill.yellow { background:#ff0; }
.player-bar-fill.red, .latency-bar-fill.red { background:#f00; }
.player-bar-text, .latency-bar-text {
  position:absolute; width:100%; text-align:center; line-height:20px; color:#fff; font-weight:bold; font-size:0.85em; white-space:nowrap;
}
.tooltip { position:relative; display:block; }
.tooltip .tooltiptext {
  visibility:hidden; background:rgba(0,0,0,0.8); color:#fff; text-align:center; padding:4px 8px; border-radius:5px;
  position:absolute; z-index:10; bottom:125%; left:50%; transform:translateX(-50%);
  opacity:0; transition:opacity 0.3s; white-space:nowrap;
}
.tooltip:hover .tooltiptext { visibility:visible; opacity:1; }
.refresh-btn { margin-top:10px; padding:5px 10px; }
</style>
</head>
<body>
<h1>Minecraft Server Status</h1>
<div class="server-container">
<?php
foreach ($all_servers as $server) {
    $data = fetch_server_data($server);
    display_server_info($server, $data);
}
?>
</div>
<script>
document.querySelectorAll(".refresh-btn").forEach(btn=>{
  btn.addEventListener("click", ()=>{
    btn.textContent = "Refreshing...";
    fetch("refresh.php?host="+btn.dataset.host+"&port="+btn.dataset.port+"&type="+btn.dataset.type)
      .then(res=>res.text())
      .then(html=>{
        btn.parentElement.outerHTML = html;
      });
  });
});
</script>
</body>
</html>
