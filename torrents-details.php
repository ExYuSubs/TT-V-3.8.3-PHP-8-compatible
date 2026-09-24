<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

require_once("backend/functions.php");
require_once("backend/BDecode.php");
dbconn();

$torrent_dir = $site_config["torrent_dir"];
$nfo_dir = $site_config["nfo_dir"];

//==============================================================
// YouTube trailer fallback
//==============================================================
// use an IP restriction for the web server (not an HTTP referrer restriction).

if (!defined('YOUTUBE_API_KEY')) {
    define('YOUTUBE_API_KEY', '');
}

function tt_get_youtube_trailer($title, $year = '', $apiKey = '', &$apiError = '') {
    $title = trim((string)$title);
    $year  = trim((string)$year);
    $apiKey = trim((string)$apiKey);
    $apiError = '';

    if ($title === '') {
        $apiError = 'Movie title is empty.';
        return '';
    }

    if ($apiKey === '') {
        $apiError = 'YouTube API key is empty.';
        return '';
    }

    // Try the most specific query first. A second, broader query is used only
    // if the first one produces no usable trailer, so normal requests stay cheap.
    $queries = array();
    $q1 = $title;
    if ($year !== '' && preg_match('/^\d{4}$/', $year)) {
        $q1 .= ' ' . $year;
    }
    $q1 .= ' official trailer';
    $queries[] = $q1;

    $q2 = $title;
    if ($year !== '' && preg_match('/^\d{4}$/', $year)) {
        $q2 .= ' ' . $year;
    }
    $q2 .= ' trailer';
    if ($q2 !== $q1) {
        $queries[] = $q2;
    }

    $bestId = '';
    $bestScore = -9999;

    foreach ($queries as $query) {
        $params = array(
            'part' => 'snippet',
            'q' => $query,
            'type' => 'video',
            'maxResults' => 10,
            'videoEmbeddable' => 'true',
            'safeSearch' => 'moderate',
            'key' => $apiKey
        );

        $url = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $response = false;
        $httpCode = 0;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_USERAGENT, 'TorrentTrader YouTube Trailer Lookup/1.1');
            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false && $curlError !== '') {
                $apiError = 'cURL: ' . $curlError;
            }
        } elseif (ini_get('allow_url_fopen')) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'timeout' => 12,
                    'ignore_errors' => true,
                    'header' => "User-Agent: TorrentTrader YouTube Trailer Lookup/1.1\r\n"
                )
            ));
            $response = @file_get_contents($url, false, $context);

            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $headerLine) {
                    if (preg_match('/^HTTP\/\S+\s+(\d+)/i', $headerLine, $m)) {
                        $httpCode = (int)$m[1];
                    }
                }
            }
        } else {
            $apiError = 'Neither cURL nor allow_url_fopen is available on this server.';
        }

        if ($response === false || $response === '') {
            if ($apiError === '') {
                $apiError = 'No response from YouTube API' . ($httpCode ? ' (HTTP ' . $httpCode . ')' : '') . '.';
            }
            continue;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            $apiError = 'Invalid JSON response from YouTube API.';
            continue;
        }

        if (!empty($data['error'])) {
            $reason = '';
            if (!empty($data['error']['errors'][0]['reason'])) {
                $reason = $data['error']['errors'][0]['reason'];
            }
            $message = !empty($data['error']['message']) ? $data['error']['message'] : 'Unknown YouTube API error.';
            $apiError = $message . ($reason ? ' [' . $reason . ']' : '') . ($httpCode ? ' (HTTP ' . $httpCode . ')' : '');
            // Do not waste quota on the broader query if the key/API itself failed.
            break;
        }

        if (empty($data['items'])) {
            continue;
        }

        $normalizedTitle = strtolower($title);

        foreach ($data['items'] as $item) {
            if (empty($item['id']['videoId']) || empty($item['snippet'])) {
                continue;
            }

            $videoId = $item['id']['videoId'];
            $videoTitle = (string)($item['snippet']['title'] ?? '');
            $description = (string)($item['snippet']['description'] ?? '');
            $channelTitle = (string)($item['snippet']['channelTitle'] ?? '');
            $haystack = strtolower($videoTitle . ' ' . $description . ' ' . $channelTitle);
            $score = 0;

            if (strpos($haystack, $normalizedTitle) !== false) {
                $score += 60;
            }

            $words = preg_split('/\s+/', preg_replace('/[^a-z0-9]+/i', ' ', $title));
            foreach ($words as $word) {
                if (strlen($word) >= 3 && strpos($haystack, strtolower($word)) !== false) {
                    $score += 4;
                }
            }

            if (preg_match('/\b(official\s+)?trailer\b/i', $videoTitle)) {
                $score += 40;
            }
            if (preg_match('/\b(teaser|teaser\s+trailer)\b/i', $videoTitle)) {
                $score += 12;
            }
            if (preg_match('/\b(movie|film)\b/i', $videoTitle)) {
                $score += 5;
            }
            if ($year !== '' && strpos($haystack, $year) !== false) {
                $score += 10;
            }

            if (preg_match('/\b(official|pictures|films|studio|entertainment|movies|disney|warner|sony|paramount|universal|lionsgate|20th century|netflix|amazon|prime video|apple tv|a24)\b/i', $channelTitle)) {
                $score += 20;
            }

            if (preg_match('/\b(review|reaction|recap|ending|explained|interview|clip|scene|shorts?|fan[- ]?made|concept|parody|spoiler|breakdown)\b/i', $videoTitle)) {
                $score -= 60;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = $videoId;
            }
        }

        // A strong result from the first query is enough; otherwise continue
        // with the broader query.
        if ($bestScore >= 70) {
            break;
        }
    }

    if ($bestId === '' || $bestScore < 35) {
        if ($apiError === '') {
            $apiError = 'YouTube returned no sufficiently relevant trailer.';
        }
        return '';
    }

    return 'https://www.youtube.com/watch?v=' . rawurlencode($bestId);
}

//check permissions
if ($site_config["MEMBERSONLY"]){
    loggedinonly();

    if($CURUSER["view_torrents"]=="no")
        show_error_msg(T_("ERROR"), T_("NO_TORRENT_VIEW"), 1);
}

//=======| Added for external scrape |========//

//=======| Added for external scrape |========//


//************ DO SOME "GET" STUFF BEFORE PAGE LAYOUT ***************

$id = (int) $_GET["id"];
$scrape = (int)($_GET["scrape"] ?? 0);
if (!is_valid_id($id))
    show_error_msg(T_("ERROR"), T_("THATS_NOT_A_VALID_ID"), 1);

// Server-side tab selection: no JavaScript or CSS state is required.
$activeTab = isset($_GET['tab']) ? (string)$_GET['tab'] : 'details';
$allowedTabs = array('details', 'imdb', 'trailer', 'posters', 'stats', 'comments');
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'details';
}

//GET ALL MYSQL VALUES FOR THIS TORRENT
$res = SQL_Query_exec("SELECT torrents.anon, torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, torrents.nfo, torrents.last_action, torrents.numratings, torrents.name, torrents.imdb, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.external, torrents.poster, torrents.poster2, torrents.announce, torrents.numfiles, torrents.freeleech, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.numratings, categories.name AS cat_name, torrentlang.name AS lang_name, torrentlang.image AS lang_image, categories.parent_cat as cat_parent, users.username, users.privacy FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id");
$row = mysqli_fetch_assoc($res);

//DECIDE IF TORRENT EXISTS
if (!$row || ($row["banned"] == "yes" && $CURUSER["edit_torrents"] == "no"))
    show_error_msg(T_("ERROR"), T_("TORRENT_NOT_FOUND"), 1);

//torrent is availiable so do some stuff

if ($_GET["hit"] ?? false) {
    SQL_Query_exec("UPDATE torrents SET views = views + 1 WHERE id = $id");
    header("Location: torrents-details.php?id=$id");
    die;
}


    stdhead(T_("DETAILS_FOR_TORRENT")." \"" . $row["name"] . "\"");

    if ($CURUSER["id"] == $row["owner"] || $CURUSER["edit_torrents"] == "yes")
        $owned = 1;
    else
        $owned = 0;

//take rating
if (($_GET["takerating"] ?? '') === 'yes'){
    $rating = (int)$_POST['rating'];

    if ($rating <= 0 || $rating > 5)
        show_error_msg(T_("RATING_ERROR"), T_("INVAILD_RATING"), 1);

    $res = SQL_Query_exec("INSERT INTO ratings (torrent, user, rating, added) VALUES ($id, " . $CURUSER["id"] . ", $rating, '".get_date_time()."')");

    if (!$res) {
        if (mysqli_errno($GLOBALS["DBconnector"]) == 1062)
            show_error_msg(T_("RATING_ERROR"), T_("YOU_ALREADY_RATED_TORRENT"), 1);
        else
            show_error_msg(T_("RATING_ERROR"), T_("A_UNKNOWN_ERROR_CONTACT_STAFF"), 1);
    }

    SQL_Query_exec("UPDATE torrents SET numratings = numratings + 1, ratingsum = ratingsum + $rating WHERE id = $id");
    show_error_msg(T_("RATING_SUCCESS"), T_("RATING_THANK")."<br /><br /><a href='torrents-details.php?id=$id'>" .T_("BACK_TO_TORRENT"). "</a>");
}

//take comment add
if (($_GET["takecomment"] ?? '') == 'yes'){
    loggedinonly();
    $body = $_POST['body'];

    if (!$body)
        show_error_msg(T_("RATING_ERROR"), T_("YOU_DID_NOT_ENTER_ANYTHING"), 1);

    SQL_Query_exec("UPDATE torrents SET comments = comments + 1 WHERE id = $id");

    SQL_Query_exec("INSERT INTO comments (user, torrent, added, text) VALUES (".$CURUSER["id"].", ".$id.", '" .get_date_time(). "', " . sqlesc($body).")");

    if (mysqli_affected_rows($GLOBALS["DBconnector"]) == 1)
            show_error_msg(T_("COMPLETED"), T_("COMMENT_ADDED"), 0);
        else
            show_error_msg(T_("ERROR"), T_("UNABLE_TO_ADD_COMMENT"), 0);
}//end insert comment

//START OF PAGE LAYOUT HERE
$char1 = 75; //cut length
$shortname = CutName(htmlspecialchars($row["name"]), $char1);

begin_frame(T_("TORRENT_DETAILS_FOR")." \"" . $shortname . "\"");

// Count comments for the top summary without relying on a possibly stale torrents.comments value.
$commentCount = 0;
$commentCountRes = SQL_Query_exec("SELECT COUNT(*) AS cnt FROM comments WHERE torrent = $id");
if ($commentCountRes && ($commentCountRow = mysqli_fetch_assoc($commentCountRes))) {
    $commentCount = (int)$commentCountRow["cnt"];
}

// Calculate local torrent speed test.
if ($row["leechers"] >= 1 && $row["seeders"] >= 1 && $row["external"] != 'yes'){
    $speedQ = SQL_Query_exec("SELECT (SUM(p.downloaded)) / (UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(added)) AS totalspeed FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND p.torrent = '$id' GROUP BY t.id ORDER BY added ASC LIMIT 15");
    $a = mysqli_fetch_assoc($speedQ);
    $totalspeed = !empty($a["totalspeed"]) ? mksize($a["totalspeed"]) . "/s" : T_("NO_ACTIVITY");
}else{
    $totalspeed = T_("NO_ACTIVITY");
}

// External tracker scrape. This keeps the existing Update Stats functionality,
// but the resulting values are also reflected in the summary shown below.
if ($row["external"] == "yes" && $scrape == 1) {
    $seeders1 = 0;
    $leechers1 = 0;
    $downloaded1 = 0;
    $got_real_stats = false;

    $tres = SQL_Query_exec("SELECT url FROM announce WHERE torrent=$id");

    while ($trow = mysqli_fetch_assoc($tres)) {
        $ann = $trow["url"];

        $tracker = explode("/", $ann);
        $path = array_pop($tracker);
        $oldpath = $path;
        $path = preg_replace("/^announce/", "scrape", $path);
        $tracker = implode("/", $tracker) . "/" . $path;

        if ($oldpath == $path) {
            continue;
        }

        if (preg_match("/thepiratebay.org/i", $tracker) || preg_match("/prq.to/", $tracker)) {
            $tracker = "http://tracker.opentrackr.org/scrape";
        }

        $stats = torrent_scrape_url($tracker, $row["info_hash"]);

        if (
            is_array($stats) &&
            isset($stats['seeds'], $stats['peers'], $stats['downloaded']) &&
            (
                (int)$stats['seeds'] > 0 ||
                (int)$stats['peers'] > 0 ||
                (int)$stats['downloaded'] > 0
            )
        ) {
            $seeders1 = max($seeders1, (int)$stats['seeds']);
            $leechers1 = max($leechers1, (int)$stats['peers']);
            $downloaded1 = max($downloaded1, (int)$stats['downloaded']);
            $got_real_stats = true;

            SQL_Query_exec("
                UPDATE announce SET
                    online='yes',
                    seeders={$stats['seeds']},
                    leechers={$stats['peers']},
                    times_completed={$stats['downloaded']}
                WHERE url=" . sqlesc($ann) . " AND torrent=$id
            ");
        } else {
            SQL_Query_exec("
                UPDATE announce SET online='no'
                WHERE url=" . sqlesc($ann) . " AND torrent=$id
            ");
        }
    }

    // Keep the original fallback behaviour used by this installation.
    if (!$got_real_stats) {
        srand(crc32($id));
        $seeders1 = rand(3, 11);
        $leechers1 = rand(0, 7);
        $downloaded1 = rand(3, 15);
    }

    $seeders1 = max(0, (int)$seeders1);
    $leechers1 = max(0, (int)$leechers1);
    $downloaded1 = max(0, (int)$downloaded1);

    SQL_Query_exec("
        UPDATE torrents SET
            seeders='$seeders1',
            leechers='$leechers1',
            times_completed='$downloaded1',
            last_action='" . get_date_time() . "',
            visible='yes'
        WHERE id='" . $row['id'] . "'
    ");

    // Make the freshly scraped values visible immediately on this page.
    $row["seeders"] = $seeders1;
    $row["leechers"] = $leechers1;
    $row["times_completed"] = $downloaded1;
    $row["last_action"] = get_date_time();
}

// Load the same IMDb data used by the IMDb fieldset so the top poster
// always comes from the IMDb scrape/cache rather than torrents.poster.
$TTIMDB = new TTIMDB;
$_data = false;
if (!empty($row['imdb'])) {
    if ((($_data = $TTCache->Get("imdb/$id", 900)) === false) && ($_data = $TTIMDB->Get($row['imdb']))) {
        $_data->Poster = $TTIMDB->getImage($_data->Poster, $id);
        if (!isset($_data->imdbTime)) {
            $_data->imdbTime = time();
            $_data->imdbVideo = null;
        }
        $TTCache->Set("imdb/$id", $_data, 900);
    }
}

$posterTop = (is_object($_data) && !empty($_data->Poster)) ? $_data->Poster : '';
$posterTopAlt = htmlspecialchars(
    (is_object($_data) && !empty($_data->Title)) ? $_data->Title : $row["name"],
    ENT_QUOTES,
    'UTF-8'
);

$uploaderName = "Unknown";
$uploaderHtml = "Unknown";
if ($row["anon"] == "yes" && !$owned) {
    $uploaderName = "Anonymous";
    $uploaderHtml = "Anonymous";
} elseif (!empty($row["username"])) {
    $uploaderName = $row["username"];
    $uploaderHtml = "<a href='account-details.php?id=".(int)$row["owner"]."'>".htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8')."</a>";
}

$categoryText = trim(($row["cat_parent"] ?? "") . " > " . ($row["cat_name"] ?? ""), " >");
if ($categoryText === "") {
    $categoryText = "Unknown/NA";
}

$languageText = !empty($row["lang_name"]) ? $row["lang_name"] : "Unknown/NA";
$infoHashText = htmlspecialchars($row["info_hash"] ?? "", ENT_QUOTES, 'UTF-8');

$peerCount = (int)$row["seeders"] + (int)$row["leechers"];
$addedDate = date("d-m-Y H:i:s", utc_to_tz_time($row["added"]));
$lastChecked = date("d-m-Y H:i:s", utc_to_tz_time($row["last_action"]));

?>
<style>
/* Torrent details - compact layout inspired by the supplied screenshot. */
.tt-details-wrap {
    width: 100%;
    margin: 0 auto;
}
.tt-details-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 0 16px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 14px;
}
.tt-details-title {
    margin: 0;
    font-size: 25px;
    font-weight: 600;
}
.tt-details-actions a {
    margin-left: 6px;
}
.tt-summary {
    display: grid;
    grid-template-columns: 300px minmax(0, 1fr);
    gap: 20px;
    margin-bottom: 22px;
}
.tt-summary-poster {
    min-height: 380px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--surface-2);
    border-radius: var(--radius);
}
.tt-summary-poster img {
    display: block;
    width: 100%;
    max-width: 300px;
    max-height: 440px;
    object-fit: cover;
}
.tt-summary-poster .tt-no-poster {
    padding: 50px 20px;
    opacity: .65;
    text-align: center;
}
.tt-summary-info {
    min-width: 0;
}
.tt-summary-table {
    width: 100%;
}
.tt-summary-table td.css {
    width: 170px;
    white-space: nowrap;
}
.tt-summary-table td.css-right {
    overflow-wrap: anywhere;
    text-align: left;
}
.tt-stat-green { color: var(--success); font-weight: 700; }
.tt-stat-red { color: var(--danger); font-weight: 700; }
.tt-download-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
}
.tt-download-row a,
.tt-download-row button,
.tt-scrape-form input[type="submit"] {
    display: inline-block;
    border-radius: 22px;
    padding: 10px 20px;
    text-decoration: none;
    font-weight: 700;
    cursor: pointer;
}
.tt-download-button {
    background: var(--success);
    color: var(--text-on-accent) !important;
}
.tt-secondary-button {
    background: transparent;
    color: var(--text);
    border: 1px solid var(--border-strong);
}
.tt-scrape-form {
    display: inline-block;
    margin: 0;
}
.tt-scrape-form input[type="submit"] {
    border: 1px solid var(--border-strong);
    background: transparent;
    color: var(--text);
}
.tt-section-spacer { margin: 18px 0; }
.tt-trailer {
    margin: 18px 0 24px;
}
.tt-trailer-box {
    background: var(--bg-soft);
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
}
.tt-trailer-box iframe {
    display: block;
    width: 100%;
    min-height: 430px;
    border: 0;
}
.tt-poster-links {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 12px;
    flex-wrap: wrap;
}
.tt-poster-links a {
    padding: 8px 14px;
    border: 1px solid var(--border-strong);
    border-radius: 18px;
    text-decoration: none;
    color: var(--text);
}
.tt-images-grid {
    display: grid;
    grid-template-columns: minmax(0,1fr) minmax(0,1fr);
    gap: 18px;
    align-items: start;
}
.tt-images-grid > div {
    text-align: center;
}
.tt-images-grid img {
    width: 100%;
    max-width: 520px;
    height: auto;
    display: block;
    margin: 0 auto;
}
.tt-file-section,
.tt-peer-section {
    margin-top: 26px;
}

/* Torrent details tabs - server-side state, deliberately no JS/CSS hiding logic */
.tt-tabs { margin-top: 6px; }
.tt-tab-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 0 0 15px;
    padding: 0;
    border-bottom: 1px solid var(--border);
}
.tt-tab-button {
    display: inline-block;
    border: 1px solid var(--border);
    border-bottom: 0;
    background: var(--surface);
    color: inherit;
    padding: 10px 15px;
    border-radius: var(--radius) var(--radius) 0 0;
    cursor: pointer;
    font: inherit;
    font-weight: 600;
    text-decoration: none !important;
    transition: .15s ease;
}
.tt-tab-button:hover { background: var(--accent-soft); }
.tt-tab-button.active {
    background: var(--accent-soft);
    border-color: var(--border-strong);
    box-shadow: inset 0 -2px 0 var(--accent);
}
.tt-tab-button i { margin-right: 5px; }
.tt-tab-panel { display: block; visibility: visible; }
.tt-tab-panel.is-hidden { display: none; }
.tt-tab-panel > :first-child { margin-top: 0; }
.tt-tab-panel .tt-summary { margin-top: 0; }
@media (max-width: 800px) {
    .tt-tab-nav { gap: 4px; }
    .tt-tab-button { padding: 9px 10px; font-size: 13px; }
}

@media (max-width: 800px) {
    .tt-summary { grid-template-columns: 1fr; }
    .tt-summary-poster { min-height: 0; }
    .tt-summary-poster img { max-width: 260px; }
    .tt-images-grid { grid-template-columns: 1fr; }
    .tt-details-head { align-items: flex-start; flex-direction: column; }
}
</style>

<div class="tt-details-wrap">

    <div class="tt-details-head">
        <h1 class="tt-details-title"><?php echo $shortname; ?></h1>
        <div class="tt-details-actions">
            [<a href="report.php?torrent=<?php echo $id; ?>"><b><?php echo T_("REPORT_TORRENT"); ?></b></a>]
            <?php if ($owned): ?>
                [<a href="torrents-edit.php?id=<?php echo $id; ?>&amp;returnto=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>"><b><?php echo T_("EDIT_TORRENT"); ?></b></a>]
            <?php endif; ?>
        </div>
    </div>

    <div class="tt-tabs" id="ttTorrentTabs">
        <div class="tt-tab-nav" role="tablist" aria-label="Torrent information">
            <a href="torrents-details.php?id=<?php echo $id; ?>&amp;tab=details" class="tt-tab-button<?php echo $activeTab === 'details' ? ' active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'details' ? 'true' : 'false'; ?>">
                <i class="fa fa-info-circle"></i>Torrent details
            </a>
            <a href="torrents-details.php?id=<?php echo $id; ?>&amp;tab=imdb" class="tt-tab-button<?php echo $activeTab === 'imdb' ? ' active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'imdb' ? 'true' : 'false'; ?>">
                <i class="fa fa-film"></i>iMDB details
            </a>
            <a href="torrents-details.php?id=<?php echo $id; ?>&amp;tab=trailer" class="tt-tab-button<?php echo $activeTab === 'trailer' ? ' active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'trailer' ? 'true' : 'false'; ?>">
                <i class="fa fa-youtube-play"></i>Trailer
            </a>
            <a href="torrents-details.php?id=<?php echo $id; ?>&amp;tab=posters" class="tt-tab-button<?php echo $activeTab === 'posters' ? ' active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'posters' ? 'true' : 'false'; ?>">
                <i class="fa fa-picture-o"></i>Posters / Screenshots
            </a>
            <a href="torrents-details.php?id=<?php echo $id; ?>&amp;tab=stats" class="tt-tab-button<?php echo $activeTab === 'stats' ? ' active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'stats' ? 'true' : 'false'; ?>">
                <i class="fa fa-bar-chart"></i>Ratings / Files / Trackers
            </a>
            <a href="torrents-details.php?id=<?php echo $id; ?>&amp;tab=comments" class="tt-tab-button<?php echo $activeTab === 'comments' ? ' active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'comments' ? 'true' : 'false'; ?>">
                <i class="fa fa-comments"></i>Comments
            </a>
        </div>

        <div class="tt-tab-panels">
        <div id="tt-tab-details" class="tt-tab-panel" role="tabpanel" style="display:<?php echo $activeTab === 'details' ? 'block' : 'none'; ?>;">
    <div class="tt-summary">
        <div class="tt-summary-poster">
            <?php if ($posterTop): ?>
                <img src="<?php echo htmlspecialchars($posterTop, ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo $posterTopAlt; ?>"
                     title="<?php echo $posterTopAlt; ?>" class="rip">
            <?php else: ?>
                <div class="tt-no-poster"><i class="fa-solid fa-photo-film fa-10x" style="color: var(--accent);"></i> <br> No poster image</div>
            <?php endif; ?>
        </div>

        <div class="tt-summary-info">
            <table class="tt-summary-table" cellpadding="5" cellspacing="0" border="0">
            <tr>
                <td class="css">Category</td>
                <td class="css-right"><?php echo htmlspecialchars($categoryText, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <td class="css">Added</td>
                <td class="css-right"><?php echo $addedDate; ?></td>
            </tr>
            <tr>
                <td class="css">Size</td>
                <td class="css-right"><?php echo mksize($row["size"]); ?></td>
            </tr>
            <tr>
                <td class="css">Peers</td>
                <td class="css-right">
                    <?php echo number_format($peerCount); ?>
                    (<?php echo number_format($row["seeders"]); ?> Seeders and <?php echo number_format($row["leechers"]); ?> Leechers)
                </td>
            </tr>
            <tr>
                <td class="css">Downloaded</td>
                <td class="css-right"><?php echo number_format($row["times_completed"]); ?> times</td>
            </tr>
            <tr>
                <td class="css">Uploader</td>
                <td class="css-right"><?php echo $uploaderHtml; ?></td>
            </tr>
            <tr>
                <td class="css">Comments</td>
                <td class="css-right"><?php echo number_format($commentCount); ?></td>
            </tr>
            <tr>
                <td class="css">Tags</td>
                <td class="css-right">—</td>
            </tr>
            <tr>
                <td class="css">Seeders</td>
                <td class="css-right tt-stat-green"><?php echo number_format($row["seeders"]); ?></td>
            </tr>
            <tr>
                <td class="css">Leechers</td>
                <td class="css-right tt-stat-red"><?php echo number_format($row["leechers"]); ?></td>
            </tr>
            <tr>
                <td class="css"><?php echo T_("LANG"); ?></td>
                <td class="css-right">
                    <?php echo htmlspecialchars($languageText, ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (!empty($row["lang_image"])): ?>
                        &nbsp;<img border="0" src="<?php echo $site_config['SITEURL']; ?>/images/languages/<?php echo htmlspecialchars($row["lang_image"], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($languageText, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="css"><?php echo T_("INFO_HASH"); ?></td>
                <td class="css-right"><?php echo $infoHashText; ?></td>
            </tr>
            <tr>
                <td class="css"><?php echo T_("LAST_CHECKED"); ?></td>
                <td class="css-right"><?php echo $lastChecked; ?></td>
            </tr>
            <tr>
                <td class="css"><?php echo T_("VIEWS"); ?></td>
                <td class="css-right"><?php echo number_format($row["views"]); ?></td>
            </tr>
            <tr>
                <td class="css"><?php echo T_("HITS"); ?></td>
                <td class="css-right"><?php echo number_format($row["hits"]); ?></td>
            </tr>
            <tr>
                <td class="css" valign="top"><?php echo T_("DESCRIPTION"); ?></td>
                <td class="css-right" valign="top"><?php echo format_comment($row["descr"]); ?></td>
            </tr>
            </table>

            <div class="tt-download-row">
                <?php if ($row["banned"] == "yes"): ?>
                    <strong><?php echo T_("DOWNLOAD"); ?>: BANNED!</strong>
                <?php else: ?>
                    <a href="download.php?id=<?php echo $id; ?>&amp;name=<?php echo rawurlencode($row["filename"]); ?>"
                       class="buttonS tt-download-button"><?php echo T_("DOWNLOAD_TORRENT"); ?></a>

                    <?php if ($row["external"] != "yes" && $row["freeleech"] == "1"): ?>
                        <span class="tt-secondary-button" style="padding:10px 20px;">
                            <span style="color: var(--danger);"><?php echo T_("FREE_LEECH_MSG"); ?></span>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($row["external"] == "yes"): ?>
                    <form class="tt-scrape-form" action="torrents-details.php?id=<?php echo $id; ?>&amp;tab=stats&amp;scrape=1" method="post">
                        <input type="submit" value="Update Stats">
                    </form>
                <?php endif; ?>
            </div>

            <div class="tt-section-spacer">
                <b><?php echo T_("HEALTH"); ?>:</b>
                <img src="<?php echo $site_config["SITEURL"]; ?>/images/health/health_<?php echo health($row["leechers"], $row["seeders"]); ?>.gif" alt="">
                &nbsp;&nbsp;
                <?php if ($row["external"] != "yes"): ?>
                    <b><?php echo T_("SPEED"); ?>:</b> <?php echo htmlspecialchars($totalspeed, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </div>

            <?php if ($row["external"] == "yes"): ?>
                <div class="tt-section-spacer">
                    <b>Tracked:</b> EXTERNAL
                    <?php if ($scrape == 1): ?>
                        <br><b><?php echo T_("LIVE_STATS"); ?>:</b>
                        Seeders: <?php echo number_format($row["seeders"]); ?>,
                        Leechers: <?php echo number_format($row["leechers"]); ?>,
                        <?php echo T_("COMPLETED"); ?>: <?php echo number_format($row["times_completed"]); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($row["external"] != "yes" && $row["times_completed"] > 0): ?>
                <div class="tt-section-spacer">
                    [<a href="torrents-completed.php?id=<?php echo $id; ?>"><?php echo T_("WHOS_COMPLETED"); ?></a>]
                    <?php if ($row["seeders"] <= 1): ?>
                        [<a href="torrents-reseed.php?id=<?php echo $id; ?>"><?php echo T_("REQUEST_A_RE_SEED"); ?></a>]
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

        </div><!-- /tt-tab-details -->

        <div id="tt-tab-imdb" class="tt-tab-panel" role="tabpanel" style="display:<?php echo $activeTab === 'imdb' ? 'block' : 'none'; ?>;">
<?php
// The IMDb fieldset below is intentionally kept as a separate, complete block.

   //===| Start IMDb
        $TTIMDB = new TTIMDB;

        if ((($_data = $TTCache->Get("imdb/$id", 900)) === false) && ($_data = $TTIMDB->Get($row['imdb'])))
        {
            $_data->Poster = $TTIMDB->getImage($_data->Poster, $id);
            if ( ! isset( $_data->imdbTime ) )
            {
                $_data->imdbTime = time();
                $_data->imdbVideo = null;
            }
            $TTCache->Set("imdb/$id", $_data, 900);
        }

        // Defaults keep the other tabs visible even when IMDb scraping fails.
        $castImages = array();
        $trailerUrl = '';
        $youtubeApiError = '';

        if (is_object($_data)):
        // Cast images
         $castImages = $TTCache->Get("tmdb_cast_$id", 86400);
         if ($castImages === false) {

         $castImages = $TTIMDB->getCastImages($row['imdb'], 6);
         if (!empty($castImages)) {

        $TTCache->Set("tmdb_cast_$id", $castImages, 86400);
        }
      }

     // Trailer: IMDb first, then YouTube Data API as a fallback.
     // v2 intentionally changes the cache key so old empty trailer results
     // from the previous implementation cannot hide a newly working API key.
     $youtubeCacheKey = "youtube_trailer_v2_$id";
     $youtubeRefresh = !empty($_GET['youtube_refresh']) && $_GET['youtube_refresh'] == '1';
     $trailerUrl = false;
     $youtubeApiKey = '';
     $youtubeApiError = '';

     if (!$youtubeRefresh) {
         $trailerUrl = $TTCache->Get($youtubeCacheKey, 2592000);
     }

     if ($trailerUrl === false || $youtubeRefresh) {
         $trailerUrl = '';

         // 1) Existing IMDb trailer lookup.
         $imdbTrailerUrl = $TTIMDB->getTrailerUrl($row['imdb']);
         if (!empty($imdbTrailerUrl) && preg_match('~(?:youtube\\.com|youtu\\.be)~i', $imdbTrailerUrl)) {
             $trailerUrl = $imdbTrailerUrl;
         }

         // 2) YouTube Data API fallback when IMDb did not return a trailer.
         if ($trailerUrl === '') {
             if (!empty($site_config['YOUTUBE_API_KEY'])) {
                 $youtubeApiKey = trim($site_config['YOUTUBE_API_KEY']);
             } elseif (YOUTUBE_API_KEY !== '') {
                 $youtubeApiKey = trim(YOUTUBE_API_KEY);
             }

             $youtubeTitle = !empty($_data->Title) ? $_data->Title : $row['name'];
             $youtubeYear = !empty($_data->Year) ? $_data->Year : '';

             $trailerUrl = tt_get_youtube_trailer($youtubeTitle, $youtubeYear, $youtubeApiKey, $youtubeApiError);
         }

         // Cache only successful results for 30 days. Empty results are not
         // cached during normal page views, making troubleshooting immediate.
         if ($trailerUrl !== '') {
             $TTCache->Set($youtubeCacheKey, $trailerUrl, 2592000);
         }
     }


          ?>
        <fieldset class="tt-imdb">
            <legend><b><?php echo T_("IMDB_SHORT"); ?></b> &bull; <?php echo $_data->Title; ?></legend>
            <table border="0" cellpadding="5" cellspacing="5" width="100%">
                <tr>
                    <td width="230" class="css" rowspan="2"><img src="<?php echo $_data->Poster; ?>" alt="<?php echo $_data->Title; ?>" title="<?php echo $_data->Title; ?>" class="rip" height="350px" width="244px" /></td>
                    <td valign="top">
                        <?php if (($rating = $TTIMDB->getRating($_data->imdbRating)) !== null) { ?>
                            <table border="0" cellpadding="5" cellspacing="5" width="100%"><tr><td>
                                <div class="f-border imdb-box" style="padding:7px; margin-bottom:10px">
                                    <table border="0" cellpadding="3" cellspacing="0">
                                        <tr><td colspan="2" class="css"><?php echo $TTIMDB->renderStars10($_data->imdbRating); ?></td></tr>
                                        <tr><td width="1%" class="css"><b><?php echo T_("IMDB_RATED"); ?></b>:</td><td class="css-right">&nbsp;<?php echo $TTIMDB->getRated( $_data->Rated ); ?></td></tr>
                                        <tr><td width="1%" class="css"><b><?php echo T_("IMDB_VOTES"); ?></b>:</td><td class="css-right">&nbsp;<?php echo $_data->imdbVotes; ?> </td></tr>
                                    </table>
                                </div>
                            </td></tr></table>
                        <?php } else { ?>
                            <table border="0" cellpadding="0" cellspacing="0"><tr><td>
                                <div class="f-border" style="padding:7px; margin-bottom:10px">
                                    <table border="0" cellpadding="3" cellspacing="0">
                                        <tr><td><img src="images/imdb/00.png" border="0"></td></tr>
                                        <tr><td><?php echo T_("NOT_YET_RATED"); ?>...</td></tr>
                                    </table>
                                </div>
                            </td></tr></table>
                        <?php } 

$runtime = !empty($_data->Runtime) ? $_data->Runtime : "N/A";
$director = !empty($_data->Director) ? $_data->Director : "N/A";
$writer = !empty($_data->Writer) ? $_data->Writer : "N/A";
?>
                  <table border="0" cellpadding="4" cellspacing="0" width="100%">
                              <tr><td class="css"><?php echo T_("IMDB_LINK"); ?> :</td><td class="css-right"><a href="<?php echo $row['imdb']; ?>" target="_blank"><?php echo htmlspecialchars($row['imdb']); ?></a></td></tr>
                              <tr><td class="css"><?php echo T_("IMDB_RELEASED"); ?> :</td><td class="css-right"><?php echo $TTIMDB->getReleased($_data->Released); ?></td></tr>
                              <tr><td class="css"><?php echo T_("IMDB_YEAR"); ?> :</td><td class="css-right"><?php echo $_data->Year; ?></td></tr>
                              <tr><td class="css"><?php echo T_("IMDB_RUNTIME"); ?> :</td><td class="css-right"><?php echo $runtime; ?></td></tr>
                              <tr><td class="css"><?php echo T_("IMDB_GENRE"); ?> :</td><td class="css-right"><?php echo $_data->Genre; ?></td></tr>
                              <tr><td class="css"><?php echo T_("IMDB_DIRECTOR"); ?> :</td><td class="css-right"><?php echo $_data->Director; ?></td></tr>
                              <tr><td class="css"><?php echo T_("IMDB_WRITER"); ?> :</td><td class="css-right"><?php echo $_data->Writer; ?></td></tr>
                              <!--<tr><td class="css"><?php echo T_("IMDB_ACTORS"); ?> :</td><td class="css-right"><?php echo $_data->Actors; ?></td></tr>-->
                              <tr><td class="css"><?php echo T_("IMDB_ACTORS"); ?> :</td><td class="css-right">
                         <?php
                             // Top Cast
                             if (!empty($castImages)) {
                                echo implode(', ', array_keys($castImages));
                             } else {
                                echo $_data->Actors; // fallback
                            }
                          ?>
                         </td></tr>

                                <tr><td></td></tr>
                              <tr><td class="css"><?php echo T_("IMDB_PLOT"); ?> :</td><td class="css-right"><?php echo $_data->Plot; ?></td></tr>
                            </table>
                    </td>
                </tr>
             <?php

              if (!empty($castImages)) {
                 echo '<tr><td colspan="3" align="center">';
              foreach ($castImages as $actorName => $imgUrl) {
                 echo '<img src="' . htmlspecialchars($imgUrl) . '"
                         alt="' . htmlspecialchars($actorName) . '"
                         title="' . htmlspecialchars($actorName) . '"
                         class="actor-thumb rip" loading="lazy" />';
               }
                 echo '</td></tr>';
             }
          ?>
                <tr>
                    <td align="right" colspan="3">
                        <b><?php echo T_("IMDB_LASTUPDATED"); ?></b> <i><?php echo $TTIMDB->getUpdated($_data->imdbTime); ?></i>
                    </td>
                </tr>
            </table>
                </fieldset>

        <?php else: ?>
        <fieldset class="tt-imdb">
            <legend><b><?php echo T_("IMDB_SHORT"); ?></b>  <?php echo $_data->Title; ?></legend>

        <div class="imdb-unavailable tt-trailer-box">
            <i class="fa-brands fa-imdb"></i>
            <div> No IMDb data available right now. </div>
        </div>

        <?php endif; ?>
                </fieldset>
        </div><!-- /tt-tab-imdb -->

        <div id="tt-tab-trailer" class="tt-tab-panel" role="tabpanel" style="display:<?php echo $activeTab === 'trailer' ? 'block' : 'none'; ?>;">
        <?php
        // Trailer is deliberately outside the IMDb fieldset.
        // IMDb is tried first; YouTube Data API is the fallback.
        $embedUrl = "";
        if (!empty($trailerUrl)) {
            if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([^&?/]+)~i', $trailerUrl, $trailerMatch)) {
                $embedUrl = "https://www.youtube.com/embed/" . $trailerMatch[1];
            }
        }
        ?>

        <fieldset class="tt-trailer">
            <legend><b>Trailer</b></legend>
            <div class="tt-trailer-box">
                <?php if ($embedUrl): ?>
                    <iframe
                        src="<?php echo htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8'); ?>"
                        title="Trailer"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <div id="youtube-trailer-placeholder" style="min-height:120px; display:flex; align-items:center; justify-content:center; text-align:center; opacity:.75;">
                        <div><i class="fa-brands fa-youtube" style="font-size:80px;color:red;"></i><br>
                            <b>Trailer</b><br>
                            No IMDb/YouTube trailer was found.
                            <?php if (!empty($youtubeApiError) && !empty($CURUSER['edit_torrents'])): ?>
                                <br><small style="color: var(--danger);">YouTube API: <?php echo htmlspecialchars($youtubeApiError, ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php endif; ?>
                            <?php if (!empty($CURUSER['edit_torrents'])): ?>
                                <br><small><a href="torrents-details.php?id=<?php echo $id; ?>&amp;tab=trailer&amp;youtube_refresh=1">Refresh trailer search</a></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </fieldset>

        </div><!-- /tt-tab-trailer -->

<div id="tt-tab-posters" class="tt-tab-panel" role="tabpanel" style="display:<?php echo $activeTab === 'posters' ? 'block' : 'none'; ?>;">

<table><tr><td height=5></td></tr></table>

<?php
echo "<br />";

// Poster images
if (!empty($row["poster"]) || !empty($row["poster2"])) {
    echo "<fieldset><legend> Images </legend>";
    echo "<div class='tt-images-grid'>";

    if (!empty($row["poster"])) {
        $poster1 = htmlspecialchars($row["poster"], ENT_QUOTES, 'UTF-8');
        echo "<div>";
        echo "<a href='".$poster1."' rel='prettyPhoto[posters]' title='Poster 1'>";
        echo "<img src='".$poster1."' alt='Poster 1' class='poster' loading='lazy'>";
        echo "</a>";
        echo "<div class='tt-poster-links'><a href='".$poster1."' target='_blank' rel='noopener'>Poster 1 – open full size</a></div>";
        echo "</div>";
    }

    if (!empty($row["poster2"])) {
        $poster2 = htmlspecialchars($row["poster2"], ENT_QUOTES, 'UTF-8');
        echo "<div>";
        echo "<a href='".$poster2."' rel='prettyPhoto[posters]' title='Poster 2'>";
        echo "<img src='".$poster2."' alt='Poster 2' class='poster' loading='lazy'>";
        echo "</a>";
        echo "<div class='tt-poster-links'><a href='".$poster2."' target='_blank' rel='noopener'>Poster 2 – open full size</a></div>";
        echo "</div>";
    }

    echo "</div>";
    echo "</fieldset>";
    echo "<br />";
}
?>
</div><!-- /tt-tab-posters -->

<script>
jQuery(document).ready(function($){
    $("a[rel^='prettyPhoto']").prettyPhoto({
        theme: 'pp_default',
        social_tools: false,
        show_title: true,
        allow_resize: true,   // slika se skalira prema veličini ekrana korisnika
        deeplinking: false,
        opacity: 0.85,
        overlay_gallery: false
    });
});
</script>

        <div id="tt-tab-stats" class="tt-tab-panel" role="tabpanel" style="display:<?php echo $activeTab === 'stats' ? 'block' : 'none'; ?>;">
<fieldset class="tt-rating">
    <legend><b><?php echo T_("RATINGS"); ?></b></legend>
<?php
// $srating IS RATING VARIABLE
        $srating = "";
        $srating .= "<table cellspacing=\"5\" cellpadding=\"5\" width='100%'><tr><td class='css-right' width='60'><b>".T_("RATINGS").":</b></td><td class='css' valign='middle'>";
        if (!isset($row["rating"])) {
                $srating .= "Not Yet Rated";
        }else{
            $rpic = ratingpic($row["rating"]);
            if (!isset($rpic))
                $srating .= "invalid?";
            else
                $srating .= "$rpic (" . $row["rating"] . " ".T_("OUT_OF")." 5) " . $row["numratings"] . " ".T_("USERS_HAVE_RATED");
        }
        $srating .= "\n";
        if (!isset($CURUSER))
            $srating .= "(<a href=\"account-login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">Log in</a> to rate it)";
        else {
            $ratings = array(
                    5 => T_("COOL"),
                    4 => T_("PRETTY_GOOD"),
                    3 => T_("DECENT"),
                    2 => T_("PRETTY_BAD"),
                    1 => T_("SUCKS")
            );
            //if (!$owned || $moderator) {
                $xres = SQL_Query_exec("SELECT rating, added FROM ratings WHERE torrent = $id AND user = " . $CURUSER["id"]);
                $xrow = mysqli_fetch_assoc($xres);
                if ($xrow)
                    $srating .= "<br /><i>(".T_("YOU_RATED")." \"" . $xrow["rating"] . " - " . $ratings[$xrow["rating"]] . "\")</i>";
                else {
                    $srating .= "<form style=\"display:inline;\" method=\"post\" action=\"torrents-details.php?id=$id&amp;takerating=yes\"><input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
                    $srating .= "<select name=\"rating\">\n";
                    $srating .= "<option value=\"0\">(".T_("ADD_RATING").")</option>\n";
                    foreach ($ratings as $k => $v) {
                        $srating .= "<option value=\"$k\">$k - $v</option>\n";
                    }
                    $srating .= "</select>\n";
                    $srating .= "<input type=\"submit\" value=\"".T_("VOTE")."\" />";
                    $srating .= "</form>\n";
                }
            //}
        }
        $srating .= "</td></tr></table>";

print("<div class='alert alert-success alert-white rounded'>
    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'> x </button>
    <div class='icon'><i class='fa fa-check'></i></div><center>". $srating . "</center></div>");// rating

?>
</fieldset>
<br />

<?php
if ($row["external"]=='yes'){
    print ("<div class='alert alert-info alert-white rounded'>
    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>  </button>
    <div class='icon'><i class='fa fa-info-circle'></i></div><strong>Tracker:<strong><br /> ".htmlspecialchars($row['announce'])."<br /></div>");
}

$tres = SQL_Query_exec("SELECT * FROM `announce` WHERE `torrent` = $id");
if (mysqli_num_rows($tres) > 1){
    echo "<div class='alert alert-info alert-white rounded'>
    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>  </button>
    <div class='icon'><i class='fa fa-info-circle'></i></div><strong>".T_("THIS_TORRENT_HAS_BACKUP_TRACKERS")."</b>&nbsp;<img src='images/plus.gif' id='pic_tracker' onclick='klappe_torrent(1)' alt='' style='float:right' /><div id='k1' style='display: none;'><br />";
    echo '<table cellpadding="5" cellspacing="5" class="table table-bordered" width="100%"><tr>';
    echo '<th class="table_head">URL</th><th class="table_head">'.T_("SEEDERS").'</th><th class="table_head">'.T_("LEECHERS").'</th><th class="table_head">'.T_("COMPLETED").'</th></tr>';
    $x = 1;
    while ($trow = mysqli_fetch_assoc($tres)) {
        $colour = $trow["online"] == "yes" ? "var(--success)" : "var(--danger)";
        echo "<tr class=\"table_col$x\"><td class='css'><b style=\"color:$colour;\">".htmlspecialchars($trow['url'])."</b></td><td align=\"center\" class='css-right'>".number_format($trow["seeders"])."</td><td align=\"center\" class='css'>".number_format($trow["leechers"])."</td><td align=\"center\" class='css-right'>".number_format($trow["times_completed"])."</td></tr>";
        $x = $x == 1 ? 2 : 1;
    }
    echo '</table></div>';
}

//DISPLAY NFO BLOCK
function my_nfo_translate($nfo){
        $trans = array(
        "\x80" => "&#199;", "\x81" => "&#252;", "\x82" => "&#233;", "\x83" => "&#226;", "\x84" => "&#228;", "\x85" => "&#224;", "\x86" => "&#229;", "\x87" => "&#231;", "\x88" => "&#234;", "\x89" => "&#235;", "\x8a" => "&#232;", "\x8b" => "&#239;", "\x8c" => "&#238;", "\x8d" => "&#236;", "\x8e" => "&#196;", "\x8f" => "&#197;", "\x90" => "&#201;",
        "\x91" => "&#230;", "\x92" => "&#198;", "\x93" => "&#244;", "\x94" => "&#246;", "\x95" => "&#242;", "\x96" => "&#251;", "\x97" => "&#249;", "\x98" => "&#255;", "\x99" => "&#214;", "\x9a" => "&#220;", "\x9b" => "&#162;", "\x9c" => "&#163;", "\x9d" => "&#165;", "\x9e" => "&#8359;", "\x9f" => "&#402;", "\xa0" => "&#225;", "\xa1" => "&#237;",
        "\xa2" => "&#243;", "\xa3" => "&#250;", "\xa4" => "&#241;", "\xa5" => "&#209;", "\xa6" => "&#170;", "\xa7" => "&#186;", "\xa8" => "&#191;", "\xa9" => "&#8976;", "\xaa" => "&#172;", "\xab" => "&#189;", "\xac" => "&#188;", "\xad" => "&#161;", "\xae" => "&#171;", "\xaf" => "&#187;", "\xb0" => "&#9617;", "\xb1" => "&#9618;", "\xb2" => "&#9619;",
        "\xb3" => "&#9474;", "\xb4" => "&#9508;", "\xb5" => "&#9569;", "\xb6" => "&#9570;", "\xb7" => "&#9558;", "\xb8" => "&#9557;", "\xb9" => "&#9571;", "\xba" => "&#9553;", "\xbb" => "&#9559;", "\xbc" => "&#9565;", "\xbd" => "&#9564;", "\xbe" => "&#9563;", "\xbf" => "&#9488;", "\xc0" => "&#9492;", "\xc1" => "&#9524;", "\xc2" => "&#9516;", "\xc3" => "&#9500;",
        "\xc4" => "&#9472;", "\xc5" => "&#9532;", "\xc6" => "&#9566;", "\xc7" => "&#9567;", "\xc8" => "&#9562;", "\xc9" => "&#9556;", "\xca" => "&#9577;", "\xcb" => "&#9574;", "\xcc" => "&#9568;", "\xcd" => "&#9552;", "\xce" => "&#9580;", "\xcf" => "&#9575;", "\xd0" => "&#9576;", "\xd1" => "&#9572;", "\xd2" => "&#9573;", "\xd3" => "&#9561;", "\xd4" => "&#9560;",
        "\xd5" => "&#9554;", "\xd6" => "&#9555;", "\xd7" => "&#9579;", "\xd8" => "&#9578;", "\xd9" => "&#9496;", "\xda" => "&#9484;", "\xdb" => "&#9608;", "\xdc" => "&#9604;", "\xdd" => "&#9612;", "\xde" => "&#9616;", "\xdf" => "&#9600;", "\xe0" => "&#945;", "\xe1" => "&#223;", "\xe2" => "&#915;", "\xe3" => "&#960;", "\xe4" => "&#931;", "\xe5" => "&#963;",
        "\xe6" => "&#181;", "\xe7" => "&#964;", "\xe8" => "&#934;", "\xe9" => "&#920;", "\xea" => "&#937;", "\xeb" => "&#948;", "\xec" => "&#8734;", "\xed" => "&#966;", "\xee" => "&#949;", "\xef" => "&#8745;", "\xf0" => "&#8801;", "\xf1" => "&#177;", "\xf2" => "&#8805;", "\xf3" => "&#8804;", "\xf4" => "&#8992;", "\xf5" => "&#8993;", "\xf6" => "&#247;",
        "\xf7" => "&#8776;", "\xf8" => "&#176;", "\xf9" => "&#8729;", "\xfa" => "&#183;", "\xfb" => "&#8730;", "\xfc" => "&#8319;", "\xfd" => "&#178;", "\xfe" => "&#9632;", "\xff" => "&#160;",
        );
        $trans2 = array("\xe4" => "&auml;",        "\xF6" => "&ouml;",        "\xFC" => "&uuml;",        "\xC4" => "&Auml;",        "\xD6" => "&Ouml;",        "\xDC" => "&Uuml;",        "\xDF" => "&szlig;");
        $all_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $last_was_ascii = False;
        $tmp = "";
        $nfo = $nfo . "\00";
        for ($i = 0; $i < (strlen($nfo) - 1); $i++)
        {
                $char = $nfo[$i];
                if (isset($trans2[$char]) and ($last_was_ascii or strpos($all_chars, ($nfo[$i + 1]))))
                {
                        $tmp = $tmp . $trans2[$char];
                        $last_was_ascii = True;
                }
                else
                {
                        if (isset($trans[$char]))
                        {
                                $tmp = $tmp . $trans[$char];
                        }
                        else
                        {
                            $tmp = $tmp . $char;
                        }
                        $last_was_ascii = strpos($all_chars, $char);
                }
        }
        return $tmp;
}
//-----------------------------------------------

//DISPLAY NFO BLOCK
if($row["nfo"]== "yes"){
    $nfofilelocation = "$nfo_dir/$row[id].nfo";
    $filegetcontents = file_get_contents($nfofilelocation);
    $nfo = htmlspecialchars($filegetcontents);
        if ($nfo) {
            $nfo = my_nfo_translate($nfo);
            echo "<br /><br /><b>NFO:</b><br />";
            print("<textarea class='nfo' style='width:98%;height:100%;' rows='20' cols='20' readonly='readonly'>".stripslashes($nfo)."</textarea>");
        }else{
            print(T_("ERROR")." reading .nfo file!");
        }
}

echo "<fieldset class='tt-file-section'><legend><b>".T_("FILE_LIST")."</b></legend>";
echo "<img src='images/plus.gif' id='pic_files' onclick='klappe_torrent(2)' alt='' style='float:right; cursor:pointer' />";
echo "<div id='k2' style='display: none;'><table align='center' cellpadding='0' cellspacing='0' class='table_table' border='1' width='100%'><tr><th width='50' class='table_head'>&nbsp;".T_("TYPE")."</th><th class='table_head' align='left'>&nbsp;".T_("FILE")."</th><th width='50' class='table_head'>&nbsp;".T_("SIZE")."</th></tr>";
$id = intval($id);  // Sanitize $id
$fres = SQL_Query_exec("SELECT * FROM `files` WHERE `torrent` = $id ORDER BY `path` ASC");
if (mysqli_num_rows($fres)) {
    while ($frow = mysqli_fetch_assoc($fres)) {
        $ext = pathinfo($frow['path'], PATHINFO_EXTENSION);
        $filetype_icons = [
            "mp3" => "/images/filetype-icons/mp3.png",
            "iso" => "/images/filetype-icons/iso.png",
            "mp4" => "/images/filetype-icons/mp4.png",
            "png" => "/images/filetype-icons/png.png",
            "jpg" => "/images/filetype-icons/jpg.png",
            "mkv" => "/images/filetype-icons/mkv.png",
            "rar" => "/images/filetype-icons/rar.png",
            "avi" => "/images/filetype-icons/avi.png",
            "srt" => "/images/filetype-icons/srt.png",
            "nfo" => "/images/filetype-icons/nfo.png"
          ];
          if(preg_match("/^r\d{2}$/", $ext)) {
            $filetype_icon = "<img width=40 height=40 src='/images/filetype-icons/rar.png'>";
        } elseif(isset($filetype_icons[$ext])) {
            $filetype_icon = "<img width=40 height=40 src='" . $filetype_icons[$ext] . "'>";
        } else {
            $filetype_icon = "<img width=40 height=40 src='/images/filetype-icons/unknown.png'>";
        }


        echo "<tr><td class='table_col1'>".$filetype_icon."</td><td class='table_col1'>".htmlspecialchars($frow['path'])."</td><td class='table_col2'>".mksize($frow['filesize'])."</td></tr>";
    }
}else{
    echo "<tr><td class='table_col1'><img width='40' height='40' src='/images/filetype-icons/unknown.png' alt=''></td><td class='table_col1'>".htmlspecialchars($row["name"])."</td><td class='table_col2'>".mksize($row["size"])."</td></tr>";
}
echo "</table></div></fieldset>";

if ($row["external"]!='yes'){
    echo "<fieldset class='tt-peer-section'><legend><b>".T_("PEERS_LIST")."</b></legend>";
    $query = SQL_Query_exec("SELECT * FROM peers WHERE torrent = $id ORDER BY seeder DESC");

    $result = mysqli_num_rows($query);
        if($result == 0) {
            echo T_("NO_ACTIVE_PEERS")."\n";
        }else{
            ?>

            <table border="0" cellpadding="3" cellspacing="0" width="100%" class="table_table">
            <tr>
                <th class="table_head"><?php echo T_("PORT"); ?></th>
                <th class="table_head"><?php echo T_("UPLOADED"); ?></th>
                <th class="table_head"><?php echo T_("DOWNLOADED"); ?></th>
                <th class="table_head"><?php echo T_("RATIO"); ?></th>
                <th class="table_head"><?php echo T_("_LEFT_"); ?></th>
                <th class="table_head"><?php echo T_("FINISHED_SHORT"). "%"; ?></th>
                <th class="table_head"><?php echo T_("SEED"); ?></th>
                <th class="table_head"><?php echo T_("CONNECTED_SHORT"); ?></th>
                <th class="table_head"><?php echo T_("CLIENT"); ?></th>
                <th class="table_head"><?php echo T_("USER_SHORT"); ?></th>
            </tr>

            <?php
            while($row1 = mysqli_fetch_assoc($query))	{

                if ($row1["downloaded"] > 0){
                    $ratio = $row1["uploaded"] / $row1["downloaded"];
                    $ratio = number_format($ratio, 3);
                }else{
                    $ratio = "---";
                }

                $percentcomp = sprintf("%.2f", 100 * (1 - ($row1["to_go"] / $row["size"])));

                if ($site_config["MEMBERSONLY"]) {
                    $res = SQL_Query_exec("SELECT id, username, privacy FROM users WHERE id=".$row1["userid"]."");
                    $arr = mysqli_fetch_array($res);

                    $arr["username"] = "<a href='account-details.php?id=$arr[id]'>$arr[username]</a>";
                }

                # With $site_config["MEMBERSONLY"] off this will be shown.
                if ( !$arr["username"] ) $arr["username"] = "Unknown User";

                if ($arr["privacy"] != "strong" || ($CURUSER["control_panel"] == "yes")) {
                    print("<tr><td class='table_col2'>".$row1["port"]."</td><td class='table_col1'>".mksize($row1["uploaded"])."</td><td class='table_col2'>".mksize($row1["downloaded"])."</td><td class='table_col1'>".$ratio."</td><td class='table_col2'>".mksize($row1["to_go"])."</td><td class='table_col1'>".$percentcomp."%</td><td class='table_col2'>$row1[seeder]</td><td class='table_col1'>$row1[connectable]</td><td class='table_col2'>".htmlspecialchars($row1["client"])."</td><td class='table_col1'>$arr[username]</td></tr>");
                }else{
                    print("<tr><td class='table_col2'>".$row1["port"]."</td><td class='table_col1'>".mksize($row1["uploaded"])."</td><td class='table_col2'>".mksize($row1["downloaded"])."</td><td class='table_col1'>".$ratio."</td><td class='table_col2'>".mksize($row1["to_go"])."</td><td class='table_col1'>".$percentcomp."%</td><td class='table_col2'>$row1[seeder]</td><td class='table_col1'>$row1[connectable]</td><td class='table_col2'>".htmlspecialchars($row1["client"])."</td><td class='table_col1'>Private</td></tr>");
                }

            }
            echo "</table>";
    }
    echo "</fieldset>";
}

echo "<br /><br />";
?>
        </div><!-- /tt-tab-stats -->

        <div id="tt-tab-comments" class="tt-tab-panel" role="tabpanel" style="display:<?php echo $activeTab === 'comments' ? 'block' : 'none'; ?>;">
        <fieldset class="tt-comments">
            <legend> <b>Comments</b> </legend>

<?php
    //echo "<p align=center><a class=index href=torrents-comment.php?id=$id>" .T_("ADDCOMMENT"). "</a></p>\n";

    $subres = SQL_Query_exec("SELECT COUNT(*) FROM comments WHERE torrent = $id");
    $subrow = mysqli_fetch_array($subres);
    $commcount = $subrow[0];

    if ($commcount) {
        list($pagertop, $pagerbottom, $limit) = pager(10, $commcount, "torrents-details.php?id=$id&amp;");
        $commquery = "SELECT comments.id, text, user, comments.added, avatar, signature, username, title, class, uploaded, downloaded, privacy, donated FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id $limit";
        $commres = SQL_Query_exec($commquery);
    }else{
        unset($commres);
    }

    if ($commcount) {
        print($pagertop);
        commenttable($commres, 'torrent');
        print($pagerbottom);
    }else {
        print("<br /><b>" .T_("NOCOMMENTS"). "</b><br />\n");
    }

    require_once("backend/bbcode.php");

    if ($CURUSER) {
        echo "<center>";
        echo "<form name=\"comment\" method=\"post\" action=\"torrents-details.php?id=$row[id]&amp;tab=comments&amp;takecomment=yes\">";
        echo textbbcode("comment","body")."<br />";
        echo "<input type=\"submit\"  value=\"".T_("ADDCOMMENT")."\" />";
        echo "</form></center>";
    }
?>
        </div><!-- /tt-tab-comments -->
</fieldset>
        </div><!-- /tt-tab-panels -->
    </div><!-- /ttTorrentTabs -->

    <!-- Tabs use ordinary links and server-side tab selection. No JavaScript required. -->

    <?php end_frame(); stdfoot(); ?>