<?php
// Ensure cache directory exists
$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) {
    if (!mkdir($cache_dir, 0777, true)) {
        die("Failed to create cache directory at $cache_dir");
    }
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

/* Visual enhancements */
.motd { background-color: rgba(255,255,255,0.1); padding:5px 10px; border-radius:5px; margin-bottom:5px; }
.player-bar, .latency-bar { height:12px; border-radius:6px; background-color:#444; margin:5px 0; position:relative; }
.player-bar-fill, .latency-bar-fill { height:100%; border-radius:6px; width:0%; transition: width 0.5s; }
.player-bar-fill.green, .latency-bar-fill.green { background-color:#00ff00; }
.player-bar-fill.yellow, .latency-bar-fill.yellow { background-color:#ffff00; color:#000; }
.player-bar-fill.red, .latency-bar-fill.red { background-color:#ff0000; }
.player-bar-text, .latency-bar-text { position:absolute; width:100%; text-align:center; font-size:0.75em; line-height:12px; color:#fff; font-weight:bold; }

/* Tooltip */
.tooltip { position: relative; display: inline-block; cursor: default; }
.tooltip .tooltiptext { visibility: hidden; width: max-content; max-width: 200px; background-color: rgba(0,0,0,0.8); color: #fff; text-align: center; padding: 4px 8px; border-radius: 5px; font-size: 0.75em; position: absolute; z-index: 10; bottom: 125%; left: 50%; transform: translateX(-50%); opacity: 0; transition: opacity 0.3s; white-space: nowrap; }
.tooltip:hover .tooltiptext { visibility: visible; opacity: 1; }

@media (max-width:768px){ .server-container{ flex-direction:column; } .server-column{ max-width:100%; } }
</style>
</head>
<body>
<h1>Minecraft Server Status Dashboard</h1>
<button id="refresh-all">Refresh All Servers</button>
<div class="server-container" id="dashboard">
<?php
function fetch_api_data($api_url,$cache_ttl=30){
    global $cache_dir;
    $cache_file=$cache_dir.'/'.md5($api_url).'.json';
    if(file_exists($cache_file) && (time()-filemtime($cache_file))<$cache_ttl){
        return ['data'=>json_decode(file_get_contents($cache_file)),'updated_at'=>filemtime($cache_file)];
    }
    $context=stream_context_create(['http'=>['timeout'=>5]]);
    $results=@file_get_contents($api_url,false,$context);
    if($results===false) return null;
    file_put_contents($cache_file,$results);
    return ['data'=>json_decode($results),'updated_at'=>time()];
}

function get_latency_color($ms){
    if($ms<100) return 'green';
    if($ms<250) return 'yellow';
    return 'red';
}

function display_server_info($server,$api_url){
    $fetched=fetch_api_data($api_url);
    $status=$fetched?$fetched['data']:null;
    $updated_at=$fetched?$fetched['updated_at']:time();
    $host=strpos($server,':')!==false?substr($server,0,strpos($server,':')):$server;
    $ipAddress=filter_var($host,FILTER_VALIDATE_IP)?$host:gethostbyname($host);
    $hostname=$host;

    echo '<div class="server-card" data-server="'.htmlspecialchars($server).'" data-api="'.htmlspecialchars($api_url).'">';
    if(!$status||isset($status->error)){
        echo '<div class="status"><span class="dot offline-dot"></span><span class="server-name">'.strtoupper(htmlspecialchars($server)).'</span></div>';
        echo "<p>Failed to retrieve data.</p>";
        echo "<p>IP: ".htmlspecialchars($ipAddress)."</p>";
        echo "<p>Hostname: ".htmlspecialchars($hostname)."</p>";
        echo '<div class="last-updated tooltip" data-timestamp="'.$updated_at.'">Last updated: 0s ago<span class="tooltiptext">Updated at: '.date('Y-m-d H:i:s',$updated_at).'</span></div>';
        echo '</div>'; return;
    }

    $isOnline=!empty($status->online);
    echo '<div class="status"><span class="dot '.($isOnline?'online-dot':'offline-dot').'"></span><span class="server-name">'.strtoupper(htmlspecialchars($server)).'</span></div>';
    echo "<p>IP: ".htmlspecialchars($ipAddress)."</p>";
    echo "<p>Hostname: ".htmlspecialchars($hostname)."</p>";

    if($isOnline){
        $motdRaw=is_array($status->motd->html)?implode("\n",$status->motd->html):$status->motd->html;
        $motdHtml=nl2br(htmlspecialchars($motdRaw));
        echo '<div class="motd">MOTD: '.$motdHtml.'</div>';

        $onlinePlayers = $status->players->online;
        $maxPlayers = $status->players->max;
        $fillPercent = $maxPlayers>0 ? ($onlinePlayers/$maxPlayers)*100 : 0;
        $colorClass=$fillPercent<50?'green':($fillPercent<80?'yellow':'red');
        echo '<div class="player-bar tooltip"><div class="player-bar-fill '.$colorClass.'" style="width:'.$fillPercent.'%;"></div><div class="player-bar-text">'.$onlinePlayers.' / '.$maxPlayers.'</div><span class="tooltiptext">Players online: '.$onlinePlayers.'/'.$maxPlayers.'</span></div>';

        $version_name=isset($status->version->name_clean)?htmlspecialchars($status->version->name_clean)
            :(isset($status->version->name)?htmlspecialchars($status->version->name):'N/A');
        $version_protocol=isset($status->version->protocol)?htmlspecialchars($status->version->protocol):'N/A';
        echo "Version: $version_name<br>";
        echo "Protocol: $version_protocol<br>";

        $latency=isset($status->latency)?round($status->latency):null;
        if($latency!==null){
            $latColor=get_latency_color($latency);
            echo '<div class="latency-bar tooltip"><div class="latency-bar-fill '.$latColor.'" style="width:'.min($latency,500)/5 .'%"></div><div class="latency-bar-text">'.$latency.' ms</div><span class="tooltiptext">Exact latency: '.$latency.' ms</span></div>';
        }
    }

    echo '<div class="last-updated tooltip" data-timestamp="'.$updated_at.'">Last updated: 0s ago<span class="tooltiptext">Updated at: '.date('Y-m-d H:i:s',$updated_at).'</span></div>';
    echo '</div>';
}

// Bedrock servers
for($i=1;$i<=10;$i++){
    $env_var="MINECRAFT_SERVER$i";
    if(getenv($env_var)){
        $server=getenv($env_var);
        $api_url="https://api.mcstatus.io/v2/status/bedrock/".$server;
        display_server_info($server,$api_url);
    }
}

// Java servers
for($i=1;$i<=10;$i++){
    $env_var="JAVA_MINECRAFT_SERVER$i";
    if(getenv($env_var)){
        $server=getenv($env_var);
        $api_url="https://api.mcstatus.io/v2/status/java/".$server;
        display_server_info($server,$api_url);
    }
}
?>
</div>

<script>
function updateRelativeTimes(){
    document.querySelectorAll('.last-updated').forEach(div=>{
        let ts=parseInt(div.dataset.timestamp);
        let diff=Math.floor(Date.now()/1000 - ts);
        let text=diff<60?diff+'s ago':(diff<3600?Math.floor(diff/60)+'m ago':(diff<86400?Math.floor(diff/3600)+'h ago':Math.floor(diff/86400)+'d ago'));
        div.childNodes[0].textContent='Last updated: '+text;
    });
}
setInterval(updateRelativeTimes,1000);
updateRelativeTimes();

async function refreshAllServers(){
    const btn=document.getElementById('refresh-all');
    btn.classList.add('loading');
    const cards=Array.from(document.querySelectorAll('.server-card'));
    const fetchPromises=cards.map(card=>{
        const server=card.dataset.server;
        const api=card.dataset.api;
        card.innerHTML='<div class="status">Refreshing...</div>';
        return fetch('refresh.php?api='+encodeURIComponent(api))
            .then(res=>res.text())
            .then(html=>{ card.outerHTML=html; })
            .catch(()=>{ card.innerHTML='<div class="status"><span class="dot offline-dot"></span>'+server+' (failed)</div>'; });
    });
    await Promise.all(fetchPromises);
    btn.classList.remove('loading');
}
document.getElementById('refresh-all').addEventListener('click', refreshAllServers);
setInterval(refreshAllServers, 60000);
</script>
</body>
</html>
