<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#   Created by M-Jay             #
#   Modified by MicroMonkey,     #
#   Coco, Botanicar, Spiritblue  #
#================================#

begin_frame( T_("ONLINE_USERS_TODAY") );

$expires = 60; // Cache time in seconds

if (($rows = $TTCache->Get("usersonlinetoday_block", $expires)) === false) {
$res = SQL_Query_exec("SELECT id, username, class, donated, warned FROM users WHERE enabled = 'yes' AND status = 'confirmed' AND username != 'System' AND UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(users.last_access) <= 86400 ORDER BY username");
  $rows = array();

while ($row = mysqli_fetch_assoc($res)) {
  $rows[] = $row;
}
  $TTCache->Set("usersonlinetoday_block", $rows, $expires);
}
echo "<div id='uOnline' class='list' align='left'>";
echo "<button class=\"Tsiz\"><i class=\"fas fa-database\"></i><font color=\"" . $site_config['system_color'] . "\">   ".T_("CLASS_SYSTEM")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-user\"></i><font color=\"" . $site_config['user_color'] . "\">   ".T_("CLASS_USER")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-user-tie\"></i><font color=\"" . $site_config['power_user_color'] . "\">   ".T_("CLASS_USER_POWER")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-ban text-red\"></i><font color=\"" . $site_config['uploader_color'] . "\">   ".T_("CLASS_USER_POWER")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-user-shield\"></i><font color=\"" . $site_config['vip_color'] . "\">   ".T_("CLASS_USER_VIP")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-user-cog\"></i><font color=\"" . $site_config['moderator_color'] . "\">   ".T_("CLASS_USER_MODERATOR")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-user-astronaut\"></i><font color=\"" . $site_config['super_moderator_color'] . "\">   ".T_("CLASS_USER_SUPER_MODERATOR")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-user-graduate\"></i><font color=\"" . $site_config['administrator_color'] . "\">   ".T_("CLASS_USER_ADMINISTRATOR")."</font></button> ";
echo "<button class=\"Tsiz\"><i class=\"fas fa-gavel\"></i><font color=\"" . $site_config['siteowner_color'] . "\">   ".T_("CLASS_USER_FONDATOR")."</font></button> ";
echo "<hr>";
if (!$rows) {
  echo T_("NO_USERS_ONLINE");
  } else {
echo "<ul>\n";
  for ($i = 0, $cnt = count($rows), $n = $cnt - 1; $i < $cnt; $i++) {
  $row = &$rows[$i];


switch ( $row['class'] ){

case 9:
$row["username"] = "<font color=\"" . $site_config['system_color'] . "\">" . $row["username"] . "</font>"; //Robot
break;
case 8:
$row["username"] = "<font color=\"" . $site_config['siteowner_color'] . "\">" . $row["username"] . "</font>"; //Fondator
break;
case 7:
$row["username"] = "<font color=\"" . $site_config['administrator_color'] . "\">" . $row["username"] . "</font>"; //Administrator
break;
case 6:
$row["username"] = "<font color=\"" . $site_config['super_moderator_color'] . "\">" . $row["username"] . "</font>"; //Power Moderator
break;
case 5:
$row["username"] = "<font color=\"" . $site_config['moderator_color'] . "\">" . $row["username"] . "</font>"; //Moderator
break;
case 4:
$row["username"] = "<font color=\"" . $site_config['uploader_color'] . "\">" . $row["username"] . "</font>"; //Uploader
break;
case 3:
$row["username"] = "<font color=\"" . $site_config['vip_color'] . "\">" . $row["username"] . "</font>"; //V.I.P
break;
case 2:
$row["username"] = "<font color=\"" . $site_config['power_user_color'] . "\">" . $row["username"] . "</font>"; //Power User
break;
case 1:
$row["username"] = "<font color=\"" . $site_config['user_color'] . "\">" . $row["username"] . "</font>"; //Members
break;
}

  $warned = null;

if ( $row['warned'] == 'yes' ){
  $warned = '<sup><i class="fas fa-ban text-red"></i></sup>';
}

  $donated = null;

if ($row['donated'] > 0){
  $donated = '<sup><i class="fas fa-star text-gold fa-spin"></i></sup>';
}

  echo "<li>  <a href='account-details.php?id=$row[id]'>  $row[username] $warned $donated  </a>".($i < $n ? ", " : "")."</li>\n";
}
  echo "</ul><br/></div>\n";
}
end_frame();
?>