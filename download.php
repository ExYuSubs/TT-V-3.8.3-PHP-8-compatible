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
dbconn();

// notice/warning 
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

if (isset($_GET["passkey"]) && !empty($_GET["passkey"])) {
    $CURUSER = mysqli_fetch_assoc(SQL_Query_exec(
        "SELECT * FROM users 
         INNER JOIN groups 
         ON users.class = groups.group_id 
         WHERE passkey = " . sqlesc($_GET["passkey"]) . " 
         AND enabled = 'yes' 
         AND status = 'confirmed'"
    ));
}

// check permissions
if (!empty($site_config["MEMBERSONLY"])) {
    loggedinonly();
    if (($CURUSER['can_download'] ?? 'no') === "no") {
        show_error_msg(T_("ERROR"), T_("NO_PERMISSION_TO_DOWNLOAD"), 1);
        exit;
    }
}

$id = (int)($_GET["id"] ?? 0);
if (!$id) {
    show_error_msg(T_("ID_NOT_FOUND"), T_("ID_NOT_FOUND_MSG_DL"), 1);
    exit;
}

$res = SQL_Query_exec("SELECT filename, banned, external, announce FROM torrents WHERE id = " . intval($id));
$row = $res ? mysqli_fetch_assoc($res) : null;

if (!$row) {
    show_error_msg(T_("FILE_NOT_FOUND"), T_("ID_NOT_FOUND"), 1);
    exit;
}
if ($row["banned"] === "yes") {
    show_error_msg(T_("ERROR"), T_("BANNED_TORRENT"), 1);
    exit;
}

$torrent_dir = $site_config["torrent_dir"] ?? '';
$fn = "$torrent_dir/$id.torrent";

if (!is_file($fn)) {
    show_error_msg(T_("FILE_NOT_FOUND"), T_("FILE_NOT_FILE"), 1);
    exit;
}
if (!is_readable($fn)) {
    show_error_msg(T_("FILE_NOT_FOUND"), T_("FILE_UNREADABLE"), 1);
    exit;
}

$name = $row['filename'];
$friendlyname = str_replace(".torrent", "", $name);
$propername = str_replace(" ", "_", $friendlyname);
$name = ($site_config["SITENAME"] ?? 'SiteName') . "-$propername.torrent";

SQL_Query_exec("UPDATE torrents SET hits = hits + 1 WHERE id = $id");

require_once("backend/BEncode.php");
require_once("backend/BDecode.php");

if (!empty($site_config["MEMBERSONLY"])) {
    if (strlen($CURUSER['passkey'] ?? '') !== 32) {
        $rand = array_sum(explode(" ", microtime()));
        $CURUSER['passkey'] = md5($CURUSER['username'] . $rand . $CURUSER['secret'] . ($rand * mt_rand()));
        SQL_Query_exec("UPDATE users SET passkey='" . $CURUSER['passkey'] . "' WHERE id=" . intval($CURUSER['id']));
    }
}

// --- Подаване на торента ---
if ($row["external"] !== 'yes' && !empty($site_config["MEMBERSONLY"])) {
    $raw = file_get_contents($fn);

    if ($raw === false || strlen($raw) < 16) {
        show_error_msg(T_("ERROR"), "Torrent файлът е празен или не може да се прочете.", 1);
        exit;
    }

    // Fail-safe: ако случайно има HTML
    if ($raw[0] === '<') {
        show_error_msg(T_("ERROR"), "Вместо .torrent се връща HTML. Провери include файловете и изключи display_errors.", 1);
        exit;
    }

    $dict = BDecode($raw);
    if (!is_array($dict)) {
        show_error_msg(T_("ERROR"), "BDecode не успя да декодира .torrent.", 1);
        exit;
    }

    $dict['announce'] = sprintf($site_config["PASSKEYURL"], $CURUSER["passkey"]);
    unset($dict['announce-list']);

    $data = BEncode($dict);
    if ($data === false || strlen($data) < 16) {
        show_error_msg(T_("ERROR"), "BEncode върна невалидни данни.", 1);
        exit;
    }

    if (ob_get_length()) {
        @ob_clean();
    }
    flush();

    header('Content-Disposition: attachment; filename="' . $name . '"');
    header("Content-Type: application/x-bittorrent");
    header('Content-Length: ' . strlen($data));
    echo $data;
} else {
    if (ob_get_length()) {
        @ob_clean();
    }
    flush();

    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . filesize($fn));
    header("Content-Type: application/x-bittorrent");
    readfile($fn);
}

mysqli_close($GLOBALS["DBconnector"]);
exit;
?>
