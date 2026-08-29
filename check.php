<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

error_reporting(E_ALL ^ E_NOTICE);

if (isset($_GET["phpinfo"])) {
if ($_GET["phpinfo"] == 1){
	echo "<br /><center><a href='check.php'>Back To Check</a></center><br /><br />";
	phpinfo();
	die();
}
}

function get_php_setting($val) {
	$r =(ini_get($val) == '1' ? 1 : 0);
	return $r ? 'ON' : 'OFF';
}

function writableCell( $folder, $relative=1, $text='' ) {
	$writeable = '<b><span class="ok">Writeable</span></b>';
	$unwriteable = '<b><span class="err">Unwriteable</span></b>';

	echo '<tr>';
	echo '<td>' . $folder . '</td>';
	echo '<td align="right">';
	if ( $relative ) {
		echo is_writable( "./$folder" ) ? $writeable : $unwriteable;
	} else {
		echo is_writable( "$folder" ) ? $writeable : $unwriteable;
	}
    echo '</td>';
	echo '</tr>';
}


view();


function view() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>TorrentTrader Check</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style type="text/css">
	:root {
		--bg: #0e1116;
		--panel: #161b22;
		--panel-alt: #1c2230;
		--border: #2a3140;
		--text: #d7dde5;
		--muted: #8b96a5;
		--accent: #4f8cff;
		--ok: #35d07f;
		--err: #ff5c5c;
		--radius: 10px;
	}

	* { box-sizing: border-box; }

	html, body {
		margin: 0;
		padding: 0;
		background: var(--bg);
		color: var(--text);
		font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, Arial, sans-serif;
		font-size: 14px;
		line-height: 1.5;
	}

	.wrap {
		max-width: 960px;
		margin: 0 auto;
		padding: 32px 20px 60px;
	}

	.header {
		text-align: center;
		margin-bottom: 24px;
	}

	.header h1 {
		font-size: 22px;
		font-weight: 600;
		margin: 0 0 4px;
		color: #fff;
	}

	.header .subtitle {
		color: var(--muted);
		font-size: 13px;
		margin-bottom: 18px;
	}

	.actions {
		display: flex;
		justify-content: center;
		gap: 10px;
		flex-wrap: wrap;
		margin-bottom: 8px;
	}

	.btn, input.button {
		background: var(--panel-alt);
		border: 1px solid var(--border);
		color: var(--text);
		padding: 8px 16px;
		border-radius: 8px;
		font-size: 13px;
		cursor: pointer;
		text-decoration: none;
		display: inline-block;
		transition: border-color .15s ease, background .15s ease;
	}

	.btn:hover, input.button:hover {
		border-color: var(--accent);
		background: #1e2636;
	}

	h2, .section-title {
		font-size: 16px;
		font-weight: 600;
		color: #fff;
		margin: 34px 0 10px;
		padding-bottom: 8px;
		border-bottom: 1px solid var(--border);
	}

	.card {
		background: var(--panel);
		border: 1px solid var(--border);
		border-radius: var(--radius);
		padding: 16px 18px;
		margin-bottom: 18px;
	}

	.card p, .card a { color: var(--muted); }
	.card a { color: var(--accent); }

	table {
		width: 100%;
		border-collapse: collapse !important;
		background: var(--panel);
		border: 1px solid var(--border) !important;
		border-radius: var(--radius);
		overflow: hidden;
		margin-bottom: 20px;
	}

	table, td, th, tr {
		border-color: var(--border) !important;
	}

	td, th {
		vertical-align: top;
		padding: 9px 12px !important;
		border: 1px solid var(--border) !important;
	}

	th {
		background: var(--panel-alt);
		text-align: left;
		color: #fff;
		font-weight: 600;
	}

	tr:nth-child(even) td {
		background: rgba(255,255,255,0.015);
	}

	tr:hover td {
		background: rgba(79,140,255,0.06);
	}

	td[align="right"] {
		text-align: right;
	}

	.ok, span.ok, font.ok {
		color: var(--ok) !important;
		font-weight: 600;
	}

	.err, span.err, font.err {
		color: var(--err) !important;
		font-weight: 600;
	}

	b { color: inherit; }
	fieldset.field {
		border: 1px solid var(--border);
		border-radius: 8px;
		background: var(--panel-alt);
		padding: 14px 16px 12px;
		margin: 4px 0 0;
	}

	fieldset.field legend {
		padding: 0 8px;
		font-size: 12px;
		text-transform: uppercase;
		letter-spacing: .04em;
		color: var(--muted);
		font-weight: 600;
	}

	fieldset.field .value {
		font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
		font-size: 13px;
		word-break: break-word;
	}
</style>
</head>
<body>
<div class="wrap">

<div class="header">
	<h1>TorrentTrader v3.8.3 Config Check</h1>
	<div class="subtitle"> MicroMonkey, Coco, Botanicar </div>
	<div class="actions">
		<input type="button" class="button" value="Check Again" onclick="window.location=window.location" />
		<a class="btn" href="check.php?phpinfo=1">PHPInfo</a>
		<a class="btn" href='index.php'>Return to your homepage</a>
	</div>
</div>

<div class="section-title">Required Settings Check</div>
<div class="card">
<p>Disable MYSQL STRICT MODE or you MAY experience errors. In my tests this base script works fine in strict. Here is a reference for you <br /> <a class='btn' href='https://www.linode.com/community/questions/17070/how-can-i-disable-mysql-strict-mode' target= "_blank">Click here to follow link</a></p>
	
	<?php
require_once("backend/mysql.php");
	$link = mysqli_connect($mysql_host, $mysql_user, $mysql_pass);
			$stmt = mysqli_query($link, "SHOW VARIABLES LIKE 'sql_mode'")->fetch_row();
			   if (!$stmt) {
				   echo "<fieldset class=\"field\"><legend class=\"err\">Error Getting SQL Mode</legend><span class=\"err\">Unable to retrieve the current SQL mode.</span></fieldset>";
			   } else {
			echo "<fieldset class=\"field\"><legend>Current SQL Mode</legend><span class=\"value\">".$stmt[1]."</span></fieldset>";
			}
			?>
</div>

<p style="color: var(--muted);">If any of these items are highlighted in red then please take actions to correct them. Failure to do so could lead to your installation not functioning correctly.<br />
This system check is designed for unix based servers, windows based servers may not give desired results.</p>

<table cellpadding="3" cellspacing="1" style="border-collapse: collapse" border="1">
<tr>
	<td>PHP version >= 8.1.0</td>
	<td>
	<?php
		echo phpversion() < '8.0' ? '<b><span class="err">No</span> 8.0 or above required</b>' : '<b><span class="ok">Yes</span></b>';
		echo " - Your PHP version is ".phpversion();
	?>
	</td>
</tr>

<tr>
	<td>&nbsp; - zlib compression support</td>
	<td><?php echo extension_loaded('zlib') ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>

<tr>
	<td>&nbsp; - XML support</td>
	<td><?php echo extension_loaded('xml') ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>

<tr>
	<td>&nbsp; - MySQLi support</td>
	<td><?php echo function_exists( 'mysqli_connect' ) ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>

<tr>
	<td>&nbsp; - curl support (Not required but external torrents may scrape faster)</td>
	<td><?php echo function_exists( 'curl_init' ) ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>
<tr>
	<td>&nbsp; - openSSL (for the torrent encryption mod)</td>
	<td><?php echo extension_loaded( 'openssl' ) ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>
<tr>
	<td>&nbsp; - gmp support (Required for IPv6)</td>
	<td><?php echo extension_loaded( 'gmp' ) ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>

<tr>
	<td>&nbsp; - bcmath support (Required for IPv6)</td>
	<td><?php echo extension_loaded( 'bcmath' ) ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>

<tr>
	<td>&nbsp; - hash_hmac support (Recommended - For better password encryption)</td>
	<td><?php echo function_exists( 'hash_hmac' ) ? '<b><span class="ok">Available</span></b>' : '<b><span class="err">Unavailable</span></b>'; ?></td>
</tr>

<tr>
	<td>backend/config.php (<b class="unavailable">chmod 444</b>)</td>
	<td>
	<?php
	$file = 'backend/config.php';
	if (is_writable($file)) {
		echo '<b><span class="err">Writeable</span></b><br />Warning: leaving backend/config.php writeable is a security risk';
	} else {
		echo '<b><span class="ok">Unwriteable</span></b>';
	}
	?>
	</td>
</tr>

<tr>
	<td>Document Root<br /><i><font size="1">(Use this for your PATHS in config.php)</span></i></td>
	<td><?php echo str_replace('\\', '/', getcwd()) ?></td>
</tr>

</table>


<div class="section-title">Recommended PHP Settings</div>
<p style="color: var(--muted); margin-top: -6px;">These settings are recommended for PHP in order to ensure full compatibility with TorrentTrader!.
However, TorrentTrader! will still operate if your settings do not quite match the recommended.</p>

<table cellpadding="3" cellspacing="1" style="border-collapse: collapse" border="1">
<tr><th width="500px">Directive</th><th>Recommended</th><th>Actual</th></tr>

<?php
$php_recommended_settings = array(array ('Safe Mode','safe_mode','OFF'),
array ('Display Errors (Can be off, but does make debugging difficult.)','display_errors','ON'),
array ('File Uploads','file_uploads','ON'),
//array ('Magic Quotes Runtime','magic_quotes_runtime','OFF'),
array ('Register Globals','register_globals','OFF'),
array ('Output Buffering','output_buffering','OFF'),
array ('Session auto start','session.auto_start','OFF'),
array ('allow_url_fopen (Required for external torrents)', 'allow_url_fopen', 'ON')
);

foreach ($php_recommended_settings as $phprec) {
	?>
	<tr>
	<td><?php echo $phprec[0]; ?>:</td>
	<td><?php echo $phprec[2]; ?>:</td>
	<td><b>
	<?php
	if ( get_php_setting($phprec[1]) == $phprec[2] ) {
	?>
		<span class="ok">
	<?php
	} else {
	?>
		<span class="err">
	<?php
	}
	echo get_php_setting($phprec[1]);
?>
</span></b>
</td></tr>
<?php
}
?>
</table>

<div class="section-title">Directory and File Permissions Check</div>
<p style="color: var(--muted); margin-top: -6px;">In order for TorrentTrader! to function correctly it needs to be able to access or write to certain files or directories.<br />
If you see "Unwriteable" you need to change the permissions on the file or directory to 777 (directories) or 666 (files) so that TorrentTrader to write to it.
<br />The censor.txt should be chmodded to <b>600</b>.
</p>

<table cellpadding="3" cellspacing="1" style='border-collapse: collapse' border="1" >
<?php
writableCell( 'backups' );
writableCell( 'uploads' );
writableCell( 'uploads/images' );
writableCell( 'cache' );
writableCell( 'cache/get_row_count' );
writableCell( 'cache/queries' );
writableCell( 'cache/diskcache' );
writableCell( 'import' );
writableCell( 'censor.txt', 1 );  
?>
</table>
<br />
<?php
require_once("backend/mysql.php");
echo "<div class=\"section-title\" style=\"margin-top:34px;\">Table Status Check</div>";
	$link = mysqli_connect($mysql_host, $mysql_user, $mysql_pass);
	if (!$link)
	printf("<span class=\"err\"><b>Failed to connect to database:</b></span> (%d) %s<br />", mysqli_errno($link), mysqli_error($link));
else {
	if (!mysqli_select_db($link, $mysql_db))
		printf("<span class=\"err\"><b>Failed to select database:</b></span> (%d) %s<br />", mysqli_errno($link), mysqli_error($link));
	else {
		$r = mysqli_query($link, "SHOW TABLES");
		if (!$r)
			printf("<span class=\"err\"><b>Failed to list tables:</b></span> (%d) %s<br />", mysqli_errno($link), mysqli_error($link));
		else {
			$tables = array();
			while($rr=mysqli_fetch_row($r))
			$tables[] = $rr[0];
			$arr[] = "announce";
			$arr[] = "bans";
			$arr[] = "blocks";
			$arr[] = "categories";
			$arr[] = "censor";
			$arr[] = "comments";
			$arr[] = "completed";
			$arr[] = "countries";
			$arr[] = "email_bans";
			$arr[] = "faq";
			$arr[] = "groups";
			$arr[] = "guests";
			$arr[] = "languages";
			$arr[] = "log";
			$arr[] = "messages";
			$arr[] = "news";
			$arr[] = "peers";
			$arr[] = "pollanswers";
			$arr[] = "polls";
			$arr[] = "ratings";
			$arr[] = "reports";
			$arr[] = "rules";
			$arr[] = "shoutbox";
			$arr[] = "stylesheets";
			$arr[] = "tasks";
			$arr[] = "teams";
			$arr[] = "torrentlang";
			$arr[] = "torrents";
			$arr[] = "users";
			$arr[] = "warnings";
            $arr[] = "forumcats";
            $arr[] = "forum_topics";
            $arr[] = "forum_posts";
            $arr[] = "forum_forums";
            $arr[] = "forum_readposts";
            $arr[] = "sqlerr";  

			echo "<table cellpadding='3' cellspacing='1' style='border-collapse: collapse' border='1'>";
			echo "<tr><th>Table</th><th>Status</th></tr>";
			foreach ($arr as $t)
				if (!in_array($t, $tables))
					echo "<tr><td>$t</td><td align='right'><span class=\"err\"><b>MISSING</b></span></td></tr>";
				else
					echo "<tr><td>$t</td><td align='right'><span class=\"ok\"><b>OK</b></span></td></tr>";
				echo "</table>";

			require("backend/config.php");
			echo "<br /><br /><b>Default Theme:</b> ";
			if (!is_numeric($site_config["default_theme"]))
				echo "<span class=\"err\"><b>Invalid.</b></span> (Not a number)";
			else {
				$res = mysqli_query($link,"SELECT uri FROM stylesheets WHERE id=$site_config[default_theme]");
				if ($row = mysqli_fetch_row($res)) {
					if (file_exists("themes/$row[0]/header.php"))
						echo "<span class=\"ok\"><b>Valid.</b></span> (ID: $site_config[default_theme], Path: themes/$row[0]/)";
					else
						echo "<span class=\"err\"><b>Invalid.</b></span> (No header.php found)";
				} else
					echo "<span class=\"err\"><b>Invalid.</b></span> (No theme found with ID $site_config[default_theme])";
		}

		echo "<br /><b>Default Language:</b> ";
		if (!is_numeric($site_config["default_language"]))
			echo "<span class=\"err\"><b>Invalid.</b></span> (Not a number)";
		else {
			$res = mysqli_query($link,"SELECT uri FROM languages WHERE id=$site_config[default_language]");
			if ($row = mysqli_fetch_row($res)) {
				if (file_exists("languages/$row[0]"))
					echo "<span class=\"ok\"><b>Valid.</b></span> (ID: $site_config[default_language], Path: languages/$row[0])";
				else
					echo "<span class=\"err\"><b>Invalid.</b></span> (File languages/$row[0] missing)";
			} else
				echo "<span class=\"err\"><b>Invalid.</b></span> (No language found with ID $site_config[default_language])";
			}
		}
	}
}
mysqli_free_result($res); ///not sure if this is really necessary, but whatever. Here it is.
mysqli_close($link);
?>

</div><!-- /.wrap -->
</body>
</html>
<?php
}//end func

?>