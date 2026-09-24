<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

//==== NEWS BLOCK

$CURUSER = $CURUSER ?? [];
$CURUSER['view_news'] = $CURUSER['view_news'] ?? null;
//Site News
if ($site_config['NEWSON'] && $CURUSER['view_news'] == "yes"){
	begin_frame(T_("NEWS"));
	$res = SQL_Query_exec("SELECT news.id, news.title, news.added, news.body, users.username FROM news LEFT JOIN users ON news.userid = users.id ORDER BY added DESC LIMIT 10");
	if (mysqli_num_rows($res) > 0){
		print("<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td>\n<ul>");
		$news_flag = 0;

		while($array = mysqli_fetch_assoc($res)){

            if (!$array["username"])
                 $array["username"] = T_('UNKNOWN_USER');

			$numcomm = get_row_count("comments", "WHERE news='".$array['id']."'");

			// Show first 2 items expanded
			if ($news_flag < 2) {
				$disp = "block";
				$pic = "minus";
			} else {
				$disp = "none";
				$pic = "plus";
			}

				print("<br /><a href=\"javascript: klappe_news('a".$array['id']."')\"><img border=\"0\" src=\"".$site_config["SITEURL"]."/images/$pic.gif\" id=\"pica".$array['id']."\" alt=\"Show/Hide\" />");
				print("&nbsp;<b>". $array['title'] . "</b></a> - <b>".T_("POSTED").":</b> " . date("d-M-y", utc_to_tz_time($array['added'])) . " <b>".T_("BY").":</b> $array[username]");

				print("<div id=\"ka".$array['id']."\" style=\"display: $disp;\"> ".format_comment($array["body"])." <br /><br />".T_("COMMENTS")." (<a href='comments.php?type=news&amp;id=".$array['id']."'>".number_format($numcomm)."</a>)</div><br /> ");

				$news_flag++;
		}
		print("</ul></td></tr></table>\n");
	}else{
		echo "<br /><b>".T_("NO_NEWS")."</b>";
	}
	end_frame();
}

//==== NEWS BLOCK
?>