<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

//==== NOTICE BLOCK

if ($CURUSER["class"] >= 1)
if ($site_config['SITENOTICEON']){
	begin_frame(T_("NOTICE"));
	include 'notice_3.php';
	end_frame();
}

//==== NOTICE BLOCK
?>