<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

$_POST = $_POST ?? [];
$_GET  = $_GET  ?? [];
$_REQUEST = $_REQUEST ?? array_merge($_GET, $_POST);

require_once("backend/functions.php");
dbconn();
loggedinonly();

stdhead(T_("USERCP"));

function navmenu()
	{

print("<div class='breadcrumb flat'>
		<a href='account.php'><span style='float:left; font-size:12px!important;'><i class='flaticon-profile' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("YOUR_PROFILE")." &nbsp;</span></a>

		<a href='account.php?action=edit_settings&do=edit'><span style='float:left; font-size:12px!important;'><i class='flaticon-settings' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("YOUR_SETTINGS")." &nbsp;</span></a>

		<a href='account.php?action=changepw'><span style='float:left; font-size:12px!important;'><i class='flaticon-key' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("CHANGE_PASS")." &nbsp;</span></a>

		<a href='account.php?action=mytorrents'><span style='float:left; font-size:12px!important;'><i class='flaticon-torrent-download' style='color:green;font-size:14px;'></i>&nbsp; ".T_("YOUR_TORRENTS")." &nbsp;</span></a>

		<a href='mailbox.php'><span style='float:left; font-size:12px!important;'><i class='flaticon-envelope' style='color:orange;font-size:14px;'></i>&nbsp; ".T_("YOUR_MESSAGES")." &nbsp;</span></a>

	</div><br />");
		
	}//end func

$action = $_REQUEST["action"] ?? null;
$do = $_REQUEST["do"] ?? null;

if (!$action){
	begin_frame(T_("USER").": $CURUSER[username] (".T_("ACCOUNT_PROFILE").")");

	$usersignature = stripslashes($CURUSER["signature"]);

	navmenu();
	?>
	<table class="f-border comment" cellpadding="5" cellspacing="5" border="0" width="100%">
	<tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("USERNAME"); ?>:</b></td><td valign="top" class="css-right"> <?php echo $CURUSER["username"]; ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("CLASS"); ?>:</b></td><td valign="top" class="css-right"> <?php echo $CURUSER["level"]; ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("EMAIL"); ?>:</b></td><td valign="top" class="css-right"> <?php echo $CURUSER["email"]; ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("JOINED"); ?>:</b></td><td valign="top" class="css-right"> <?php echo utc_to_tz($CURUSER["added"]); ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("AGE"); ?>:</b></td><td valign="top" class="css-right"> <?php echo $CURUSER["age"]; ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("GENDER"); ?>:</b></td><td valign="top" class="css-right"> <?php echo T_($CURUSER["gender"]); ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("PREFERRED_CLIENT"); ?>:</b></td><td valign="top" class="css-right"> <?php echo htmlspecialchars($CURUSER["client"]); ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("DONATED"); ?>:</b></td><td valign="top" class="css-right"> <?php echo $site_config['currency_symbol']; ?><?php echo number_format($CURUSER["donated"], 2); ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("CUSTOM_TITLE"); ?>:</b></td><td valign="top" class="css-right"> <?php echo format_comment($CURUSER["title"]); ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("ACCOUNT_PRIVACY_LVL"); ?>:</b></td><td valign="top" class="css-right"> <?php echo T_($CURUSER["privacy"]); ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("SIGNATURE"); ?>:</b></td><td valign="top" class="css-right"> <?php echo format_comment($usersignature); ?></td></tr>
	<td width="200" valign="top" class="css"><b><?php echo T_("PASSKEY"); ?>:</b></td><td valign="top" class="css-right"> <?php echo $CURUSER["passkey"]; ?></td></tr>
	<?php
		if ($CURUSER["invited_by"]) {
			$res = SQL_Query_exec("SELECT username FROM users WHERE id=$CURUSER[invited_by]");
			$row = mysqli_fetch_assoc($res);
			echo "<td width='200' valign='top' class='css'><b>".T_("INVITED_BY").":</b></td><td valign='top' class='css'> <a href=\"account-details.php?id=$CURUSER[invited_by]\">$row[username]</a></td></tr>";
		}
		echo "<td valign='top' width='200' class='css'><b>".T_("INVITES").":</b></td><td valign='top' class='css-right'> " . number_format($CURUSER["invites"]) . "</td></tr>";
		$invitees = array_reverse(explode(" ", $CURUSER["invitees"]));
		$rows = [];
		foreach ($invitees as $invitee) {
			$res = SQL_Query_exec("SELECT id, username FROM users WHERE id='$invitee' and status='confirmed'");
			if ($row = mysqli_fetch_assoc($res)) {
				$rows[] = "<a href=\"account-details.php?id=$row[id]\">$row[username]</a>";
			}
		}
		if ($rows)
			echo "<td width='200' valign='top' class='css'><b>".T_("INVITED").":</b></td><td valign='top' class='css-right'> ".implode(", ", $rows)."</td></tr>";
	?>
	<?php print("<td width='200' valign='top' class='css'><b>".T_("IP").":</b></td><td valign='top' class='css-right'> " . $CURUSER["ip"] . "\n"); ?></td></tr>
	</table>
	<br /><br />
	<?php
	end_frame();
}

/////////////// MY TORRENTS ////////////////////

if ($action=="mytorrents"){
begin_frame(T_("YOUR_TORRENTS"));
navmenu();
// Ensure 'page' is set and defaults to 0 if not
$page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
$perpage = 200;

// Get the total number of torrents
$res = SQL_Query_exec("SELECT COUNT(*) FROM torrents WHERE torrents.owner = " . $CURUSER["id"]);
$arr = mysqli_fetch_row($res);

// Calculate total pages
$pages = floor($arr[0] / $perpage);
if ($pages * $perpage < $arr[0]) {
    ++$pages;
}

// Validate the current page
if ($page < 1) {
    $page = 1;
} else if ($page > $pages) {
    $page = $pages;
}

// Calculate the offset for the query
$offset = ($page - 1) * $perpage; // Ensure correct offset calculation (zero-indexed)

$res = SQL_Query_exec("SELECT * FROM torrents WHERE torrents.owner = " . $CURUSER["id"] . " LIMIT $offset, $perpage");

// Generate page numbers
$pagemenu = '';
for ($i = 1; $i <= $pages; ++$i) {
    if ($i == $page) {
        $pagemenu .= "$i\n";
    } else {
        $pagemenu .= "<a href='account.php?action=mytorrents&amp;page=$i'>$i</a>\n";
    }
}

// Browse navigation (prev/next)
$browsemenu = "";
if ($page == 1) {
    $browsemenu .= "";
} else {
    $browsemenu .= "<a href='account.php?action=mytorrents&amp;page=" . ($page - 1) . "'>[Prev]</a>";
}

$browsemenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

if ($page == $pages) {
    $browsemenu .= "";
} else {
    $browsemenu .= "<a href='account.php?action=mytorrents&amp;page=" . ($page + 1) . "'>[Next]</a>";
}



$where = "WHERE torrents.owner = " . $CURUSER["id"] ."";
$orderby = "ORDER BY added DESC";

$query = SQL_Query_exec("SELECT torrents.id, torrents.category, torrents.name, torrents.added, torrents.hits, torrents.banned, torrents.comments, torrents.seeders, torrents.leechers, torrents.times_completed, categories.name AS cat_name, categories.parent_cat AS cat_parent FROM torrents LEFT JOIN categories ON category = categories.id $where $orderby LIMIT $offset,$perpage");

$allcats = mysqli_num_rows($query);
	if($allcats == 0) {
		echo '<div class="f-border comment"><br /><b>'.T_("NO_UPLOADS").'</b></div>';
	}else{
		print("<p align='center'>$pagemenu<br />$browsemenu</p>");
?>
    <table align="center" cellpadding="5" cellspacing="5" class="table_table" width="100%">
    <tr class="table_head">
        <th><?php echo T_("TYPE"); ?></th>
        <th><?php echo T_("NAME"); ?></th>
        <th><?php echo T_("COMMENTS"); ?></th>
        <th><?php echo T_("HITS"); ?></th>
        <th><?php echo T_("SEEDS"); ?></th>
        <th><?php echo T_("LEECHERS"); ?></th>
        <th><?php echo T_("COMPLETED"); ?></th>
        <th><?php echo T_("ADDED"); ?></th>
        <th><?php echo T_("EDIT"); ?></th>
    </tr>

<?php

		while($row = mysqli_fetch_assoc($query))
			{
			$char1 = 75; //cut length
			$smallname = CutName(htmlspecialchars($row["name"]), $char1);
			echo "<tr><td class='table_col2' align='center'>$row[cat_parent]: $row[cat_name]</td><td class='table_col1' align='left'><a href='torrents-details.php?id=$row[id]'>$smallname</a></td><td class='table_col2' align='center'><a href='comments.php?type=torrent&amp;id=$row[id]'>".number_format($row["comments"])."</a></td><td class='table_col1' align='center'>".number_format($row["hits"])."</td><td class='table_col2' align='center'>".number_format($row["seeders"])."</td><td class='table_col1' align='center'>".number_format($row["leechers"])."</td><td class='table_col2' align='center'>".number_format($row["times_completed"])."</td><td class='table_col1' align='center'>".get_elapsed_time(sql_timestamp_to_unix_timestamp($row["added"]))."</td><td class='table_col2'><a href='torrents-edit.php?id=$row[id]'>EDIT</a></td></tr>\n";
			}
		echo "</table><br />";
		print("<p align='center'>$pagemenu<br />$browsemenu</p>");
	}

end_frame();
}


/////////////////////// EDIT SETTINGS ////////////////
if ($action=="edit_settings"){

	if ($do=="edit"){
	begin_frame(T_("EDIT_ACCOUNT_SETTINGS"));

	navmenu();
	?>
	<form enctype="multipart/form-data" method="post" action="account.php">
	<input type="hidden" name="action" value="edit_settings" />
	<input type="hidden" name="do" value="save_settings" />
	<table class="f-border" cellspacing="5" cellpadding="5" width="100%" align="center">
	<?php

	$ss_r = SQL_Query_exec("SELECT * from stylesheets");
	$ss_sa = [];
	while ($ss_a = mysqli_fetch_assoc($ss_r))
	{
	  $ss_id = $ss_a["id"];
	  $ss_name = $ss_a["name"];
	  $ss_sa[$ss_name] = $ss_id;
	}
	ksort($ss_sa);
	reset($ss_sa);

	$stylesheets = ""; // Initialize outside the loop
	foreach($ss_sa as $ss_name => $ss_id)
	{
	  if ($ss_id == $CURUSER["stylesheet"]) $ss = " selected='selected'"; else $ss = "";
	  $stylesheets .= "<option value='$ss_id'$ss>$ss_name</option>\n";
	}

	$countries = "<option value='0'>----</option>\n";
	$ct_r = SQL_Query_exec("SELECT id,name from countries ORDER BY name");
	while ($ct_a = mysqli_fetch_assoc($ct_r))
	  $countries .= "<option value='$ct_a[id]'" . ($CURUSER["country"] == $ct_a['id'] ? " selected='selected'" : "") . ">$ct_a[name]</option>\n";

	$teams = "<option value='0'>--- ".T_("NONE_SELECTED")." ----</option>\n";
	$sashok = SQL_Query_exec("SELECT id,name FROM teams ORDER BY name");
	while ($sasha = mysqli_fetch_assoc($sashok))
		$teams .= "<option value='$sasha[id]'" . ($CURUSER["team"] == $sasha['id'] ? " selected='selected'" : "") . ">$sasha[name]</option>\n";


	$acceptpms = $_POST['acceptpms'] ?? 'no';
	print ("<tr><td width='200' valign='top' class='css'><b>" . T_("ACCEPT_PMS") . ":</b> </td><td class='css-right'><input type='radio' name='acceptpms'" . ($acceptpms ? " checked='checked'" : "") .
	  " value='yes' /><b>".T_("FROM_ALL")."</b> <input type='radio' name='acceptpms'" .
	  ($acceptpms ? "" : " checked='checked'") . " value='no' /><b>" . T_("FROM_STAFF_ONLY") . "</b><br /><i>".T_("ACCEPTPM_WHICH_USERS")."</i></td></tr>");

	$gender = "<option value='Male'" . ($CURUSER["gender"] == "Male" ? " selected='selected'" : "") . ">" . T_("MALE") . "</option>\n"
		 ."<option value='Female'" . ($CURUSER["gender"] == "Female" ? " selected='selected'" : "") . ">" . T_("FEMALE") . "</option>\n";

	// START CAT LIST SQL
	$categories = ''; //init cats like a boss
	$r = SQL_Query_exec("SELECT id,name,parent_cat FROM categories ORDER BY parent_cat ASC, sort_index ASC");
	if (mysqli_num_rows($r) > 0)
	{
		$categories .= "<table><tr>\n";
		$i = 0;
		while ($a = mysqli_fetch_assoc($r))
		{
		  $categories .=  ($i && $i % 2 == 0) ? "</tr><tr>" : "";
		  $categories .= "<td class='bottom' style='padding-right: 5px'><input name='cat$a[id]' type=\"checkbox\" " . (strpos($CURUSER['notifs'], "[cat$a[id]]") !== false ? " checked='checked'" : "") . " value='yes' />&nbsp;" .htmlspecialchars($a["parent_cat"]).": " . htmlspecialchars($a["name"]) . "</td>\n";
		  ++$i;
		}
		$categories .= "</tr></table>\n";
	}

	// END CAT LIST SQL
	function priv($name, $descr) {
		global $CURUSER;
		if ($CURUSER["privacy"] == $name)
			return "<input type=\"radio\" name=\"privacy\" value=\"$name\" checked=\"checked\" /> $descr";
		return "<input type=\"radio\" name=\"privacy\" value=\"$name\" /> $descr";
	}

	print("<tr><td width='200' valign='top' class='css'><b>" . T_("ACCOUNT_PRIVACY_LVL") . ":</b> </td><td align='left' class='css-right'>". priv("normal", "<b>" . T_("NORMAL") . "</b>") . " " . priv("low", "<b>" . T_("LOW") . "</b>") . " " . priv("strong", "<b>" . T_("STRONG") . "</b>") . "<br /><i>".T_("ACCOUNT_PRIVACY_LVL_MSG")."</i></td></tr>");
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("EMAIL_NOTIFICATION") . ":</b> </td><td align='left' class='css-right'><input type='checkbox' name='pmnotif' " . (strpos($CURUSER['notifs'], "[pm]") !== false ? " checked='checked'" : "") .
	   " value='yes' /><b>" . T_("PM_NOTIFY_ME") . "</b><br /><i>".T_("EMAIL_WHEN_PM")."</i></td></tr>");

	   //print("<tr><td align=right class=alt3 valign=top><b>".T_("CATEGORY_FILTER").": </b></td><td align=left class=alt3><i>The system will only display the following categories when browsing (uncheck all to disable filter).</i><br />".$categories."</td></tr>");

	print("<tr><td width='200' valign='top' class='css'><b>" . T_("THEME") . ":</b> </td><td align='left' class='css-right'><select name='stylesheet'>\n$stylesheets\n</select></td></tr>");
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("PREFERRED_CLIENT") .":</b> </td><td align='left' class='css-right'><input type='text' size='20' maxlength='20' name='client' value=\"" . htmlspecialchars($CURUSER["client"]) . "\" /></td></tr>");
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("AGE") . ":</b> </td><td align='left' class='css-right'><input type='text' size='3' maxlength='2' name='age' value=\"" . htmlspecialchars($CURUSER["age"]) . "\" /></td></tr>");
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("GENDER") . ":</b> </td><td align='left' class='css-right'><select size='1' name='gender'>\n$gender\n</select></td></tr>");
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("COUNTRY") . ":</b> </td><td align='left' class='css-right'><select name='country'>\n$countries\n</select></td></tr>");

/// START UPLOAD AVATAR ///
if ($site_config['AVATARUPLOAD']) {
    $avatar = htmlspecialchars($CURUSER["avatar"] ?? "");

 if (!$avatar)
	$avatar = "".$site_config["SITEURL"]."/images/default_avatar.png";
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("AVATAR") . ":</b></td><td valign='top' class='css-right'><table><tr><td><img src=$avatar height='40' width='40'></td><td ><input type='text' name='avatar' size='50' value=\"" . htmlspecialchars($CURUSER["avatar"]) . "\"><br /><i>" . T_("AVATAR_LINK") . "</i>&nbsp;or <a href=upavatar.php?action=upload><b>" . T_("UP_AVATAR") . "</b></a></td></tr></table></td></tr>");
	}
	else {
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("AVATAR_UPLOAD") . ":</b> </td><td align='left' class='css-right'><input type='text' name='avatar' size='50' value=\"" . htmlspecialchars($CURUSER["avatar"]) .
	  "\" /><br />\n<i>".T_("AVATAR_LINK")."</i><br /></td></tr>");
	 }
 ///END UPLOAD AVATAR ///

	if ($CURUSER["class"] > 1)
		print("<tr><td width='200' valign='top' class='css'><b>".T_("TEAM").":</b> </td><td align='left' class='css-right'><select name='teams'>\n$teams\n</select></td></tr>");

	print("<tr><td width='200' valign='top' class='css'><b>" . T_("AVATAR_UPLOAD") . ":</b> </td><td align='left' class='css-right'><input type='text' name='avatar' size='50' value=\"" . htmlspecialchars($CURUSER["avatar"]) .
	  "\" /><br />\n<i>".T_("AVATAR_LINK")."</i><br /></td></tr>");
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("CUSTOM_TITLE") . ":</b> </td><td align='left' class='css-right'><input type='text' name='title' size='50' value=\"" . strip_tags($CURUSER["title"]) .
	  "\" /><br />\n <i>" . T_("HTML_NOT_ALLOWED") . "</i></td></tr>");
	print("<tr><td width='200' valign='top' class='css'><b>" . T_("SIGNATURE") . ":</b> </td><td align='left' class='css-right'><textarea name='signature' cols='50' rows='10'>" . htmlspecialchars($CURUSER["signature"]) .
	  "</textarea><br />\n <i>".sprintf(T_("MAX_CHARS"), 150).", " . T_("HTML_NOT_ALLOWED") . "</i></td></tr>");

	print("<tr><td width='200' valign='top' class='css'><b>".T_("RESET_PASSKEY").":</b> </td><td align='left' class='css-right'><input type='checkbox' name='resetpasskey' value='1' />&nbsp;<i>".T_("RESET_PASSKEY_MSG").".</i></td></tr>");

    if ($site_config["SHOUTBOX"])
        print("<tr><td width='200' valign='top' class='css'><b>".T_("HIDE_SHOUT").":</b></td><td align='left' class='css-right'><input type='checkbox' name='hideshoutbox' value='yes' ".($CURUSER['hideshoutbox'] == 'yes' ? 'checked="checked"' : '')." />&nbsp;<i>".T_("HIDE_SHOUT_TEXT")."</i></td></tr> ");

    print("<tr><td width='200' valign='top' class='css'><b>" . T_("EMAIL") . ":</b> </td><td align='left' class='css-right'><input type=\"text\" name=\"email\" size=\"50\" value=\"" . htmlspecialchars($CURUSER["email"]) .
	  "\" /><br />\n<i>".T_("REPLY_TO_CONFIRM_EMAIL")."</i><br /></td></tr>");

	ksort($tzs);
	reset($tzs);

	$tz = "";  // Initialize $tz as an empty string

	foreach ($tzs as $key => $val) {
		if ($CURUSER["tzoffset"] == $key) {
			$tz .= "<option value=\"$key\" selected='selected'>$val[0]</option>\n";
		} else {
			$tz .= "<option value=\"$key\">$val[0]</option>\n";
		}
	}


	print("<tr><td width='200' valign='top' class='css'><b>".T_("TIMEZONE").":</b> </td><td align='left' class='right'><select name='tzoffset'>$tz</select></td></tr>");

	?>
	<tr><td colspan="2" align="center" class="css"><input type="submit" value="<?php echo T_("SUBMIT");?>" /> <input type="reset" value="<?php echo T_("REVERT");?>" /></td></tr>
	</table></form>

	<?php
	end_frame();
	}


	if ($do == "save_settings"){
	$message = '';
	begin_frame(T_("EDIT_ACCOUNT_SETTINGS"));

	navmenu();
		$set = [];
		  $updateset = [];
		  $changedemail = $newsecret = 0;

          $email = $_POST["email"];
		  if ($email != $CURUSER["email"]) {
				if (!validemail($email))
					$message = T_("NOT_VALID_EMAIL");
				$changedemail = 1;
		  }

		  $acceptpms = $_POST["acceptpms"];
		  $pmnotif = $_POST['pmnotif'] ?? $_GET['pmnotif'] ?? $_REQUEST['pmnotif'] ?? 'no';
		  $privacy = $_POST["privacy"];
		  $notifs = ($pmnotif == 'yes' ? "[pm]" : "");
		  $r = SQL_Query_exec("SELECT id FROM categories");
		  $rows = mysqli_num_rows($r);
		  for ($i = 0; $i < $rows; ++$i) {
				$a = mysqli_fetch_assoc($r);
				$catKey = 'cat' . $a['id'];
				if (isset($_POST[$catKey]) && $_POST[$catKey] === 'yes') {
				$notifs .= "[cat" . $a['id'] . "]";
				}
		  }

		  if (!empty($_POST['resetpasskey'])) {
			$updateset[] = "passkey=''";
			}

          $avatar = strip_tags( $_POST["avatar"] );

          if ( $avatar != null )
          {
               # Allowed Image Extenstions.
               $allowed_types = &$site_config["allowed_image_types"];

               # We force http://
               if ( !preg_match( "#^\w+://#i", $avatar ) ) $avatar = "http://" . $avatar;

               # Clean Avatar Path.
               $avatar = cleanstr( $avatar );

               # Validate Image.
               $im = @getimagesize( $avatar );

               if ( !$im[ 2 ] || !@array_key_exists( $im['mime'], $allowed_types ) )
                     $message = "The avatar url was determined to be of a invalid nature.";

               # Save New Avatar.
               $updateset[] = "avatar = " . sqlesc($avatar);
          }

		  $title = strip_tags($_POST["title"]);
		  $signature = $_POST["signature"];
		  $stylesheet = $_POST["stylesheet"];
		  $resetpasskey = $_POST['resetpasskey'] ?? null;
		  $language = $_POST['language'] ?? null;
		  $client = strip_tags($_POST["client"]);
		  $age = $_POST["age"];
		  $gender= $_POST["gender"];
		  $country = $_POST["country"];
		  $teams = $_POST["teams"];
		  $privacy = $_POST["privacy"];
		  $timezone = (int)$_POST['tzoffset'];

		  if (is_valid_id($stylesheet))
			$updateset[] = "stylesheet = '$stylesheet'";
		  if (is_valid_id($language))
			$updateset[] = "language = '$language'";
		  if (is_valid_id($teams))
			$updateset[] = "team = '$teams'";
		  if (is_valid_id($country))
			$updateset[] = "country = $country";
		  if ($acceptpms == "yes")
			$acceptpms = 'yes';
		  else
			$acceptpms = 'no';
		  if (is_valid_id($age))
				$updateset[] = "age = '$age'";

          $hideshoutbox = isset($_POST['hideshoutbox']) && $_POST['hideshoutbox'] === 'yes' ? 'yes' : 'no';

            $updateset[] = "hideshoutbox = ".sqlesc($hideshoutbox);
			$updateset[] = "acceptpms = ".sqlesc($acceptpms);
			$updateset[] = "commentpm = " . sqlesc($pmnotif == "yes" ? "yes" : "no");
			$updateset[] = "notifs = ".sqlesc($notifs);
			$updateset[] = "privacy = ".sqlesc($privacy);
			$updateset[] = "gender = ".sqlesc($gender);
			$updateset[] = "client = ".sqlesc($client);
			$updateset[] = "signature = ".sqlesc($signature);
			$updateset[] = "title = ".sqlesc($title);
			$updateset[] = "tzoffset = $timezone";

		  /* ****** */

		  if (!$message) {

			if ($changedemail) {
				$sec = mksecret();
				$hash = md5($sec . $email . $sec);
				$obemail = rawurlencode($email);
				$updateset[] = "editsecret = " . sqlesc($sec);
				$thishost = $_SERVER["HTTP_HOST"];
				$thisdomain = preg_replace('/^www\./is', "", $thishost);
$body = <<<EOD
You have requested that your user profile (username {$CURUSER["username"]})
on {$site_config["SITEURL"]} should be updated with this email address ($email) as
user contact.

If you did not do this, please ignore this email. The person who entered your
email address had the IP address {$_SERVER["REMOTE_ADDR"]}. Please do not reply.

To complete the update of your user profile, please follow this link:

{$site_config["SITEURL"]}/account-ce.php?id={$CURUSER["id"]}&secret=$hash&email=$obemail

Your new email address will appear in your profile after you do this. Otherwise
your profile will remain unchanged.
EOD;

				sendmail($email, "$site_config[SITENAME] profile update confirmation", $body, "From: $site_config[SITEEMAIL]", "-f$site_config[SITEEMAIL]");
				$mailsent = 1;
			} //changedemail

			SQL_Query_exec("UPDATE users SET " . implode(",", $updateset) . " WHERE id = " . $CURUSER["id"]."");
			$edited=1;
			echo "<br /><br /><center><b><font class='error'>Updated OK</font></b></center><br /><br />";
			if ($changedemail) {
				echo "<br /><center><b>".T_("EMAIL_CHANGE_SEND")."</b></center><br /><br />";
			}
		  }else{
			echo "<br /><br /><center><b><font class='error'>".T_("ERROR").": ".$message."</font></b></center><br /><br />";
		  }// message


		end_frame();
	}// end do

}//end action

if ($action=="changepw"){

	if ($do=="newpassword"){

        $chpassword = $_POST['chpassword'];
        $passagain = $_POST['passagain'];

        if ($chpassword != "") {

					if (strlen($chpassword) < 6)
						$message = T_("PASS_TOO_SHORT");
					if ($chpassword != $passagain)
						$message = T_("PASSWORDS_NOT_MATCH");
					$chpassword = passhash($chpassword);
                    $secret = mksecret();
		}

		if ((!$chpassword) || (!$passagain))
			$message = "You must enter something!";

		begin_frame();
		navmenu();

		if (!$message){
			SQL_Query_exec("UPDATE users SET password = " . sqlesc($chpassword) . ", secret = " . sqlesc($secret) . "  WHERE id = " . $CURUSER["id"]);
			echo "<br /><br /><center><b>".T_("PASSWORD_CHANGED_OK")."</b></center>";
			logoutcookie();
		}else{
			echo "<br /><br /><b><center>".$message."</center></b><br /><br />";
		}


		end_frame();
		stdfoot();
		die();
	}//do

	begin_frame(T_("CHANGE_YOUR_PASS"));
	navmenu();
	?>

	<form method="post" action="account.php?action=changepw">
	<input type="hidden" name="do" value="newpassword" />
    <div class="f-border">
    <br />
    <table border="0" align="center" cellpadding="10">
    <tr class="alt3">
        <td width='200' valign='top' class='css'><b><?php echo T_("NEW_PASSWORD"); ?>:</b></td>
        <td valign='top' class='css-right'><input type="password" name="chpassword" size="50" /></td>
    </tr>
    <tr class="alt3">
        <td width='200' valign='top' class='css'><b><?php echo T_("REPEAT"); ?>:</b></td>
        <td valign='top' class='css-right'><input type="password" name="passagain" size="50" /></td>
    </tr>
    <tr class="alt2">
        <td colspan="2" align="center" class="css">
        <input type="reset" value="<?php echo T_("REVERT"); ?>" />
        <input type="submit" value="<?php echo T_("SUBMIT"); ?>" />
        </td>
    </tr>
    </table>
    <br />
    </div>
	</form>

	<?php
	end_frame();
}

stdfoot();
?>
