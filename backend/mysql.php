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
$mysql_user = getenv('MYSQL_USER') ?: 'username';        // Username to connect
$mysql_pass = getenv('MYSQL_PASS') ?: 'password';        // Password to connect
$mysql_db = getenv('MYSQL_DB') ?: 'database';            // Database name

?>
