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
	begin_block(T_("ONLINE_USERS"));

		$res = SQL_Query_exec("SELECT id, username FROM users WHERE enabled = 'yes' AND status = 'confirmed' AND username != 'System' AND UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(users.last_access) <= 86400");
		$rows = array();
		while ($row = mysqli_fetch_assoc($res))
		{
			$rows[] = $row;
		}

		if (!$rows)
		{
			echo T_("NO_USERS_ONLINE");
		}
		else
		{
			echo "<div id='uOnline' class='bMenu'>\n";
				echo "<ul>\n";
					for ($i = 0, $cnt = count($rows), $n = $cnt - 1; $i < $cnt; $i++)
					{
						$row = &$rows[$i];
						echo "<li><a href='account-details.php?id=$row[id]'><b>".$row[username]."</b></a>".($i < $n ? " &bull;" : "")."</li>\n";;
					}
				echo "</ul>\n";
			echo "</div>\n";
		}

	end_block();
}
?>
