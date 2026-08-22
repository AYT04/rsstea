<?php
$urls = file('feeds.txt', FILE_IGNORE_NEW_LINES);
$postLimit = 5;
$result = '';
$feeda = array();
$feedn = 0;
foreach ($urls as $url) {
    echo "\n"."Getting feed from: ". $url." \n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
        CURLOPT_REFERER => 'https://www.bing.com/',
        CURLOPT_TIMEOUT_MS => 7000,
        CURLOPT_ENCODING => 'gzip',
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch) || $httpcode != 200) {
         echo "\n"."Error loading feed from " . $url . " " . $httpcode . " " . curl_error($ch) ." \n ";
         $feedn++;
        continue;
    }
    curl_close($ch);

    $response = str_ireplace(array("media:thumbnail",'<media:group>','</media:group>'), array("thumbnail",'',''), $response);
    $feed = simplexml_load_string($response);
    if ($feed) {
        $feedType = strtolower($feed->getName());
        $count = 0;
        if ($feedType === 'rss') {
            foreach ($feed->channel->item as $item) {
                if ($count >= $postLimit) {
                    break;
                }
                $description = (string)$item->description;
                $image = null;
                if (isset($item->image) && isset($item->image->url)) {
                    $image = (string)$item->image->url;
                } else if (isset($item->thumbnail) && isset($item->thumbnail->attributes()['url'])) {
                    $image = (string)$item->thumbnail->attributes()['url'];
                } else {   
                    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $description, $imagematch);
                    if ($imagematch && isset($imagematch['src'])) {
                        $image = $imagematch['src'];
                    } else if (isset($feed->channel->image) && isset($feed->channel->image->url)) {
                        $image = (string)$feed->channel->image->url;
                    }
                }
                                 
                $audiourl = null;
                if (isset($item->enclosure) && isset($item->enclosure['url']) && isset($item->enclosure['type']) && strpos($item->enclosure['type'], 'audio/mpeg') !== false) {
                    $audiourl = (string)$item->enclosure['url'];
                }

                $feeda[$feedn]['link'] = (string) $item->link;
                $feeda[$feedn]['title'] = (string) $item->title;
                if(empty($feeda[$feedn]['title'])) {$feeda[$feedn]['title'] = substr(strip_tags($description),0,140);}
                $feeda[$feedn]['ch'] = (string) $feed->channel->title;
                $feeda[$feedn]['date'] = strtotime((string) $item->pubDate);
                  
                $feeda[$feedn]['image'] = (string) $image;
                $feeda[$feedn]['audio'] = (string) $audiourl;
                $count++; $feedn++;
            }
        } elseif ($feedType === 'feed') {
            foreach ($feed->entry as $entry) {
                if ($count >= $postLimit) {
                    break;
                }
                $content = (string)$entry->content;
                $image = null;
                if (isset($entry->image) && isset($entry->image->url)) {
                    $image = (string)$entry->image->url;
                } elseif (isset($entry->{'thumbnail'}) && isset($entry->{'thumbnail'}->attributes()['url'])) {
                    $image = (string) $entry->{'thumbnail'}->attributes()['url'];
                } else {
                    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $imagematch);
                    $image = ($imagematch && isset($imagematch['src'])) ? $imagematch['src'] : (isset($feed->image) && isset($feed->image->url) ? (string)$feed->image->url : null);
                }
                $audiourl = null;
                if (isset($entry->link) && isset($entry->link['rel']) && $entry->link['rel'] == 'enclosure' && isset($entry->link['href']) && isset($entry->link['type']) && strpos($entry->link['type'], 'audio/mpeg') !== false) {
                    $audiourl = (string)$entry->link['href'];
                }

                $feeda[$feedn]['link'] = (string) $entry->link['href'];
                $feeda[$feedn]['title'] = (string) $entry->title;
                $feeda[$feedn]['ch'] = (string) $feed->title;
                $feeda[$feedn]['date'] = strtotime((string) ($entry->published ?? $entry->updated));
                $feeda[$feedn]['image'] = (string) $image;
                $feeda[$feedn]['audio'] = (string) $audiourl;

                $count++; $feedn++;
            }
        }
    } else {
         echo 'Failed to parse feed from ' . $url;
         $feedn++;
    }
}
usort($feeda, fn($a, $b) => $b['date'] <=> $a['date']);
$outhtml = '';
$outoptions = '<option value="All">All Channels</option>';
$outchannels = array();
$index = 0;

foreach($feeda as $post) {
    if(!in_array($post['ch'], $outchannels)) {
        $outoptions .= '<option value="'.htmlspecialchars($post['ch']).'">'.htmlspecialchars($post['ch']).'</option>';
        $outchannels[] = $post['ch'];
    }
    $isaudio = !empty($post['audio']) ? 1 : 0;

    $chInitial = strtoupper(substr($post['ch'], 0, 1));
    $domain = parse_url($post['link'], PHP_URL_HOST);
    $imgSrc = !empty($post['image']) ? $post['image'] : 'https://s2.googleusercontent.com/s2/favicons?domain='.urlencode($domain);

    $outhtml .= '<div class="post video-card" data-channel="'.htmlspecialchars($post['ch']).'" data-ts="'.$post['date'].'" data-audio="'.$isaudio.'">';
    $outhtml .= '  <div class="thumbnail-wrapper">';
    $outhtml .= '    <a href="'.htmlspecialchars($post['link']).'" target="_blank" class="thumbnail-link">';
    $outhtml .= '      <img src="'.htmlspecialchars($imgSrc).'" alt="'.htmlspecialchars($post['title']).'" class="thumbnail-img" />';
    if ($isaudio) {
        $outhtml .= '      <span class="badge podcast-badge">PODCAST</span>';
    } else {
        $outhtml .= '      <span class="badge post-badge">POST</span>';
    }
    $outhtml .= '    </a>';

    if($isaudio){
        $outhtml .= '    <div class="audio">';
        $outhtml .= '      <button class="audio-play-btn" data-aid="'.$index.'" title="Play Audio">';
        $outhtml .= '        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
        $outhtml .= '        <span>Play</span>';
        $outhtml .= '      </button>';
        $outhtml .= '      <audio src="'.htmlspecialchars($post['audio']).'" preload="metadata" aid="'.$index.'"></audio>';
        $outhtml .= '    </div>';
        $index++;
    }

    $outhtml .= '  </div>'; // end thumbnail-wrapper

    $outhtml .= '  <div class="card-details">';
    $outhtml .= '    <div class="channel-avatar"><span>'.$chInitial.'</span></div>';
    $outhtml .= '    <div class="card-meta">';
    $outhtml .= '      <h2 class="video-title"><a href="'.htmlspecialchars($post['link']).'" target="_blank">'.htmlspecialchars($post['title']).'</a></h2>';
    $outhtml .= '      <div class="feedname"><span class="channel channel-name">'.htmlspecialchars($post['ch']).'</span></div>';
    $outhtml .= '      <div class="video-info-line"><span class="date">'.date('M d, Y', $post['date']).'</span></div>';
    $outhtml .= '    </div>';
    $outhtml .= '  </div>'; // end card-details
    $outhtml .= '</div>'; // end post
}

file_put_contents('feed.json', json_encode($feeda));
file_put_contents('public/feed.json', json_encode($feeda));
$template = file_get_contents('base.html');
$html = str_replace(array('<!-- posts here -->','<!-- options here -->'), array($outhtml, $outoptions), $template);
file_put_contents('public/index.html', $html);
?>
