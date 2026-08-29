<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

if (!$site_config["MEMBERSONLY"] || $CURUSER) {
begin_block(T_("SEARCH"));
?>
    <center>
    <form method="get" action="torrents-search.php"><br />
    <input type="text" name="search" size="15" value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
    <br /><br />
    <input type="submit" value="<?php echo T_("SEARCH"); ?>" />
    </form>
    </center><br />
<?php
end_block();
}
?>

