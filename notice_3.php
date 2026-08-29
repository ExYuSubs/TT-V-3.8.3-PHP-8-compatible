<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

if (!isset($GLOBALS["DBconnector"])) {
    require_once("backend/functions.php");
    dbconn();
    loggedinonly();
}

require_once("backend/bbcode.php");

// MariaDB Version
$res = SQL_Query_exec("SELECT VERSION() AS mysqli_version");
$row = mysqli_fetch_assoc($res);
$mysqlver = $row['mysqli_version'];

// Operating System
if (is_readable('/etc/os-release')) {
    $data = parse_ini_file('/etc/os-release');
    $os = isset($data['PRETTY_NAME']) ? $data['PRETTY_NAME'] : php_uname();
} else {
    $os = php_uname();
}

// Apache Version
if (!function_exists('apache_version')) {
    function apache_version()
    {
        if (!empty($_SERVER["SERVER_SOFTWARE"])) {
            $ver = explode(" ", $_SERVER["SERVER_SOFTWARE"], 3);
            return isset($ver[1]) ? ($ver[0] . " " . $ver[1]) : $_SERVER["SERVER_SOFTWARE"];
        }
        return "Unknown";
    }
}

// PHP Mode
$php_mode = php_sapi_name();

switch ($php_mode) {
    case 'fpm-fcgi':
        $php_mode = 'PHP-FPM';
        break;

    case 'apache2handler':
        $php_mode = 'Apache Module';
        break;

    case 'cgi-fcgi':
        $php_mode = 'CGI/FastCGI';
        break;

    case 'cli':
        $php_mode = 'CLI';
        break;

    default:
        $php_mode = strtoupper($php_mode);
        break;
}

echo "<table border='0' class='table table-bordered2' width='100%' cellpadding='10' cellspacing='10'>";

echo "<tr>";
echo "<td colspan='2' style='color:#ff0000!important;text-align:center;font:20px Georgia,serif;font-style:oblique;line-height:24px;'>";
echo "<br />";
echo "This site is just fundament of TT V3.8.3 for PHP 8+ and it's not finished yet.";
echo "</td>";
echo "</tr>";

echo "<tr><td class='css'>Operating System:</td><td class='css-right'>{$os}</td></tr>";
echo "<tr><td class='css'>Web Server:</td><td class='css-right'>" . apache_version() . "</td></tr>";
echo "<tr><td class='css'>PHP:</td><td class='css-right'>" . PHP_VERSION . " ({$php_mode})</td></tr>";
echo "<tr><td class='css'>MariaDB:</td><td class='css-right'>{$mysqlver}</td></tr>";

echo "</table>";
?>