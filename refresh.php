<?php
$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

$host = $_GET['host'] ?? '';
$port = $_GET['port'] ?? '';
$type = $_GET['type'] ?? '';

if (!$host || !$port || !$type) {
    die("Invalid request");
}

$url = "https://api.mcstatus.io/v2/status/{$type}/{$host}:{$port}";
$json = @file_get_contents($url);
if ($json) {
    file_put_contents("$cache_dir/{$type}_{$host}_{$port}.json", $json);
} else {
    $json = '{"online":false}';
}
$data = json_decode($json, true);

function display_server_info($server, $data) {
    $host = htmlspecialchars($server['host']);
    $port = $server['port'];
    $type = ucfirst($server['type']);

    echo '<div class="server-card">';
    echo "<h2>{$host}:{$port} ({$type})</h2>";

    if (!empty($data['online']) && $data['online']) {
        echo '<p><span class="dot online-dot"></span> Online</p>';

        if (!empty($data['motd']['raw'])) {
            echo '<div class="motd">' . htmlspecialchars($data['motd']['raw']) . '</div>';
        }

        if (!empty($data['version']['name'])) {
            echo '<p>Version: ' . htmlspecialchars($data['version']['name']) . '</p>';
        }
        if (!empty($data['version']['protocol'])) {
            echo '<p>Protocol: ' . htmlspecialchars($data['version']['protocol']) . '</p>';
        }

        $online = $data['players']['online'] ?? 0;
        $max = $data['players']['max'] ?? 0;
        $fillPercent = $max > 0 ? ($online / $max * 100) : 0;
        $colorClass = $fillPercent < 50 ? 'green' : ($fillPercent < 80 ? 'yellow' : 'red');
        echo '<div class="player-bar tooltip">';
        echo '<div class="player-bar-fill '.$colorClass.'" style="width:'.$fillPercent.'%;"></div>';
        echo '<div class="player-bar-text">'.$online.' / '.$max.'</div>';
        echo '<span class="tooltiptext">Players online: '.$online.'/'.$max.'</span></div>';

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

    echo '<button class="refresh-btn" data-host="'.$host.'" data-port="'.$port.'" data-type="'.strtolower($type).'">Refresh</button>';
    echo '</div>';
}

$server = ['host'=>$host, 'port'=>$port, 'type'=>$type];
display_server_info($server, $data);
