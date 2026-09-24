<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

//==== LATEST TORRENTS BLOCK

begin_frame(T_("LATEST_TORRENTS"));

print("<br /><center><a href='torrents.php'>".T_("BROWSE_TORRENTS")."</a> - <a href='torrents-search.php'>".T_("SEARCH_TORRENTS")."</a></center><br />");

if ($site_config["MEMBERSONLY"] && !$CURUSER) {
	echo "<br /><br /><center><b>".T_("BROWSE_MEMBERS_ONLY")."</b></center><br /><br />";
} else {
	$query = "SELECT torrents.id, torrents.anon, torrents.announce, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.image AS cat_pic, categories.parent_cat AS cat_parent, users.username, users.privacy, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE visible = 'yes' AND banned = 'no' ORDER BY id DESC LIMIT 25";
	$res = SQL_Query_exec($query);
	if (mysqli_num_rows($res)) {
		torrenttable($res);
	}else {
        
     print("<div class='f-border'>");
     print("<div class='f-cat' width='100%'>".T_("NOTHING_FOUND")."</div>");
     print("<div>");
     print T_("NO_UPLOADS");
     print("</div>");
     print("</div>");

	}
	if ($CURUSER && isset($CURUSER['id']) && $CURUSER['id'] > 0) {
		SQL_Query_exec("UPDATE users SET last_browse=" . gmtime() . " WHERE id=" . (int)$CURUSER['id']);
	}
	

}
end_frame();

//==== LATEST TORRENTS BLOCK
?>