<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

// Fallback za stare block funkcije
if (!function_exists('begin_block')) {
    function begin_block($title = '') {
        begin_frame($title);
    }
}

if (!function_exists('end_block')) {
    function end_block() {
        end_frame();
    }
}

	if ($CURUSER)
	{
		begin_block(" ".$CURUSER["username"]." ");

			$motm_current_query = @mysqli_fetch_row(@SQL_Query_exec("SELECT motm.points FROM motm WHERE month = " . date("m") . " && year = " . date("y") . " && id = ".$CURUSER["id"]));
			//$motm_current =  number_format(0 + $motm_current_query[0],0);

			$avatar = htmlspecialchars($CURUSER["avatar"]);
			if (!$avatar)
				$avatar = $site_config["SITEURL"]."/images/default_avatar.png";

			$userseedtime = mkprettytime($CURUSER["seedtime"]);
			$userdownloaded = mksize($CURUSER["downloaded"]);
			$useruploaded = mksize($CURUSER["uploaded"]);
			$seedbonus = T_($CURUSER["seedbonus"]);
			$privacylevel = T_($CURUSER["privacy"]);
			
			if ($CURUSER["uploaded"] > 0 && $CURUSER["downloaded"] == 0)
				$ratio = "Inf.";
			elseif ($CURUSER["downloaded"] > 0)
				$ratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 2);
			else
				$ratio = "---";
			$ratio = "<font color=".get_ratio_color($ratio).">$ratio</font>";
			
			$staff_messages = @mysqli_fetch_row(@SQL_Query_exec("SELECT text FROM ajshoutbox"));
			$staff_shout = (isset($staff_messages[0]));
			
			$stylesheets = '';
			$languages = '';

			//==| Language & Theme
			$ss_r = SQL_Query_exec("SELECT * from stylesheets");
			$ss_sa = array();

			while ($ss_a = mysqli_fetch_assoc($ss_r)){
				$ss_id = $ss_a["id"];
				$ss_name = $ss_a["name"];
				$ss_sa[$ss_name] = $ss_id;
			}

			ksort($ss_sa);
			reset($ss_sa);
			
			//while (list($ss_name, $ss_id) = thisEach($ss_sa)){
			foreach ($ss_sa as $ss_name => $ss_id) {
				if ($ss_id == $CURUSER["stylesheet"]) $ss = " selected='selected'"; else $ss = "";
				$stylesheets.= "<option value='$ss_id'$ss>$ss_name</option>\n";
			}

			$lang_r = SQL_Query_exec("SELECT * from languages");
			$lang_sa = array();

			while ($lang_a = mysqli_fetch_assoc($lang_r)){
				$lang_id = $lang_a["id"];
				$lang_name = $lang_a["name"];
				$lang_sa[$lang_name] = $lang_id;
			}

			ksort($lang_sa);
			reset($lang_sa);

			//while (list($lang_name, $lang_id) = thisEach($lang_sa)){
			foreach ($lang_sa as $lang_name => $lang_id) {
				if ($lang_id == $CURUSER["language"]) $lang = " selected='selected'"; else $lang = "";
				$languages.= "<option value='$lang_id'$lang>$lang_name</option>\n";
			}
			?>

			<div style="font:normal 12px Verdana; margin-top:-3px" align="center">
				<form method="post" action="take-theme.php">
					<table cellpadding='0' width='220' align='center' border='0'>
						<tr>
							<td class='css-right'><?php echo T_("LANGUAGE"); ?>:</td>
							<td class='css'><select name="language" onchange="this.form.submit();"><?php echo $languages;?></select><input type="hidden" name="Submit" value="ok"></td>
						</tr>
						<tr>
							<td class='css-right'><?php echo T_("THEME"); ?>:</td>
							<td class='css'><select name="stylesheet" onchange="this.form.submit();"><?php echo $stylesheets;?></select><input type="hidden" name="Submit" value="ok"></td>
						</tr>
					</table>
				</form>
			</div>

		<table cellpadding='0' width='220' align='center' border='0'>
			<?php
			print ("<tr><td colspan='2' align='center'><div style='margin-bottom:5px'><img width='150' src='$avatar' class='avatar' title='Avatar' /></div></td></tr>

			<tr><td class='css-right'><div style='font:normal 12px Verdana'>".T_("CLASS").":</div></td><td class='css'><div style='font: 12px Verdana'>".T_($CURUSER["level"])."</div></td></tr>
			<tr><td class='css-right'><div style='font:normal 12px Verdana'>".T_("ACCOUNT_PRIVACY_LVL").":</div></td><td class='css'><div style='font: 12px Verdana'>$privacylevel</div></td></tr>");
			//if ($site_config["MOTM"] && $CURUSER["class"] <= $site_config["maxmotm"]) {
			//print ("<tr><td><div style='font:normal 12px Verdana'>".T_("ACTIVITY").":</div></td><td class='css'><div style='font: 12px Verdana'>".$motm_current."</div></td></tr>");
			//}
			print ("<tr><td class='css-right'><div style='font:normal 12px Verdana'>".T_("SEEDBONUS").":</div></td><td class='css'><div style='font: 12px Verdana'><a href='seedbonus.php'>$seedbonus</a></div></td></tr>");
			print ("<tr><td class='css-right'><div style='font:normal 12px Verdana'>".T_("REPUTATION").":</div></td><td class='css'><div style='font: 12px Verdana'>".htmlspecialchars($CURUSER["reputation"])."</div></td></tr>");
			if ($site_config["ratiofree_enable"]) {
				print ("<tr><td colspan='2' height='3'></td></tr>");
				print ("<tr><td colspan='2' class='table_col1' width='100%' align='center'><div style='margin-top:3px; font:normal 12px Verdana'>".T_("TOTAL_SEED_TIME")."</div>");
				print ("<div style='margin-top:3px; margin-bottom:3px; font: 12px Verdana; color:#0080FF'>$userseedtime</div></td></tr>");
			} else {
				print ("<tr><td class='css-right'><div style='font:normal 12px Verdana'>".T_("DOWNLOADED").":</div></td><td class='css'><div style='font: 12px Verdana; color:#E70F0F'>$userdownloaded</div></td></tr>
				<tr><td class='css-right'><div style='font:normal 12px Verdana'>".T_("UPLOADED").":</div></td><td class='css'><div style='font: 12px Verdana; color:#17C417'>$useruploaded</div></td></tr>
				<tr><td class='css-right'><div style='font:normal 12px Verdana'>".T_("RATIO").":</div></td><td class='css'><div style='font: 12px Verdana'>$ratio</div></td></tr>");
			}
			print ("<tr><td colspan=2 height=2></td></tr>");
			?>
		</table>

		<table cellpadding="3" width="220" align="center" border="0">
			<?php
			echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-regular fa-address-card' style='color: blue; float: left; padding-right:10px;'></i>&nbsp;<a href='account.php'>".T_("YOUR_PROFILE")."</a></td></tr>";
			echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-angles-down' style='color: red; float: left; padding-right:10px;'></i>&nbsp;<a href='snatched.php'>".T_("SNATCHED_TORRENT[1]")."</a></td></tr>";
			echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-angles-up' style='color: green; float: left; padding-right:10px;'></i>&nbsp;<a href='debts.php'>".T_("SEEDING_TORRENT[1]")."</a></td></tr>";
			echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-angles-right' style='color: red; float: left; padding-right:10px;'></i>&nbsp;<a href='bookmark.php'>".T_("YOUR_BOOKMARKS")."</a></td></tr>";
			echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-regular fa-address-card' style='color: green; float: left; padding-right:10px;'></i>&nbsp;<a href='seedbonus.php'>".T_("YOUR_SEED_BONUS")."</a></td></tr>";
			echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-angles-left' style='color: blue; float: left; padding-right:10px;'></i>&nbsp;<a href='bitbucket.php'>".T_("YOUR_BITBUCKET")."</a></td></tr>";
			if($CURUSER["can_upload"] == "yes") {
				echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-magnet' style='color: green; float: left; padding-right:10px;'></i>&nbsp;<a href='account.php?action=mytorrents'>".T_("YOUR_TORRENTS")."</a></td></tr>";
			}
			echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-user-group' style='color: red; float: left; padding-right:10px;'></i>&nbsp;<a href='friends.php'>".T_("YOUR_FRIENDS")."</a></td></tr>";
			
			if ($CURUSER["control_panel"] == "yes") {
				if (!isset($staff_messages[0])) {
					echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-regular fa-comment-dots' style='color: blue; float: left; padding-right:10px;'></i>&nbsp;<a href='staff-chat.php'>".T_("STAFF_CHAT")."</a></td></tr>";
				} else {
					echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><img src='images/lb-staffchat.png' border=0>&nbsp;<a href=staff-chat.php><font color='red'>".T_("STAFF_CHAT")."</font></a></td></tr>";
				}
				echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-poo' style='color: brown; float: left; padding-right:10px;'></i>&nbsp;<a href=watchedusers.php>".T_("YOUR_SHITLIST")."</a></td></tr>";
				echo "<tr><td style='font:12px Verdana' class='css' width='100%' align='left'><i class='fa-solid fa-laptop-code' style='color: green; float: left; padding-right:10px;'></i>&nbsp;<a href=admincp.php>".T_("STAFF_CP")."</a></td></tr>";
			}
			?>

		</table>

		<?php
		end_block();
	}

?>