<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

if ($CURUSER)
{
	$qry = SQL_Query_exec("SELECT COUNT(`hnr`) FROM `snatched` WHERE `uid` = '".$CURUSER["id"]."' AND `hnr` = 'yes'");
	$res2 = mysqli_fetch_row($qry);
	$hnr = "<font style=\"background-color:#252525\" color=\"#FEFEFE\"><b>&nbsp; ".$res2[0]." &nbsp;</font>";
	$hnr2 = $res2[0];
	$res_reports = SQL_Query_exec("SELECT COUNT(*) FROM reports WHERE dealtwith = '0'");
	$arr_reports = mysqli_fetch_row($res_reports);
	$num_reports = $arr_reports[0];
	$res_staffmessages = SQL_Query_exec("SELECT COUNT(*) FROM staffmessages WHERE answered = '0'");
	$arr_staffmessages = mysqli_fetch_row($res_staffmessages);
	$num_staffmessages = $arr_staffmessages[0];
	$res_donatemessages = SQL_Query_exec("SELECT COUNT(*) FROM donatemessages WHERE answered = '0'");
	$arr_donatemessages = mysqli_fetch_row($res_donatemessages);
	$num_donatemessages = $arr_donatemessages[0];
	$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " and unread='yes' AND location IN ('in','both')");
	$arr = mysqli_fetch_row($res);
	$unreadmail = $arr[0];
	if (($CURUSER["announce_read"] == "no" && $arr = announcement()) || ($hnr2 > 0) || ($num_reports > 0) || ($num_staffmessages > 0) || ($num_donatemessages > 0) || ($unreadmail > 0))
	{
		begin_frame(T_("ANNOUNCEMENTS_BAR"));

		// Start Hit and Run Warning
		If ($res2[0] > 0) {
			echo"<div style='margin-top:3px; margin-bottom:3px'>
				<table width=100% border=0 cellspacing=0 cellpadding=10>
					<tr>
						<td style='background: #AB0000' align=center>
							<div style='font: bold 15px Times New Roman; color:#FFBF00'>
								$CURUSER[username], ".T_("_YOU_HAVE")." ".$hnr." ".($hnr2 > 1 ? "".T_("RECORDINGS")."" : "".T_("RECORDING")."")." ".T_("FOR_HNR")."<font style='margin-left:2px'>!</font>
								<font style='margin-left:5px'>".T_("VIEW_RECORDINGS_IN")."</font> <a href=snatched.php><font size=3 color=#CBCBCB>".T_("YOUR_SNATCH_LIST")."</font></a><br />
								".T_("MUST_SEEDING_OR")." <a href=required.php><font size=3 color=#CBCBCB>".T_("TRADE_TO_DELETE")."</font></a>
								".($hnr2 > 1 ? "".T_("THESE_RECORDINGS")."" : "".T_("THIS_RECORDING")."")."
							</div>
						</td>
					</tr>
				</table>
			</div>\n";
		}
		// End Hit and Run Warning
		// Start Announcement for Reports
		if ($CURUSER["control_panel"]=="yes"){
			if ($num_reports > 0){
				echo"<a href=admincp.php?action=reports&do=view>
					<div style='margin-top:3px; margin-bottom:3px'>
						<table width=100% border=0 cellspacing=0 cellpadding=10>
							<tr>
								<td style='background: #AB0000' align=center>
									<div style='font: bold 14px Times New Roman; color:#FFBF00'>
										$CURUSER[username], $num_reports ".($num_reports > 1 ? "".T_("REPORTS_ARE")."" : "".T_("REPORT_IS")."")." ".T_("WAITING_TO_BE_SOLVED").".
										<font style='margin-left:5px'>".T_("CLICK_TO_VIEW_REPORTS")."</font><font style='margin-left:2px'>!</font>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</a>\n";
			}
		}
		// End Announcement for Reports
		// Start Announcement for Staff Messages
		if ($CURUSER["control_panel"]=="yes"){
			if ($num_staffmessages > 0){
				echo"<a href=staffbox.php>
					<div style='margin-top:3px; margin-bottom:3px'>
						<table width=100% border=0 cellspacing=0 cellpadding=10>
							<tr>
								<td style='background: #AB0000' align=center>
									<div style='font: bold 14px Times New Roman; color:#FFBF00'>
										$CURUSER[username], $num_staffmessages ".($num_staffmessages > 1 ? "".T_("MESSAGES_FOR_STAFF_ARE")."" : "".T_("MESSAGE_FOR_STAFF_IS")."")." ".T_("WAITING_ANSWER").".
										<font style='margin-left:5px'>".T_("CLICK_THIS_BAR_TO")."</font> ".($num_staffmessages > 1 ? "".T_("READ_THEM")."" : "".T_("READ_IT")."")."<font style='margin-left:2px'>!</font>
									</div>
								</td>
							</tr>
						</table>
					</div> 
				</a>\n";
			}
		}
		// End Announcement for Staff Messages
		// Start Announcement for Donated Messages
		if ($CURUSER["control_panel"]=="yes"){
			if ($num_donatemessages > 0){
				echo"<a href=donatebox.php>
					<div style='margin-top:3px; margin-bottom:3px'>
						<table width=100% border=0 cellspacing=0 cellpadding=10>
							<tr>
								<td style='background: #AB0000' align=center>
									<div style='font: bold 14px Times New Roman; color:#FFBF00'>
										$CURUSER[username], $num_donatemessages ".($num_donatemessages > 1 ? "".T_("MSGS_FOR_DONATION_ARE")."" : "".T_("MSG_FOR_DONATION_IS")."")." ".T_("WAITING_TO_BE_SOLVED").".
										<font style='margin-left:5px'>".T_("CLICK_THIS_BAR_TO")."</font> ".($num_donatemessages > 1 ? "".T_("READ_THEM")."" : "".T_("READ_IT")."")."<font style='margin-left:2px'>!</font>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</a>\n";
			}
		}
		// End Announcement for Donated Messages
		// Start Announcement for Private Messages
		if ($unreadmail){
			$res_messages = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " and unread='yes' AND location IN ('in','both')");
			$arr_messages = mysqli_fetch_row($res_messages);
			$num_messages = $arr_messages[0];
			if ($num_messages > 0){
				echo"<a href=mailbox.php?inbox>
					<div style='margin-top:3px; margin-bottom:3px'>
						<table width=100% border=0 cellspacing=0 cellpadding=10>
							<tr>
								<td style='background: #AB0000' align=center>
									<div style='font: bold 14px Times New Roman; color:#FFBF00'>
										$CURUSER[username], $num_messages ".($num_messages > 1 ? "".T_("PRIVATE_MESSAGES_ARE")."" : "".T_("PRIVATE_MESSAGE_IS")."")." ".T_("WAITING_FOR_YOU").".
										<font style='margin-left:5px'>".T_("CLICK_THIS_BAR_TO")."</font> ".($num_messages > 1 ? "".T_("READ_THEM")."" : "".T_("READ_IT")."")."<font style='margin-left:2px'>!</font>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</a>\n";
			}
		}
		// End Announcement for Private Messages
		// Site Anouncements
		if ($CURUSER && $CURUSER["announce_read"] == "no" && $arr = announcement()){
			$added = date("<\\b>d-M-Y<\\/\\b> H:i", utc_to_tz_time($arr["added"]));
			?>
			<script type="text/javascript" src="scripts/announcement.js"></script>
			<script type="text/javascript" src="scripts/preview.js"></script>
			<div id="dropin" style="position:absolute; visibility:hidden; center; top:100px; width:850px; height:100px">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="error" align="center">
					<tbody>
						<tr>
							<td class="none" style="padding: 2px 0 0 10px; background: #FFBF00">
								<font color=black><?php echo T_("TITLE"); ?>: <b><?=$arr["subject"]?></b> <?php echo T_("_CREATED_ON"); ?>: <?=$added ?></font>
							</td>
							<td align="right" class="none" style="padding: 5px; background: #FFBF00">
								<a href="#" onClick="dismissbox();return false"><img src="images/close.jpg" border="0"></a>
							</td>
						</tr>
						<tr>
							<td colspan="2" class=none width="100%" style="padding: 0 0 0 10px;" bgcolor="#252525">
								<div style="margin-top:10px; margin-bottom:10px">
									<font size=2 color="#CBCBCB"><?=format_comment($arr["message"]) ?></font>
								</div>
								<img id="loading" style="visibility: hidden" src="images/load.gif">
								<span style="color:#CBCBCB" name="preview" id="previewr" align="left">
									<div style="margin-top:10px; margin-bottom:10px" align="center">
										[<a href="#" onclick="javascript:clearannouncement(this.parentNode,'clear_ann.php')"><font color="#FFBF00"><b><?php echo T_("DELETE_THIS_ANN"); ?></b></font></a>]
									</div>
								</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<a href="javascript:show_announcement()">
				<div style='margin-top:3px; margin-bottom:3px'>
					<span id="new_ann" style="visibility: visible">
						<table width='100%' border="0" cellspacing="0" cellpadding="10" bgcolor="#AB0000">
							<tr>
								<td style='background: #AB0000' align='center'>
									<div style='font: bold 14px Times New Roman; color:#FFBF00'>
										<?php echo "".$CURUSER[username].""; ?>, <?php echo T_("NEW_ANN_CLICK_TO_READ"); ?><font style='margin-left:2px'>!</font>
									</div>
								</td>
							</tr>
						</table>
					</span>
				</div>
			</a>
			<?php
		} // End Site Announcements
		end_frame();
	}
}
?>
