<?php
#================================#
#       TorrentTrader 3.00       #
#  http://www.torrenttrader.uk   #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by Botanicar    #
#================================#


require_once("backend/functions.php");
require_once("mailbox-functions.php");
dbconn(false);
loggedinonly();

	function navmenu()
	{

print("<div class='breadcrumb flat'>
		<a href='account.php'><span style='float:left; font-size:12px!important;'><i class='flaticon-profile' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("YOUR_PROFILE")." &nbsp;</span></a>

		<a href='account.php?action=edit_settings&do=edit'><span style='float:left; font-size:12px!important;'><i class='flaticon-settings' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("YOUR_SETTINGS")." &nbsp;</span></a>

		<a href='account.php?action=changepw'><span style='float:left; font-size:12px!important;'><i class='flaticon-key' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("CHANGE_PASS")." &nbsp;</span></a>

		<a href='account.php?action=mytorrents'><span style='float:left; font-size:12px!important;'><i class='flaticon-torrent-download' style='color:green;font-size:14px;'></i>&nbsp; ".T_("YOUR_TORRENTS")." &nbsp;</span></a>

		<a href='mailbox.php'><span style='float:left; font-size:12px!important;'><i class='flaticon-envelope' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("YOUR_MESSAGES")." &nbsp;</span></a>

	</div><br />");
		
	}

	$readme = add_get('read') . '=';
	$unread = false;

	if (isset($_REQUEST['compose'])); // This blocks everything until done...

	if (isset($_GET['inbox'])) {
		$pagename = T_("INBOX");
		$tablefmt = "&nbsp;,Sender,Subject,Date";
		$where = "`receiver` = $CURUSER[id] AND `location` IN ('in','both')";
		$type = "Mail";
	} elseif (isset($_GET['outbox'])) {
		$pagename = T_("OUTBOX");
		$tablefmt = "&nbsp;,Sent_to,Subject,Date";
		$where = "`sender` = $CURUSER[id] AND `location` IN ('out','both')";
		$type = "Mail";
	} elseif (isset($_GET['draft'])) {
		$pagename = T_("DRAFT");
		$tablefmt = "&nbsp;,Sent_to,Subject,Date";
		$where = "`sender` = $CURUSER[id] AND `location` = 'draft'";
		$type = "Mail";
	} elseif (isset($_GET['templates'])) {
		$pagename = T_("TEMPLATES");
		$tablefmt = "&nbsp;,Subject,Date";
		$where = "`sender` = $CURUSER[id] AND `location` = 'template'";
		$type = "Mail";
	} else {
		$pagename = T_("OVERVIEW_MESSAGES");
		$type = "Overview";
	}

	//****** Send a message, or save after editing ******
	if (isset($_POST['send']) || isset($_POST['draft']) || isset($_POST['template'])) {
		if (!isset($_POST['template']) && !isset($_POST['change']) && (!isset($_POST['userid']) || !is_valid_id(($_POST['userid'] ?? null))))
			$error = "Unknown recipient";
		else {
			$sendto = (@($_POST['template'] ?? null) ? $CURUSER['id'] : @($_REQUEST['userid'] ?? null));
			if (isset($_POST['usetemplate']) && is_valid_id(($_POST['usetemplate'] ?? null))) {
				$res = SQL_Query_exec("SELECT * FROM messages WHERE `id` = $_POST[usetemplate] AND `location` = 'template' LIMIT 1");
				$arr = mysqli_fetch_array($res);
				$subject = $arr['subject'] . (@($_POST['oldsubject'] ?? null) ? " (was " . ($_POST['oldsubject'] ?? null) . ")" : "");
				$msg = sqlesc($arr['msg']);
			} else {
				$subject = @($_POST['subject'] ?? null);
				$msg = sqlesc(@($_POST['msg'] ?? null));
			}
			if ($msg) {
				$subject = sqlesc($subject);
				if ((isset($_POST['draft']) || isset($_POST['template'])) && isset($_POST['msgid']))
					SQL_Query_exec("UPDATE messages SET `subject` = $subject, `msg` = $msg WHERE `id` = $_POST[msgid] AND `sender` = $CURUSER[id]") or die("arghh");
				else {
					$to = (@($_POST['draft'] ?? null) ? 'draft' : (@($_POST['template'] ?? null) ? 'template' : (@($_POST['save'] ?? null) ? 'both' : 'in')));
					$status = (@($_POST['send'] ?? null) ? 'yes' : 'no');
					
					//=== Antiflood ===//=== BigMax ===//
						if (strlen($msg) > $site_config['floodsize'] && $CURUSER["control_panel"] != "yes")
							show_error_msg(T_("ERROR"), "".T_("MESSAGE_IS_TOO_LONG").".", 1);
						$res = SQL_Query_exec("SELECT COUNT(*) FROM `messages` WHERE sender = $CURUSER[id] AND added > '" . get_date_time(gmtime() - ($site_config['floodtime'] * 60)) . "'");
						$row = mysqli_fetch_row($res);
						if ($row[0] > $site_config['floodlimit'] && $CURUSER["control_panel"] != "yes")
							show_error_msg(T_("ERROR"), "".T_("EXCEDED_LIMIT")." $site_config[floodlimit] ".T_("MESSAGES_IN")." $site_config[floodtime] ".T_("MINUTES").".", 1);
					//=== End Antiflood ===//*/
					
					//===| Start Blocked Users |===//
					$blocked = SQL_Query_exec("SELECT id FROM blocked WHERE userid=$sendto AND blockid=$CURUSER[id]");
					$show = mysqli_num_rows($blocked);
					if ($show != 0 && $CURUSER["control_panel"] != "yes")
						show_error_msg(T_("ERROR"), "<div style='margin-top:10px; margin-bottom:10px' align='center'><font size=2 color=#FF2000><b>".T_("CANNOT_SEND_TO_THIS_MEMBER")."</b></font></div>", 1);
					//===| End Blocked Users |===//

					//===| Start Shout Condition |===//
					$days = $site_config['WAIT_TO_SHOUT'];
					$ages = get_date_time(gmtime() - $days);
					if($ages < $CURUSER["added"])
						show_error_msg(T_("ERROR"), "<div style='margin-top:10px; margin-bottom:10px' align='center'>".T_("POSTING_DISABLED")."</div>", 1);
					//===| End Shout Condition |===//

					//===| Start PMs Banned |===//
					if($CURUSER["pmbanned"] != 'no')
						show_error_msg(T_("ERROR"), "<div style='margin-top:10px; margin-bottom:10px' align='center'>".T_("MESSAGES_DISABLED")."</div>", 1);
					//===| End PMs Banned |===//
					
					SQL_Query_exec("INSERT INTO `messages` (`sender`, `receiver`, `added`, `subject`, `msg`, `unread`, `location`) VALUES ('$CURUSER[id]', '$sendto', '" . get_date_time() . "', $subject, $msg, '$status', '$to')") or die("Aargh!");
					//===| Spionul Mesajelor - BigMax |===//
					SQL_Query_exec("INSERT INTO `mesaje` (`sender`, `receiver`, `added`, `subject`, `msg`, `unread`, `location`) VALUES ('$CURUSER[id]', '$sendto', '" . get_date_time() . "', $subject, $msg, '$status', '$to')") or die("Aargh!");
					
					//email notif
					$res = SQL_Query_exec("SELECT id, acceptpms, notifs, email FROM users WHERE id='$sendto'");
					$user = mysqli_fetch_assoc($res);
					
					if (strpos($user['notifs'], '[pm]') !== false) {
						$cusername = $CURUSER["username"];
						
						$body = "".T_("RECEIVED_PM_FROM")." " . $cusername . "\n\n".T_("USE_URL_BELOW").".\n\n    " . $site_config['SITEURL'] . "/mailbox.php\n\n" . $site_config['SITENAME'] . "";
						
						sendmail($user["email"], "".T_("RECEIVED_PM_FROM")." $cusername", $body, "".T_("FROM").": $site_config[SITEEMAIL]", "-f$site_config[SITEEMAIL]");
					}
					//end email notif
					
					if (isset($_POST['msgid']))
						SQL_Query_exec("DELETE FROM messages WHERE `location` = 'draft' AND `sender` = $CURUSER[id] AND `id` = $_POST[msgid]") or die("arghh"); 
				}
				if (isset($_POST['send']))
					$info = "".T_("MESSAGE_SENT_OK")."" . (@($_POST['save'] ?? null) ? ", ".T_("A_COPY_HAS_SAVED")."" : "");
				else
					$info = "".T_("MESSAGE_SAVED_OK")."";
			} else
				$error = "".T_("UNABLE_SEND_MESSAGE")."";
		}
	}

	//****** Delete a message ******
	if (isset($_POST['remove']) && (isset($_POST['msgs']) || is_array(($_POST['remove'] ?? null)))) {
		if (is_array(($_POST['remove'] ?? null)))
			$tmp[] = key(($_POST['remove'] ?? null));
		else
			foreach (($_POST['msgs'] ?? null) as $key => $value)
				if (is_valid_id($key))
					$tmp[] = $key;
		$msgs = implode(', ', $tmp);
		if ($msgs) {
			if (isset($_GET['inbox'])) {
				SQL_Query_exec("DELETE FROM messages WHERE `location` = 'in' AND `receiver` = $CURUSER[id] AND `id` IN ($msgs)");
				SQL_Query_exec("UPDATE messages SET `location` = 'out' WHERE `location` = 'both' AND `receiver` = $CURUSER[id] AND `id` IN ($msgs)");
			} else {
				if (isset($_GET['outbox']))
					SQL_Query_exec("UPDATE messages SET `location` = 'in' WHERE `location` = 'both' AND `sender` = $CURUSER[id] AND `id` IN ($msgs)");
				SQL_Query_exec("DELETE FROM messages WHERE `location` IN ('out', 'draft', 'template') AND `sender` = $CURUSER[id] AND `id` IN ($msgs)");
			}
			$info = count($tmp ?? []) . " " . P_("message", count($tmp ?? [])) . " deleted";
		} else
			$error = "".T_("NO_MESSAGES_TO_DELETE")."";
	}

	//****** Mark a message as read - only if you're the recipient ******
	if (isset($_POST['mark']) && (isset($_POST['msgs']) || is_array(($_POST['mark'] ?? null)))) {
		if (is_array(($_POST['mark'] ?? null)))
			$tmp[] = key(($_POST['mark'] ?? null));
		else
			foreach (($_POST['msgs'] ?? null) as $key => $value)
				if (is_valid_id($key))
					$tmp[] = $key;
		$msgs = implode(', ', $tmp);
		if ($msgs) {
			SQL_Query_exec("UPDATE messages SET `unread` = 'no' WHERE `id` IN ($msgs) AND `receiver` = $CURUSER[id]");
			$info = count($tmp ?? []) . " " . P_("message", count($tmp ?? [])) . " marked as read";
		} else
			$error = "".T_("NO_MESSAGES_MARKED_AS_READ")."";
	}

stdhead($pagename, false);

	if (isset($_REQUEST['compose'])) 
{

if ($CURUSER['can_pp'] == 'no')
{
    show_error_msg(T_('ERROR'), "<div class='forumInfo'>". T_('NO_PM_WRITE') ."</div>", 1);
}
else
		begin_frame(T_("COMPOSE"));
		navmenu();

		$userid = @($_REQUEST['id'] ?? null);
		$subject = '';
		$msg = '';
		$to = '';
		$hidden = '';
		$output = '';
		$reply = false;
		$sreplay = T_("REPLY");	//--| Added by M-Jay

		if (is_array(($_REQUEST['compose'] ?? null)))	// In reply or followup to another msg
		{
			$msgid = key(($_REQUEST['compose'] ?? null));
			if (is_valid_id($msgid)) {
				$res = SQL_Query_exec("SELECT * FROM `messages` WHERE `id` = $msgid AND '$CURUSER[id]' IN (`sender`,`receiver`) LIMIT 1");
				if ($arr = mysqli_fetch_assoc($res)) {
					$subject = htmlspecialchars($arr['subject']);
					$msg .= htmlspecialchars($arr['msg']);
					//if (current(($_REQUEST['compose'] ?? null)) == 'Reply') {	//--| Canceled by M-Jay
					if (current(($_REQUEST['compose'] ?? null)) == $sreplay) {	//--| Added by M-Jay
						if ($arr['unread'] == 'yes' && $arr['receiver'] == $CURUSER['id'])
							SQL_Query_exec("UPDATE messages SET `unread` = 'no' WHERE `id` = $arr[id]");
						$reply = true;
						$userid = $arr['sender'];
						if (substr($arr['subject'], 0, 4) != 'Re: ')
							$subject = "Re: $subject";
					} else
						$userid = $arr['receiver'];
					$hidden .= "<input type='hidden' name='msgid' value='$msgid' />";
				}
			}
		}

		if (isset($_GET['templates']))
			$to = '".T_("WHO_CARES")."';
		elseif (is_valid_id($userid)) {
			$where = null;
			if ($CURUSER["view_users"] == "no" && $userid != $CURUSER["id"])
				$where = "AND acceptpms = 'yes'";
			
			# Allow users to PM themself's, Privacy is determined on acceptpms - (From All or Staff Only).   
			$res = SQL_Query_exec("SELECT username FROM users WHERE id = $userid AND status = 'confirmed' AND enabled = 'yes' $where");
			$row = mysqli_fetch_assoc($res);
			
			if (!$row) {
				print("".T_("NOT_PERMISSION_OR_NOT_EXIST")."");
				end_frame();
				stdfoot();
				die;
			}
			
			$to = class_user($row["username"]);
			$hidden .= "<input type='hidden' name='userid' value='$userid' />";
			if ($to == $CURUSER["username"])
				$to = "<font size=2 color=red>".T_("TO_YOURSELF")."</font>";
			$to = "<b>$to</b>";
		} else {
			$where = null;
			if ($CURUSER["view_users"] == "no")
				$where = "AND acceptpms = 'yes'";
			
			# Don't display yourself, Privacy is determined on acceptpms - (From All or Staff Only). 
			$res = SQL_Query_exec("SELECT id, username FROM users WHERE id != $CURUSER[id] AND enabled = 'yes' AND status = 'confirmed' $where ORDER BY username");
			
			if (mysqli_num_rows($res)) {
				$to = "<select name='userid'>\n";
				while ($arr = mysqli_fetch_assoc($res))
					$to .= "<option value='$arr[id]'>$arr[username]</option>\n";
				$to .= "</select>\n";
			}
		}
		if (isset($_GET['id']) && !$to)
			print T_("INVALID_USER_ID");
		elseif (!isset($_GET['id']) && !$to)
			print T_("NO_FRIENDS");
		else {
			/******** compose frame ********/
			
			begin_form(rem_get('compose'), 'name="compose"');
			if ($subject)
				$hidden .= "<input type='hidden' name='oldsubject' value='$subject' />";
			if ($hidden)
				print($hidden);
			echo "<table border='0' align='center' cellpadding='5' cellspacing='5' class='table table-bordered'>";
			if (!isset($_GET['templates'])) {
				tr2("<font size='2' color='red'>" . T_("MESSAGE_TO") . ":</font>&nbsp;&nbsp;", $to, 1);
				$res = SQL_Query_exec("SELECT * FROM `messages` WHERE `sender` = $CURUSER[id] AND `location` = 'template' ORDER BY `subject`");
				if (mysqli_num_rows($res)) {
					$tmp = "<div style='margin-top:7px'><select name='usetemplate' onchange='toggleTemplate(this);' style='width:350px'>\n<option name='0'>---</option>\n";
					while ($arr = mysqli_fetch_assoc($res))
						$tmp .= "<option value='$arr[id]'>$arr[subject]</option>\n";
					$tmp .= "</select></div>\n";
					tr2("<font size='2'>".T_("TEMPLATE").":</font>&nbsp;&nbsp;", $tmp, 1);
				}
			}
			tr2("<font size='2' color='red'>" . T_("SUBJECT") . ":</font>&nbsp;&nbsp;", "<div style='margin-top:7px'><input name='subject' type='text' class='btnHuge' value='$subject' size='45' /></div>", 1);
			require_once("backend/bbcode.php");
			echo "</table>";
			print textbbcode("compose", "msg", "$msg");
			echo "<table border='0' align='center' cellpadding='5' cellspacing='5' class='table table-bordered'>";
			
			if (!isset($_GET['templates']))
			$output .= "<input type='submit' name='send' class='btn btn-success' value='".T_("SEND")."' />&nbsp;<label>&nbsp;<input type='checkbox' name='save' checked='checked' /> ".T_("SAVE_COPY_IN_OUTBOX")."</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' class='btn btn-primary' name='draft' value='".T_("SAVE_DRAFT")."' />&nbsp;";
			tr2($output . "<input type='submit' class='btn btn-info' name='template' value='".T_("SAVE_TEMPLATE")."' />");
			echo "</table>";
			end_form();
			end_frame();
			stdfoot();
			die;
		}
		end_frame();
	}

	begin_frame($pagename);
	navmenu();


	if ($type == "Overview")
	{
		$res = SQL_Query_exec("SELECT COUNT(*), COUNT(`unread` = 'yes') FROM messages WHERE `receiver` = $CURUSER[id] AND `location` IN ('in','both')");
		$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND `location` IN ('in','both')");
		$inbox = mysqli_result($res, 0);
		$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `receiver` = " . $CURUSER["id"] . " AND `location` IN ('in','both') AND `unread` = 'yes'");
		$unread = mysqli_result($res, 0);
		$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `sender` = " . $CURUSER["id"] . " AND `location` IN ('out','both')");
		$outbox = mysqli_result($res, 0);
		$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `sender` = " . $CURUSER["id"] . " AND `location` = 'draft'");
		$draft = mysqli_result($res, 0);
		$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `sender` = " . $CURUSER["id"] . " AND `location` = 'template'");
		$template = mysqli_result($res, 0);
		
		if ($inbox == 1) { $inmes = T_("MESSAGE"); } else { $inmes = T_("MESSAGES"); }
		if ($unread == 1) { $unmes = T_("UNREAD"); } else { $unmes = T_("UNREADX"); }
		if ($outbox == 1) { $outmes = T_("MESSAGE"); } else { $outmes = T_("MESSAGES"); }
		if ($draft == 1) { $drames = T_("MESSAGE"); } else { $drames = T_("MESSAGES"); }
		if ($template == 1) { $temes = T_("MESSAGE"); } else { $temes = T_("MESSAGES"); }
		
		
print ("<div class='torrent-category-detail clearfix'>");
print ("<br /><table border='0' class='table table-bordered2' width='100%' cellpadding='5' cellspacing='5'><tr>");
print ("<td class='css'><a href='mailbox.php?inbox' class='btn btn-primary' align='left'><i class='far fa-envelope'></i> &nbsp; &nbsp; &nbsp;  INBOX </a></td><td class='css'> $inbox - - $inmes &nbsp;</td><td class='css-right'> ($unread -- <font color='red'> $unmes </font>) </td>");
print ("<tr><td class='css'><a href='mailbox.php?outbox' class='btn btn-success' align='left'><i class='far fa-envelope'></i> &nbsp; &nbsp; &nbsp;  OUTBOX</a></td><td class='css'> $outbox - -  $outmes </td> <td class='css-right'> <a href='mailbox.php?compose' class='btn btn-outline-success' align='left'><i class='far fa-envelope'></i> &nbsp; &nbsp; &nbsp;  SEND PM </a></td></tr>");
print ("<tr><td class='css'><a href='mailbox.php?draft' class='btn btn-warning' align='left'><i class='far fa-envelope'></i> &nbsp; &nbsp; &nbsp;  DRAFT</a></td><td class='css'>$draft - -  $drames </td></tr>");
print ("<tr><td class='css'><a href='mailbox.php?templates' class='btn btn-dark'><i class='far fa-envelope'></i> &nbsp; &nbsp; &nbsp;  TEMPLATES</a></td><td class='css'>$template - -  $temes </td>");
print ("</tr></table>");
print ("</div>");
echo"<br /><br />";


	}
	elseif ($type == "Mail")
	{
		$order = order("added,sender,receiver,subject", "added", true);
		$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE $where");
		$count = mysqli_result($res, 0);
		list($pagertop, $pagerbottom, $limit) = pager2(20, $count);
		
		if ($count >= 20) { print($pagertop); }

		begin_form();
		begin_table(0, "list");
		$table['&nbsp;'] = th_left("<div style='margin-top:5px; margin-bottom:15px'>&nbsp;<input type='checkbox' onclick='toggleChecked(this.checked);this.form.remove.disabled=true;' /></div>", 1);
		$table['Sender'] = th_center("".T_("SENDER")."", 'sender');
		$table['Sent_to'] = th_center("".T_("SENT_TO")."", 'sendto');
		$table['Subject'] = th_center("".T_("SUBJECT")."", 'subject');
		$table['Date'] = th_center("".T_("DATE_ADDED")."", 'added');
		table($table, $tablefmt);
		
		$res = SQL_Query_exec("SELECT * FROM messages WHERE $where $order $limit");
		while ($arr = mysqli_fetch_assoc($res)) {
			unset($table);
			$userid = 0;
			$format = '';
			$reading = false;
			
			if ($arr["sender"] == $CURUSER['id'])
				$sender = "<font size=2 color=red>".T_("YOURSELF")."</font>";
			elseif (is_valid_id($arr["sender"])) {
				$res2 = SQL_Query_exec("SELECT username FROM users WHERE `id` = $arr[sender]");
				$arr2 = mysqli_fetch_assoc($res2);
				$sender = "<a href='account-details.php?id=$arr[sender]'>" . (class_user($arr2["username"]) ? class_user($arr2["username"]) : "[".T_("DELETED")."]") . "</a>";
			} else
				$sender = "<font size=2 color=#DF11DF>System</font>";
			
			if ($arr["receiver"] == $CURUSER['id'])
				$sentto = "<font size=2 color=red>".T_("TO_YOURSELF")."</font>";
			elseif (is_valid_id($arr["receiver"])) {
				$res2 = SQL_Query_exec("SELECT username FROM users WHERE `id` = $arr[receiver]");
				$arr2 = mysqli_fetch_assoc($res2);
				$sentto = "<a href='account-details.php?id=$arr[receiver]'>" . (class_user($arr2["username"]) ? class_user($arr2["username"]) : "[".T_("DELETED")."]") . "</a>";
			} else
				$sentto = "<font size=2 color=#DF11DF>System</font>";
			
			$subject = ($arr['subject'] ? htmlspecialchars($arr['subject']) : "".T_("NO_SUBJECT")."");
			
			if (@($_GET['read'] ?? null) == $arr['id']) {
				$reading = true;
				if (isset($_GET['inbox']) && $arr["unread"] == "yes")
					SQL_Query_exec("UPDATE messages SET `unread` = 'no' WHERE `id` = $arr[id] AND `receiver` = $CURUSER[id]");
			}
			if ($arr["unread"] == "yes") {
				$format = "font-style: normal;";
				$unread = true;
			}
			
			$date = ("" . date("j M. Y | H:i", utc_to_tz_time($arr['added'])) . "");
			
			$table['&nbsp;'] = th_left("&nbsp;<input type='checkbox' name='msgs[$arr[id]]' " . ($reading ? "checked='checked'" : "") . " onclick='this.form.remove.disabled=true;' />", 1);
			$table['Sender'] = th_center("$sender", 1, $format);
			$table['Sent_to'] = th_center("$sentto", 1, $format);
			$table['Subject'] = th_left("<a href='javascript:read($arr[id]);'><img src='" . $site_config["SITEURL"] . "/images/plus.gif' id='img_$arr[id]' class='read' border='0' alt='' /></a>&nbsp;<a href='javascript:read($arr[id]);'>$subject</a>", 1, $format);
			$table['Date'] = th_center("" . $date, 1, $format);
			
			table($table, $tablefmt);
			
			$display = "<div style='margin-top:10px; margin-bottom:10px'>" . format_comment($arr['msg']) . "<br /><br />";
			if (isset($_GET['inbox']) && is_valid_id($arr["sender"]))
				$display .= "<input type='submit' class='btn btn-success' name='compose[$arr[id]]' value='".T_("REPLY")."' />&nbsp;\n";
			elseif (isset($_GET['draft']) || isset($_GET['templates']))
				$display .= "<input type='submit' class='btn btn-warning' name='compose[$arr[id]]' value='".T_("EDIT")."' />&nbsp;";
			if (isset($_GET['inbox']) && $arr['unread'] == 'yes')
				$display .= "<input type='submit' class='btn btn-primary' name='mark[$arr[id]]' value='".T_("MARK_AS_READ")."' />&nbsp;\n";
			$display .= "<input type='submit' class='btn btn-danger' name='remove[$arr[id]]' value='".T_("DELETE")."' />\n";
			$display .= "</div>";
			table(td_left($display, 1, "padding:0 7px 0 7px"), $tablefmt, "id='msg_$arr[id]' style='display:none;'");
		}
		
		$buttons = "<br><div style='margin-left:7px; margin-bottom:7px'><input type='button' class='btn btn-danger' value='".T_("DELETE_SELECTED")."' onclick='this.form.remove.disabled=!this.form.remove.disabled;' />";
		$buttons .= "<input type='submit' class='btn btn-success' name='remove' value='".T_("_CONFIRM")."' disabled='disabled' />";
		if (isset($_GET['inbox']) && $unread)
			$buttons .= "&nbsp; &nbsp;<input type='button' value='".T_("MARK_SELECTED_AS_READ")."' onclick='this.form.mark.disabled=!this.form.mark.disabled;' /><input type='submit' name='mark' value='".T_("_CONFIRM")."' disabled='disabled' /></div>";
		if (isset($_GET['templates']))
			$buttons .= "&nbsp;<input type='submit' name='compose' value='".T_("CREATE_NEW_TEMPLATE")."' />";
		table(td_left($buttons, 1, "border:0"), $tablefmt);
		
		end_table();
		end_form();
		
		if ($count >= 20) { print($pagerbottom); }
	}
	end_frame();
stdfoot();
?>