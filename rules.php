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

stdhead(T_("SITE_RULES"));

$res = SQL_Query_exec("SELECT * FROM `rules` ORDER BY `id`");
while ($row = mysqli_fetch_assoc($res))
{
    if ($row["public"] == "yes")
    {
        begin_frame($row["title"]);
        echo format_comment($row["text"]);
        end_frame();
    }
    else if ($row["public"] == "no" && isset($CURUSER["class"]) && $CURUSER["class"] >= $row["class"])
    {
        begin_frame($row["title"]);
        echo format_comment($row["text"]);
        end_frame();
    }
}

stdfoot();
?>
