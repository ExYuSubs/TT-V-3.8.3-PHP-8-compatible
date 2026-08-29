<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


// Please Note: Languages should not be implemented here...
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require_once "backend/mysql.php";
require_once "backend/config.php";
require_once "backend/mysql.class.php";
require_once "backend/cache.php";

// Ensure no prior output pollutes bencoded response
if (ob_get_length() !== false) {
    @ob_clean();
}

// DB connect
$GLOBALS["DBconnector"] = mysqli_connect($mysql_host, $mysql_user, $mysql_pass)
    or err('dbconn: mysqli_connect: ' . mysqli_connect_error());
mysqli_select_db($GLOBALS["DBconnector"], $mysql_db)
    or err('dbconn: mysqli_select_db: ' . mysqli_error($GLOBALS["DBconnector"]));

$MEMBERSONLY = $site_config["MEMBERSONLY"];
$MEMBERSONLY_WAIT = $site_config["MEMBERSONLY_WAIT"];

// Helpers
function sqlesc($x) {
    if (is_numeric($x)) return (int)$x;
    return "'" . mysqli_real_escape_string($GLOBALS["DBconnector"], (string)$x) . "'";
}
function err($msg) {
    return benc_resp_raw("d".benc_str("failure reason").benc_str($msg)."e");
}
function benc_str($s) { return strlen($s) . ":$s"; }
function benc_resp_raw($x) {
    if (ob_get_length() !== false) {
        @ob_clean();
    }
    header("Content-Type: text/plain");
    header("Pragma: no-cache");
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')
        && isset($_SERVER["HTTP_ACCEPT_ENCODING"]) && strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false) {
        header("Content-Encoding: gzip");
        echo gzencode($x, 9, FORCE_GZIP);
    } else {
        echo $x;
    }
    exit();
}
function gmtime() { return strtotime(gmdate("Y-m-d H:i:s")); }
function get_date_time($timestamp = 0) {
    return $timestamp ? date("Y-m-d H:i:s", $timestamp) : gmdate("Y-m-d H:i:s");
}
function portblacklisted($port) {
    return ($port >= 411 && $port <= 413) || $port == 1214 ||
           ($port >= 6346 && $port <= 6347) || $port == 4662 || $port == 6699;
}

// BLOCK ACCESS WITH WEB BROWSERS ADDED CHROME AND 
$agent = $_SERVER["HTTP_USER_AGENT"] ?? '';
if (preg_match("/^Mozilla|^Opera|^Chrome|^Google|^Edge|^Links|^Lynx/i", $agent)) die("No");

// GET DETAILS
foreach (["passkey","info_hash","peer_id","ip","event"] as $x) {
    $GLOBALS[$x] = $_GET[$x] ?? null;
}
foreach (["port","downloaded","uploaded","left"] as $x) {
    $GLOBALS[$x] = isset($_GET[$x]) ? (int)$_GET[$x] : 0;
}

// passkey we get data from users ?key=value
if ($passkey && strpos($passkey, "?") !== false) {
    $tmp = substr($passkey, strpos($passkey, "?"));
    $passkey = substr($passkey, 0, strpos($passkey, "?"));
    $tmpname = substr($tmp, 1, strpos($tmp, "=")-1);
    $tmpvalue = substr($tmp, strpos($tmp, "=")+1);
    $GLOBALS[$tmpname] = $tmpvalue;
}

// Scraping userdata and passkey from database
foreach (["passkey","info_hash","peer_id","port","downloaded","uploaded","left"] as $x) {
    if (!isset($GLOBALS[$x])) err("Missing key: $x");
}

// Validate peer_id
if (strlen((string)$peer_id) != 20) err("Invalid peer_id");

// Compact flag
$no_peer_id = (int)($_GET["no_peer_id"] ?? 0);

// info_hash normalise
if (strlen((string)$GLOBALS['info_hash']) == 20)
    $GLOBALS['info_hash'] = bin2hex($GLOBALS['info_hash']);
elseif (strlen((string)$GLOBALS['info_hash']) != 40)
    err("Invalid info hash value.");
$GLOBALS['info_hash'] = strtolower($GLOBALS['info_hash']);

if ($MEMBERSONLY && strlen((string)$passkey) != 32)
    err("Invalid passkey ($passkey)");

// numwant
$rsize = 50;
foreach(["num want","numwant","num_want"] as $k) {
    if (isset($_GET[$k])) { $rsize = max(1, (int)$_GET[$k]); break; }
}

// Agent Ban
$agentarray = array_map("trim", explode(",", $site_config["BANNED_AGENTS"] ?? ""));
$useragent = substr((string)$peer_id, 0, 8);
foreach($agentarray as $bannedclient) {
    if ($bannedclient !== '' && strpos($useragent, $bannedclient) !== false)
        err("Client is banned");
}

// USER lookup
$userid = 0;
if ($MEMBERSONLY) {
    $res = SQL_Query_exec("SELECT u.`id`, u.`class`, u.`uploaded`, u.`downloaded`, u.`ip`, u.`passkey`, g.`can_download`
                           FROM `users` u INNER JOIN `groups` g ON u.`class` = g.`group_id`
                           WHERE u.`passkey`=".sqlesc($passkey)." AND u.`enabled`='yes' AND u.`status`='confirmed' LIMIT 1");
    $user = $res ? mysqli_fetch_assoc($res) : null;
    if (!$user) err("Cannot locate a user with that passkey!");
    $canDownload = strtolower((string)($user["can_download"] ?? "yes"));
    if ($canDownload === "no" || $canDownload === "0") err("You do not have permission to download.");
    $userid = (int)$user["id"];
}

// TORRENT lookup
$res = SQL_Query_exec("SELECT `id`, `info_hash`, `banned`, `freeleech`, `seeders`+`leechers` AS numpeers,
                       UNIX_TIMESTAMP(`added`) AS ts, `seeders`, `leechers`, `times_completed`
                       FROM `torrents` WHERE `info_hash`=".sqlesc($info_hash)." LIMIT 1");
$torrent = $res ? mysqli_fetch_assoc($res) : null;
if (!$torrent) err("Torrent not found on this tracker - hash=$info_hash");
if (($torrent["banned"] ?? '') === 'yes') err("Torrent has been banned - hash=$info_hash");
$torrentid = (int)$torrent["id"];

// Determine seeder state from 'left'
$seeder = ($left == 0) ? "yes" : "no";

// Port blacklist check
if (portblacklisted($port)) err("Port $port is blacklisted");

// PEERS
$peerlimit = min(50, max(1, (int)$rsize));
$numpeers = (int)($torrent["numpeers"] ?? 0);
$limit = ($numpeers > $peerlimit) ? "ORDER BY RAND() LIMIT $peerlimit" : "";

$res = SQL_Query_exec("SELECT `seeder`, UNIX_TIMESTAMP(`last_action`) AS ez, `peer_id`, `ip`, `port`, `uploaded`, `downloaded`, `userid`, `passkey`
                       FROM `peers` WHERE `torrent`=$torrentid $limit");

// Build BENC RESPONSE HEADER
$resp  = "d";
$resp .= benc_str("complete")    . "i" . (int)($torrent['seeders'] ?? 0) . "e";
$resp .= benc_str("downloaded")  . "i" . (int)($torrent['times_completed'] ?? 0) . "e";
$resp .= benc_str("incomplete")  . "i" . (int)($torrent['leechers'] ?? 0) . "e";
$resp .= benc_str("interval")    . "i" . (int)($site_config['announce_interval'] ?? 1800) . "e";
$resp .= benc_str("min interval"). "i300e";
$resp .= benc_str("peers");

// peers list
$peers = "";
$self = null;
while ($row = ($res ? mysqli_fetch_assoc($res) : null)) {
    if (!$row) break;
    if (($row["peer_id"] ?? '') === (string)$peer_id) {
        $self = $row;
        continue;
    }
    $peers .= "d" . benc_str("ip") . benc_str((string)($row["ip"] ?? ""));
    if (!$no_peer_id) {
        $peers .= benc_str("peer id") . benc_str((string)($row["peer_id"] ?? ""));
    }
    $peers .= benc_str("port") . "i" . (int)($row["port"] ?? 0) . "ee";
}
$resp .= "l{$peers}e";
$resp .= "ee"; // end top-level dict

$selfwhere = "`torrent`=$torrentid AND `peer_id`=" . sqlesc($peer_id);

// IF PEER NOT IN TABLE: WAIT TIME CHECK + CONNECTABLE
if (!is_array($self)) {
    if ($MEMBERSONLY_WAIT && $MEMBERSONLY) {
        $waitClasses = array_filter(array_map('trim', explode(",", (string)($site_config["WAIT_CLASS"] ?? ""))));
        if ($left > 0 && in_array((string)($user["class"] ?? ''), $waitClasses, true)) {
            $gigs = ((float)($user["uploaded"] ?? 0)) / (1024*1024*1024);
            $elapsed = floor((gmtime() - (int)($torrent["ts"] ?? 0)) / 3600);
            $downloadedBytes = (int)($user["downloaded"] ?? 0);
            $uploadedBytes   = (int)($user["uploaded"] ?? 0);
            $ratio = ($downloadedBytes > 0) ? ($uploadedBytes / $downloadedBytes) : 1;

            if ($ratio == 0 && $gigs == 0) $wait = (int)($site_config["WAITA"] ?? 0);
            elseif ($ratio < (float)($site_config["RATIOA"] ?? 0) || $gigs < (float)($site_config["GIGSA"] ?? 0)) $wait = (int)($site_config["WAITA"] ?? 0);
            elseif ($ratio < (float)($site_config["RATIOB"] ?? 0) || $gigs < (float)($site_config["GIGSB"] ?? 0)) $wait = (int)($site_config["WAITB"] ?? 0);
            elseif ($ratio < (float)($site_config["RATIOC"] ?? 0) || $gigs < (float)($site_config["GIGSC"] ?? 0)) $wait = (int)($site_config["WAITC"] ?? 0);
            elseif ($ratio < (float)($site_config["RATIOD"] ?? 0) || $gigs < (float)($site_config["GIGSD"] ?? 0)) $wait = (int)($site_config["WAITD"] ?? 0);
            else $wait = 0;

            if ($elapsed < $wait) {
                err("Wait Time (" . ($wait - $elapsed) . " hours) - Visit ".(string)($site_config["SITEURL"] ?? "")." for more info");
            }
        }
    }

    // CONNECTABLE CHECK
    $connectable = "no";
    if ($ip && $port > 0) {
        $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
        if ($sockres) {
            $connectable = "yes";
            fclose($sockres);
        }
    }

} else {
    // LIVE STATS UPDATE
    $upthis   = max(0, $uploaded   - (int)($self["uploaded"] ?? 0));
    $downthis = max(0, $downloaded - (int)($self["downloaded"] ?? 0));

    if (($upthis > 0 || $downthis > 0) && $userid > 0) {
        if ((int)($torrent["freeleech"] ?? 0) === 1) {
            SQL_Query_exec("UPDATE `users` SET `uploaded` = `uploaded` + $upthis WHERE `id`=$userid")
                or err("Tracker error: Unable to update stats");
        } else {
            SQL_Query_exec("UPDATE `users` SET `uploaded` = `uploaded` + $upthis, `downloaded` = `downloaded` + $downthis WHERE `id`=$userid")
                or err("Tracker error: Unable to update stats");
        }
    }
}

// EVENT UPDATES
$updateset = [];

if ((string)$event === "stopped") {
    SQL_Query_exec("DELETE FROM `peers` WHERE $selfwhere");
    if (mysqli_affected_rows($GLOBALS["DBconnector"])) {
        if ($self && ((string)($self["seeder"] ?? "no") === "yes")) {
            $updateset[] = "seeders = (CASE WHEN (seeders < 1) THEN 0 ELSE (seeders - 1) END)";
        } else {
            $updateset[] = "leechers = (CASE WHEN (leechers < 1) THEN 0 ELSE (leechers - 1) END)";
        }
    }
}

if ((string)$event === "completed") {
    $updateset[] = "times_completed = times_completed + 1";
    if ($MEMBERSONLY && $userid > 0) {
        SQL_Query_exec("INSERT INTO `completed` (`userid`, `torrentid`, `date`) VALUES ($userid, $torrentid, '".get_date_time()."')");
    }
}

// UPDATE PEER RECORD OR INSERT NEW
if (is_array($self)) {
    SQL_Query_exec("UPDATE `peers` SET `ip`=".sqlesc($ip).", `passkey`=".sqlesc($passkey).", `port`=$port,
                    `uploaded`=$uploaded, `downloaded`=$downloaded, `to_go`=$left,
                    `last_action`='".get_date_time()."', `client`=".sqlesc($agent).",
                    `seeder`='$seeder' WHERE $selfwhere");

    if (mysqli_affected_rows($GLOBALS["DBconnector"]) && ((string)($self["seeder"] ?? "no") !== (string)$seeder)) {
        $updateset[] = ($seeder == "yes")
            ? "seeders = seeders + 1"
            : "seeders = (CASE WHEN (seeders < 1) THEN 0 ELSE (seeders - 1) END)";

        $updateset[] = ($seeder == "yes")
            ? "leechers = (CASE WHEN (leechers < 1) THEN 0 ELSE (leechers - 1) END)"
            : "leechers = leechers + 1";
    }

} else {
    $ret = SQL_Query_exec("INSERT INTO `peers` (`connectable`, `torrent`, `peer_id`, `ip`, `passkey`, `port`, `uploaded`, `downloaded`, `to_go`, `started`, `last_action`, `seeder`, `userid`, `client`)
                           VALUES ('$connectable', $torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", " . sqlesc($passkey) . ",
                                   $port, $uploaded, $downloaded, $left, '".get_date_time()."', '".get_date_time()."',
                                   '$seeder', '$userid', " . sqlesc($agent) . ")");
    if ($ret) {
        $updateset[] = ($seeder === "yes") ? "seeders = seeders + 1" : "leechers = leechers + 1";
    }
}

// SEEDED, MAKE VISIBLE
if ($seeder == "yes") {
    if (($torrent["banned"] ?? "no") != "yes") {
        $updateset[] = "visible = 'yes'";
    }
    $updateset[] = "last_action = '".get_date_time()."'";
}

// Update torrent counters
if (!empty($updateset)) {
    $updateset = array_filter($updateset);
    SQL_Query_exec("UPDATE `torrents` SET " . join(",", $updateset) . " WHERE `id`=$torrentid")
        or err("Tracker error: Unable to update torrent");
}

// Send bencoded response (DO NOT re-encode $resp)
benc_resp_raw($resp);

// Close DB
mysqli_close($GLOBALS["DBconnector"]);
exit;
?>