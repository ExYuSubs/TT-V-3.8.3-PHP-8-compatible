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
begin_block(T_("NAVIGATION"));
echo "<div id='navigate' class='bMenu'><ul>"; 
echo "<li><a href='index.php'>".T_("HOME")."</a></li>";

if (isset($CURUSER["view_torrents"]) && ($CURUSER["view_torrents"] == "yes" || !$site_config["MEMBERSONLY"]))
{ 
    echo "<li><a href='torrents.php'>".T_("BROWSE_TORRENTS")."</a></li>";
    echo "<li><a href='torrents-today.php'>".T_("TODAYS_TORRENTS")."</a></li>";
    echo "<li><a href='torrents-search.php'>".T_("SEARCH")."</a></li>";
    echo "<li><a href='torrents-needseed.php'>".T_("TORRENT_NEED_SEED")."</a></li>";
}

if (isset($CURUSER["edit_torrents"]) && $CURUSER["edit_torrents"] == "yes")
{
    echo "<li><a href='torrents-import.php'>".T_("MASS_TORRENT_IMPORT")."</a></li>";
}

if (isset($CURUSER["view_users"]) && $CURUSER["view_users"] == "yes")
{
    echo "<li><a href='teams-view.php'>".T_("TEAMS")."</a></li>";
    echo "<li><a href='memberlist.php'>".T_("MEMBERS")."</a></li>";
}

echo "<li><a href='rules.php'>".T_("SITE_RULES")."</a></li>";
echo "<li><a href='faq.php'>".T_("FAQ")."</a></li>";

if (isset($CURUSER["view_users"]) && $CURUSER["view_users"] == "yes")
{
    echo "<li><a href='staff.php'>".T_("STAFF")."</a></li>";
}

echo "</ul></div>";
end_block();
}
?>

