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
dbconn(true);
loggedinonly();


	stdhead("Upload Activity");
if ($CURUSER["control_panel"] == "yes") {
	begin_frame("Upload Activity");
	$catorder = $_GET['order'];
$n_tor = get_row_count("torrents");
$n_peers = get_row_count("peers");
$upclass = 4; //===| Set your minimum class that can upload torrents
$query = "SELECT id, username, added, uploaded, downloaded, class FROM users WHERE class >= $upclass ORDER BY class DESC";
$result = SQL_Query_exec($query);
$num = mysqli_num_rows($result);
echo "<div style='margin-top:10px' align=center><font size=2>We have <font color=#0080FF><b>" . $num . "</b></font> members with upload rights</font></div>";
$zerofix = $num - 1;
echo "<table class='table_table' cellpadding='4' align='center' border='0'>";
	for ($i = 0; $i <= $zerofix; $i++) {if ($num > 0) {
$class = mysqli_result($result, $i, "class");
if ($i > 0)
$class2 = mysqli_result($result, $i-1, "class");
	if ($class != $class2) {
echo("<tr><td style='border:none' colspan='8' height='15'></td></tr>");
echo("<tr><td style='border:none' colspan='8'><b>Class</b>: <b>".get_user_class_name($class)."</b></td></tr>
<tr>
<td class='table_head' align='center'><b>Num</b></td>
<td class='table_head'><b>Username</b></td>
<td class='table_head'><b>Uploaded</b></td>
<td class='table_head'><b>Downloaded</b></td>
<td class='table_head'><b>Ratio</b></td>
<td class='table_head'><b>Uploaded Torrents</b></td>
<td class='table_head'><b>Last Upload</b></td>
<td class='table_head' align='center'><b>Contact</b></td>
</tr>");
}
	$id = mysqli_result($result, $i, "id");
$username = mysqli_result($result, $i, "username");
$added = mysqli_result($result, $i, "added");
$uploaded = mksize(mysqli_result($result, $i, "uploaded"));
$downloaded = mksize(mysqli_result($result, $i, "downloaded"));
$uploadedratio = mysqli_result($result, $i, "uploaded");
$downloadedratio = mysqli_result($result, $i, "downloaded");
$res = SQL_Query_exec("SELECT COUNT(*) FROM users WHERE class = $class");
$arr = mysqli_fetch_row($res);
$liczclass = $arr[0];
$upperquery = "SELECT added FROM torrents WHERE owner = $id";
$upperresult = SQL_Query_exec($upperquery);
$torrentinfo = mysqli_fetch_array($upperresult);
$numtorrents = mysqli_num_rows($upperresult);
	if ($downloaded > 0) {
$ratio = $uploadedratio / $downloadedratio;
$ratio = number_format($ratio, 2);
$color = get_ratio_color($ratio);
if ($color)
$ratio = "<font color=$color>$ratio</font>";
} else
if ($uploaded > 0)
$ratio = "Inf.";
else
$ratio = "---";
if ($class != $class2)
$counter = 0;
$counter = $counter + 1;
if ($numtorrents > 0) {
$lastadded = mysqli_result($upperresult, $numtorrents - 1, "added");
$add = "<td class='table_col1'>" . get_elapsed_time(sql_timestamp_to_unix_timestamp($lastadded)) . " ago (" . gmdate("d.M.Y",strtotime($lastadded)) . ")</td>";
} else
$add = "<td class='table_col1'><font color='#FF2000'><b>Nothing!</b></font></td>";
	echo "<tr>";
echo "<td class='table_col1' align='center'><b>$counter</b></td>";
echo "<td class='table_col2'><a href=/account-details.php?id=$id><b>".$username."</b></a></td>";
echo "<td class='table_col1'><font color=limegreen><b>$uploaded</b></font></td>";
echo "<td class='table_col2'><font color=#FF2000><b>$downloaded</b></font></td>";
echo "<td class='table_col1'><b>$ratio</b></td>";
echo "<td class='table_col2'><b><font color=#0080FF>$numtorrents</font> torrents</b> / total</td>\n";
echo $add;
echo "<td class='table_col2' align='center'><a href='mailbox.php?compose&id=$id'><img src='images/button_pm.gif' border='0'></a></td>";                echo "</tr>";
}
}
echo "</table>";
echo "<div style='margin-top:25px; margin-bottom:15px' align='center'><font size='2'><b>Torrents Stats</b></font></div>";
echo "<table class='table_table' cellpadding='4' align='center' border='0'>";
if ($n_tor == 0)
    stdmsg("Sorry", "There's no category!");
else {
if ($catorder == "category")
$orderby = "c.id";
elseif ($catorder == "lastul")
$orderby = "last DESC, c.id";
elseif ($catorder == "torrents")
$orderby = "n_t DESC, c.id";
elseif ($catorder == "peers")
$orderby = "n_p DESC, name";
else
$orderby = "c.id";
$res = SQL_Query_exec("SELECT c.name, c.parent_cat, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) AS n_p FROM categories as c LEFT JOIN torrents as t ON t.category = c.id LEFT JOIN peers as p ON t.id = p.torrent GROUP BY c.id ORDER BY $orderby");
	print("<tr>
<td class='table_head'><a href='upload-stats.php?order=category'><b>Category</b></a></td>
<td class='table_head'><a href='upload-stats.php?order=lastul'><b>Last Upload</b></a></td>
<td class='table_head' align='center'><a href='upload-stats.php?order=torrents'><b>Total Torrents</b></a></td>
<td class='table_head' align='center'><a href='upload-stats.php?order=torrents'><b>Torrents Rate</b></td>
<td class='table_head' align='center'><a href='upload-stats.php?order=peers'><b>Total Peers</b></a></td>
<td class='table_head' align='center'><a href='upload-stats.php?order=peers'><b>Peers Rate</b></a></td>
</tr>\n");
	while ($cat = mysqli_fetch_array($res)) {
$caty[] = "<tr>
<td class=table_col1><b><font color=#FF2000>" . $cat['parent_cat'] . "</font> " . $cat['name'] . "</b></a></td>
<td class=table_col2 " . ($cat['last']?(">".date("<b>d-M-Y</b> H:i", utc_to_tz_time($cat['last']))." (".get_elapsed_time(sql_timestamp_to_unix_timestamp($cat['last']))." ago)"):"><font color=#FF2000><b>Nothing!</b></font>") ."</td>                <td class=table_col1 align=center>" . $cat['n_t'] . "</td>
<td class=table_col2 align=center>" . number_format(100 * $cat['n_t']/$n_tor,1) . "%</td>
<td class=table_col1 align=center>" . $cat['n_p'] . "</td>
<td class=table_col2 align=center>" . ($n_peers > 0 ? number_format(100 * $cat['n_p']/$n_peers,1)."%":"---") . "</td>
</tr>\n";
}
echo implode("n",$caty);
$torrents = number_format($n_tor);
print("<td colspan='2' class='table_head' align='right'><b>Total Results</b>: </td>
<td class='table_head' align='center'>".$torrents."</td>
<td class='table_head' align='center'>".number_format(100 * $torrents/$n_tor,1) . "%</td>
<td class='table_head' align='center'>".$n_peers."</td>
<td class='table_head' align='center'>".number_format(100 * $n_peers/$n_peers,1)."%</td>");
}
echo "</table><br />";
end_frame();
} else
show_error_msg(T_("ERROR"), T_("SORRY_NO_RIGHTS_TO_ACCESS"), 1);
stdfoot();
?>