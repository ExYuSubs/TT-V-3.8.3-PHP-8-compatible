<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

//==== SHOUTBOX BLOCK
 
if ($site_config['SHOUTBOX'] && ($CURUSER['hideshoutbox'] ?? 'no') !== 'yes'){ 
	begin_frame(T_("SHOUTBOX"));
	echo '<iframe name="shout_frame" src="shoutbox.php" frameborder="0" marginheight="0" marginwidth="0" width="99%" height="550" scrolling="no" align="middle"></iframe>';
	printf(T_("SHOUTBOX_REFRESH"), 2)."<br />";
	end_frame();
}

//==== SHOUTBOX BLOCK
?>