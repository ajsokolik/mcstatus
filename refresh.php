<?php
$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) { mkdir($cache_dir, 0777, true); }
if(!isset($_GET['api'])){ http_response_code(400); echo "Missing API parameter."; exit; }
$api_url=$_GET['api'];

function fetch_api_data($api_url,$cache_ttl=0){
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

function render_server_card($api_url){
    $fetched=fetch_api_data($api_url);
    $status=$fetched?$fetched['data']:null;
    $updated_at=$fetched?$fetched['updated_at']:time();
    $parts=explode('/',$api_url);
    $server=end($parts);
    $host=strpos($server,':')!==false?substr($server,0,strpos($server,':')):$server;
    $ipAddress=filter_var($host,FILTER_VALIDATE_IP)?$host:gethostbyname($host);
    $hostname=$host;

    echo '<div class="server-card" data-server="'.htmlspecialchars($server).'" data-api="'.htmlspecialchars($api_url).'">';
    if(!$status||isset($status->error)){
        echo '<div class="status"><span class="dot offline-dot"></span><span class="server-name">'.strtoupper(htmlspecialchars($server)).'</span></div>';
        echo "<p>Failed to retrieve data.</p>";
        echo "<p>IP: ".htmlspecialchars($ipAddress)."</p>";
        echo "<p>Hostname: ".htmlspecialchars($hostname)."</p>";
        echo '<div class="last-updated tooltip" data-timestamp="'.$updated_at.'">Last updated: 0s ago<span class="tooltiptext">Updated at: '.date('Y-m-d H:i:s',$updated_at).'</span></div></div>'; return;
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

render_server_card($api_url);
?>
