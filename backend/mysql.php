<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

// Prevent direct access to this script
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    exit;
}

// Change the settings below to match your MySQL/MariaDB server connection settings
$mysql_host = getenv('MYSQL_HOST') ?: 'localhost'; // Default to 'localhost'
$mysql_user = getenv('MYSQL_USER') ?: 'luigi_usr';        // Username to connect
$mysql_pass = getenv('MYSQL_PASS') ?: 'tt_12345';        // Password to connect
$mysql_db = getenv('MYSQL_DB') ?: 'luigi_db';            // Database name

?>