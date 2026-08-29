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
dbconn(true);
loggedinonly();
stdhead(T_("HOME"));

//check
	if (file_exists("check.php") && isset($CURUSER["class"]) && $CURUSER["class"] >= 7){
	show_error_msg("WARNING", "Check.php still exists, please delete or rename the file as it could pose a security risk<br /><br /><a href='check.php'>View Check.php</a> - Use to check your config!<br /><br />",0);
}

//===| Start of Notice block |===//
require_once("blocks/notice_block.php");
//====| End of Notice block |====//

//===| Start of News block |===//
require_once("blocks/news_block.php");
//====| End of News block |====//

//===| Start of Shotbox block |===//
require_once("blocks/shoutbox_block.php");
//====| End of Shoutbox block |====//

//===| Start of Latest torrents block |===//
require_once("blocks/last_torrents_block.php");
//====| End of Latest torrents block |====//

//===| Start of Disclamer block |===//
require_once("blocks/disclamer_block.php");
//====| End of Disclamer block |====//


stdfoot();
?>