<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

class TTIMDB
{
    private ?DOMXPath $xpath = null;

    private $_nodes = ['https://www.omdbapi.com/?apikey=your_own_omdbapi_key&i=%s'];

    private function initXPath(string $urlOrId): bool {
    $imdbId = preg_match('#(tt\d+)#', $urlOrId, $m) ? $m[1] : $urlOrId;
    $imdbUrl = "https://www.imdb.com/title/$imdbId/";

    $ch = curl_init($imdbUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_USERAGENT => "Mozilla/5.0"
    ]);

    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) return false;

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    libxml_clear_errors();
    $this->xpath = new DOMXPath($doc);
    return true;
}


    public function get($uri = null)
    {
        if (preg_match('#tt(\d+)#', $uri, $m)) {
            return $this->_try($m[0]);
        }
        return false;
    }

    private function _try($id)
    {
        for ($i = 0; $i < count($this->_nodes); $i++) {
            if ($data = $this->_request($id, $i)) {
                return $data;
            }
        }
        return false;
    }

    private function _request($id, $i)
    {
        $ch = curl_init();
        if ($ch) {
            curl_setopt($ch, CURLOPT_URL, sprintf($this->_nodes[$i], $id));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'TT API Client v1.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($ch);
            curl_close($ch);
            return !empty($data) ? $this->_parse($data) : false;
        }
        return false;
    }

private function _parse($data)
{
    $info = json_decode($data);

    if (!$info || isset($info->Error)) {
        return false;
    }

    // Runtime
    if (!empty($info->Runtime) && $info->Runtime != "N/A") {
        $info->Runtime = $info->Runtime;
    } else {
        $info->Runtime = null;
    }

    // Director
    if (!empty($info->Director) && $info->Director != "N/A") {
        $info->Director = $info->Director;
    } else {
        $info->Director = null;
    }

    // Writer
    if (!empty($info->Writer) && $info->Writer != "N/A") {
        $info->Writer = $info->Writer;
    } else {
        $info->Writer = null;
    }

    return $info;
}
function tt_runtime($imdb)
{
    $data = tt_get_imdb_cache($imdb);

    if ($data && !empty($data->Runtime) && $data->Runtime !== "N/A") {
        return $data->Runtime;
    }

    return "N/A";
}
function tt_genre($imdb)
{
    $data = tt_get_imdb_cache($imdb);

    if ($data && !empty($data->Genre)) {
        return $data->Genre;
    }

    return "N/A";
}




    //===| Helpers for torrent-details.php compatibility
    public function getImage($poster, $id) {
        return (!empty($poster) && $poster !== "N/A")
            ? $poster
            : "images/imdb/no-poster.png";
    }

    public function getRating($rating) {
        return (!empty($rating) && $rating !== "N/A")
            ? $rating . "/10"
            : null;
    }

public function renderStars10($rating) {
    if (empty($rating) || $rating === "N/A") {
        return '<span class="no-rating">Not rated</span>';
    }

    $stars = round($rating);
    $html = '<div class="stars">';
    for ($i = 1; $i <= 10; $i++) {
        $html .= ($i <= $stars)
            ? '<span class="star full">&#9733;</span>'
            : '<span class="star empty">&#9734;</span>';
    }
    $html .= " <span class=\"rating-text\">($rating/10)</span></div>";
    return $html;
}


    public function getRated($rated) {
        return (!empty($rated) && $rated !== "N/A")
            ? $rated
            : "Unrated";
    }

    public function getReleased($released) {
        return (!empty($released) && $released !== "N/A")
            ? $released
            : "Unknown";
    }

    public function getUpdated($timestamp) {
        return !empty($timestamp)
            ? date("d-m-Y H:i", $timestamp)
            : "n/A";
    }

// ===| Cast Images от IMDb
public function getCastImages(string $urlOrId, int $iLimit = 6): array {

    $imdbId = preg_match('#(tt\d+)#', $urlOrId, $m) ? $m[1] : $urlOrId;

    if ($this->xpath === null && !$this->initXPath($imdbId)) {

        return [];
    }

    $images = [];
    $nodes = $this->xpath->query('//section[@data-testid="title-cast"]//img[@alt]');
    if (!$nodes || $nodes->length === 0) return [];

    $count = 0;
    foreach ($nodes as $img) {
        $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
        $alt = $img->getAttribute('alt') ?: "Unknown";
        $images[$alt] = $src ?: "images/nocover.jpg";
        if (++$count >= $iLimit) break;
    }
    return $images;
}

// ===| Trailer от TMDb + YouTube fallback
public function getTrailerUrl(string $urlOrId): ?string {

    $imdbId = preg_match('#(tt\d+)#', $urlOrId, $m) ? $m[1] : $urlOrId;

    $tmdbApiKey    = "your_own_tmdbapi_key";
    $youtubeApiKey = "your_own_youtube_key";

    // TMDb find
    $findUrl = "https://api.themoviedb.org/3/find/$imdbId?api_key={$tmdbApiKey}&external_source=imdb_id";
    $data    = $this->fetchJson($findUrl);

    $tmdbId = null;
    $type   = null;
    $title  = $imdbId;

    if (!empty($data['movie_results'][0]['id'])) {
        $tmdbId = $data['movie_results'][0]['id'];
        $type   = 'movie';
        $title  = $data['movie_results'][0]['title'] ?? $title;
    } elseif (!empty($data['tv_results'][0]['id'])) {
        $tmdbId = $data['tv_results'][0]['id'];
        $type   = 'tv';
        $title  = $data['tv_results'][0]['name'] ?? $title;
    }

    // TMDb videos
    if ($tmdbId && $type) {
        $videos = $this->fetchJson("https://api.themoviedb.org/3/$type/$tmdbId/videos?api_key={$tmdbApiKey}");
        if (!empty($videos['results'])) {
            foreach ($videos['results'] as $video) {
                if ($video['site'] === 'YouTube' && !empty($video['key'])) {
                    $vType = strtolower($video['type']);
                    if (in_array($vType, ['trailer','teaser'])) {
                        $videoId = $video['key'];

                        $videoInfoUrl = "https://www.googleapis.com/youtube/v3/videos?" . http_build_query([
                            "key"  => $youtubeApiKey,
                            "id"   => $videoId,
                            "part" => "status"
                        ]);
                        $info = $this->fetchJson($videoInfoUrl);

                        if (!empty($info['items'][0]['status']['privacyStatus']) &&
                            $info['items'][0]['status']['privacyStatus'] === 'public') {
                            return "https://www.youtube.com/watch?v=" . $videoId;
                        }
                    }
                }
            }
        }
    }

  // YouTube fallback imdbId
    $youtubeSearch = "https://www.googleapis.com/youtube/v3/search?" . http_build_query([
        "key"       => $youtubeApiKey,
        "q"         => $title . " trailer",
        "part"      => "snippet",
        "type"      => "video",
        "maxResults"=> 5, // blago
    ]);
    $yt = $this->fetchJson($youtubeSearch);

    if (!empty($yt['items'])) {
        foreach ($yt['items'] as $item) {
            if (!empty($item['id']['videoId'])) {
                $videoId = $item['id']['videoId'];

                $videoInfoUrl = "https://www.googleapis.com/youtube/v3/videos?" . http_build_query([
                    "key"  => $youtubeApiKey,
                    "id"   => $videoId,
                    "part" => "status"
                ]);
                $info = $this->fetchJson($videoInfoUrl);

                if (!empty($info['items'][0]['status']['privacyStatus']) &&
                    $info['items'][0]['status']['privacyStatus'] === 'public') {
                    return "https://www.youtube.com/watch?v=" . $videoId;
                }
            }
        }
    }

    return null;
}

// ===| Helper fetch
private function fetchJson(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $res = curl_exec($ch);

    if ($res === false) {
        error_log("fetchJson: curl error for $url -> " . curl_error($ch));
        curl_close($ch);
        return [];
    }

    curl_close($ch);

    $decoded = json_decode($res, true);
    if (!is_array($decoded)) {
        error_log("fetchJson: invalid JSON for $url -> " . substr($res, 0, 200));
        return [];
    }

    return $decoded;
  }
}
?>