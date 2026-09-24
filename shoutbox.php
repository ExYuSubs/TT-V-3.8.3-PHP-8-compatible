<?php
#================================#
#       TorrentTrader 3.00       #
#  http://www.torrenttrader.uk   #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by Botanicar    #
#   Refurbished for PHP 8.3 /    #
#   MariaDB 10.11 + Public-only  #
#================================#

require_once("backend/functions.php");
dbconn();
?>

<script type="text/javascript">
<!--
function bbshout(repdeb, repfin) {
  var input = document.forms['shoutboxform'].elements['message'];
  input.focus();
  if (typeof document.selection != 'undefined') {
    var range = document.selection.createRange();
    var insText = range.text;
    range.text = repdeb + insText + repfin;
    range = document.selection.createRange();
    if (insText.length == 0) {
      range.move('character', -repfin.length);
    } else {
      range.moveStart('character', repdeb.length + insText.length + repfin.length);
    }
    range.select();
  }
  else if (typeof input.selectionStart != 'undefined') {
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    input.value = input.value.substr(0, start) + repdeb + insText + repfin + input.value.substr(end);
    var pos;
    if (insText.length == 0) {
      pos = start + repdeb.length;
    } else {
      pos = start + repdeb.length + insText.length + repfin.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  else {
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while (!re.test(pos)) {
      pos = prompt("Insert at position (0.." + input.value.length + "):", "0");
    }
    if (pos > input.value.length) {
      pos = input.value.length;
    }
    var insText = prompt("Please enter the text to format:");
    input.value = input.value.substr(0, pos) + repdeb + insText + repfin + input.value.substr(pos);
  }
}

function bbcolor() {
  var colorvalue = document.forms['shoutboxform'].elements['color'].value;
  bbshout("[color=" + colorvalue + "]", "[/color]");
}

function bbfont() {
  var fontvalue = document.forms['shoutboxform'].elements['font'].value;
  bbshout("[font=" + fontvalue + "]", "[/font]");
}

function bbsize() {
  var sizevalue = document.forms['shoutboxform'].elements['size'].value;
  bbshout("[size=" + sizevalue + "]", "[/size]");
}
//-->
</script>

<script type="text/javascript">
  function blink() {
    var blinks = document.getElementsByTagName('blink');
    for (var i = blinks.length - 1; i >= 0; i--) {
      var s = blinks[i];
      s.style.visibility = (s.style.visibility === 'visible') ? 'hidden' : 'visible';
    }
    window.setTimeout(blink, 1000);
  }
  if (document.addEventListener) document.addEventListener("DOMContentLoaded", blink, false);
  else if (window.addEventListener) window.addEventListener("load", blink, false);
  else if (window.attachEvent) window.attachEvent("onload", blink);
  else window.onload = blink;
</script>

<script type="text/javascript">
  function SmileIT(smile, form, text) {
    document.forms[form].elements[text].value = document.forms[form].elements[text].value + " " + smile + " ";
    document.forms[form].elements[text].focus();
  }
</script>

<script type="text/javascript">
<!--
function mySubmit() {
  setTimeout('document.shbox.reset()', 350);
}
function Smilies(Smilie) {
  document.shoutboxform.message.value += Smilie + " ";
  document.shoutboxform.message.focus();
}
//-->
</script>

<script type="text/javascript" src="scripts/ncode_imageresizer.js"></script>
<script type="text/javascript">
<!--
NcodeImageResizer.MODE = 'newwindow';
NcodeImageResizer.MAXWIDTH = 350;
NcodeImageResizer.MAXHEIGHT = 0;

NcodeImageResizer.Msg1 = 'Click for full image.';
NcodeImageResizer.Msg2 = 'This image has been resized. Click this bar to view the full image.';
NcodeImageResizer.Msg3 = 'This image has been resized. Click this bar to view the full image.';
NcodeImageResizer.Msg4 = 'Click for small image.';
//-->
</script>

<script type="text/javascript">
<!--
function Reply_code(smile, form, text) {
  document.forms[form].elements[text].value = document.forms[form].elements[text].value + " " + smile + " ";
  document.forms[form].elements[text].focus();
}
//-->
</script>

<?php

if (!empty($site_config['SHOUTBOX'])) {

    function quickbbshout()
    {
        echo "<table align='center' border=0 cellpadding=2 cellspacing=2><tr>";
        echo "<td style='padding-top:5px' align='center'>";

        echo "
        <a href=\"javascript:bbshout('[b]', '[/b]')\"> <img src=images/bbcode/bbcode_bold.gif border=0 alt='Bold' title='Bold' height='22px' style='vertical-align: -30%' /></a>
        <a href=\"javascript:bbshout('[i]', '[/i]')\"> <img src=images/bbcode/bbcode_italic.gif border=0 alt='Italic' title='Italic' height='22px' style='vertical-align: -30%' /></a>
        <a href=\"javascript:bbshout('[u]', '[/u]')\"> <img src=images/bbcode/bbcode_underline.gif border=0 alt='Underline' title='Underline' height='22px' style='vertical-align: -30%' /></a>
        <a href=\"javascript:bbshout('[center]', '[/center]')\"> <img src=images/bbcode/bbcode_center.gif border=0 alt='Center' title='Center' height='22px' style='vertical-align: -30%' /></a>
        <a href=\"javascript:bbshout('[url]', '[/url]')\"> <img src=images/bbcode/bbcode_url.gif border=0 alt='URL' title='URL' height='22px' style='vertical-align: -30%' /></a>
        <a href=\"javascript:bbshout('[img]', '[/img]')\"> <img src=images/bbcode/bbcode_image.gif border=0 alt='Image' title='Image' height='22px' style='vertical-align: -30%' /></a>
        <a href=\"javascript:bbshout('[videow=', '')\"> <img src=images/bbcode/bbcode_video.gif border=0 alt='Video' title='Video' height='22px' style='vertical-align: -30%' /></a>&nbsp;";

        echo "
        <select name='color' class='bb_icon' onChange=\"javascript:bbcolor()\">
        <option selected='selected'>" . T_("COLOR") . "</option>
        <option value=mediumturquoise style=color:mediumturquoise>Medium-Turquoise</option>
        <option value=dodgerblue style=color:dodgerblue>Dodger-Blue</option>
        <option value=slateblue style=color:slateblue>Slate-Blue</option>
        <option value=royalblue style=color:royalblue>Royal-Blue</option>
        <option value=orange style=color:orange>Orange</option>
        <option value=orangered style=color:orangered>Orange-Red</option>
        <option value=crimson style=color:crimson>Crimson</option>
        <option value=red style=color:red>Red</option>
        <option value=indianred style=color:indianred>Indian-Red</option>
        <option value=firebrick style=color:firebrick>Fire-Brick</option>
        <option value=green style=color:green>Green</option>
        <option value=limegreen style=color:limegreen>Lime-Green</option>
        <option value=seagreen style=color:seagreen>Sea-Green</option>
        <option value=hotpink style=color:hotpink>Hotpink</option>
        <option value=tomato style=color:tomato>Tomato</option>
        <option value=coral style=color:coral>Coral</option>
        <option value=mediumorchid style=color:mediumorchid>Medium-Orchid</option>
        <option value=magenta style=color:magenta>Magenta</option>
        <option value=burlywood style=color:burlywood>Burlywood</option>
        <option value=sandybrown style=color:sandybrown>Sandy-Brown</option>
        <option value=sienna style=color:sienna>Sienna</option>
        <option value=goldenrod style=color:goldenrod>Golden-Rod</option>
        <option value=teal style=color:teal>Teal</option>
        <option value=silver style=color:silver>Silver</option>
        </select>";

        echo "
        <select name='font' class='bb_icon' onChange=\"javascript:bbfont()\">
        <option selected='selected'>Font</option>
        <option value='Arial'>Arial</option>
        <option value='Arial Black'>Arial Black</option>
        <option value='Comic Sans MS'>Comic Sans MS</option>
        <option value='Courier New'>Courier New</option>
        <option value='Franklin Gothic Medium'>Franklin Gothic Medium</option>
        <option value='Georgia'>Georgia</option>
        <option value='Helvetica'>Helvetica</option>
        <option value='Impact'>Impact</option>
        <option value='Lucida Console'>Lucida Console</option>
        <option value='Lucida Sans Unicode'>Lucida Sans Unicode</option>
        <option value='Microsoft Sans Serif'>Microsoft Sans Serif</option>
        <option value='Palatino Linotype'>Palatino Linotype</option>
        <option value='Tahoma' style='font-family: Tahoma;'>Tahoma</option>
        <option value='Times New Roman'>Times New Roman</option>
        <option value='Trebuchet MS'>Trebuchet MS</option>
        <option value='Verdana'>Verdana</option>
        <option value='Symbol'>Symbol</option>
        </select>";

        echo "
        <select name='size' class='bb_icon' onChange=\"javascript:bbsize()\">
        <option selected='selected'>" . T_("SIZE") . "</option>
        <option value=1>1</option>
        <option value=2>2</option>
        <option value=3>3</option>
        <option value=4>4</option>
        <option value=5>5</option>
        <option value=6>6</option>
        <option value=7>7</option>
        </select>";

        echo "</td></tr><tr><td align='center'>";

        echo "
        <a href=\"javascript: SmileIT(':)','shoutboxform','message')\"><img border=0 src=images/smilies/smile.gif></a>
        <a href=\"javascript: SmileIT(':D','shoutboxform','message')\"><img border=0 src=images/smilies/grin.gif></a>
        <a href=\"javascript: SmileIT(':lol:','shoutboxform','message')\"><img border=0 src=images/smilies/lol.gif></a>
        <a href=\"javascript: SmileIT(':rofl:','shoutboxform','message')\"><img border=0 src=images/smilies/rofl.gif></a>
        <a href=\"javascript: SmileIT(':sarcastic:','shoutboxform','message')\"><img border=0 src=images/smilies/sarcastic.gif></a>
        <a href=\"javascript: SmileIT(':w00t:','shoutboxform','message')\"><img border=0 src=images/smilies/w00t.gif></a>
        <a href=\"javascript: SmileIT(':-/','shoutboxform','message')\"><img border=0 src=images/smilies/confused.gif></a>
        <a href=\"javascript: SmileIT(':|','shoutboxform','message')\"><img border=0 src=images/smilies/noexpression.gif></a>
        <a href=\"javascript: SmileIT(':(','shoutboxform','message')\"><img border=0 src=images/smilies/sad.gif></a>
        <a href=\"javascript: SmileIT(':cry:','shoutboxform','message')\"><img border=0 src=images/smilies/cry.gif></a>
        <a href=\"javascript: SmileIT(':ras:','shoutboxform','message')\"><img border=0 src=images/smilies/ras.gif></a>
        <a href=\"javascript: SmileIT(':pardon:','shoutboxform','message')\"><img border=0 src=images/smilies/pardon.gif></a>
        <a href=\"javascript: SmileIT(':cool:','shoutboxform','message')\"><img border=0 src=images/smilies/cool.gif></a>
        <a href=\"javascript: SmileIT(':wave:','shoutboxform','message')\"><img border=0 src=images/smilies/wave.gif></a>
        <a href=\"javascript: SmileIT(':ok:','shoutboxform','message')\"><img border=0 src=images/smilies/ok.gif></a>
        <a href=\"javascript: SmileIT(':hmm:','shoutboxform','message')\"><img border=0 src=images/smilies/hmm.gif></a>";
        echo "</td></tr></table>";
    }

    // ---- EDIT MESSAGE helper ----
    function linkit($al_url, $al_msg) // create autolink
    {
        echo "\n<meta http-equiv=\"refresh\" content=\"3; url=$al_url\">\n";
        echo "<center>\n";
        echo "<b>$al_msg</b>\n";
        echo "\n<b>Redirection ...</b>\n";
        echo "\n[ <a href='$al_url'>lien</a> ]\n";
        echo "</td>\n</tr>\n</table>\n</td>\n</tr>\n</table>\n</body>\n</html>\n";
        echo "</center>\n";
        exit;
    }

    // ---- DELETE MESSAGE ----
    if (isset($_GET['del'])) {
        if (is_numeric($_GET['del'])) {
            $delId = (int) $_GET['del'];
            $query = "SELECT * FROM shoutbox WHERE msgid=" . $delId;
            $result = SQL_Query_exec($query);
        } else {
            echo "invalid msg id STOP TRYING TO INJECT SQL";
            exit;
        }

        $row = $result ? mysqli_fetch_row($result) : null;

        if ($row && !empty($CURUSER) && (($CURUSER["edit_users"] ?? "no") == "yes" || $CURUSER['username'] == $row[1])) {
            $query = "DELETE FROM shoutbox WHERE msgid=" . $delId;
            SQL_Query_exec($query);
        }
    }

    // ---- INSERT MESSAGE ----
    $shoutbox_error = null;
    if (!empty($_POST['message']) && !empty($CURUSER)) {
        $messageEsc = sqlesc(trim($_POST['message']));
        $query = "SELECT COUNT(*) FROM shoutbox WHERE message=" . $messageEsc . " AND user=" . sqlesc($CURUSER['username']) . " AND UNIX_TIMESTAMP('" . get_date_time() . "')-UNIX_TIMESTAMP(date) < 30";
        $result = SQL_Query_exec($query);

        if (!$result) {
            error_log('Shoutbox duplicate-check query failed: ' . $query);
            $shoutbox_error = 'Greska pri slanju poruke, pokusaj ponovo.';
        } else {
            $row = mysqli_fetch_row($result);

            if ($row[0] == '0') {
                $query = "INSERT INTO shoutbox (msgid, user, message, date, userid, room) VALUES (NULL, " . sqlesc($CURUSER['username']) . ", " . $messageEsc . ", '" . get_date_time() . "', '" . (int) $CURUSER['id'] . "', 0)";
                $insertResult = SQL_Query_exec($query);

                if (!$insertResult) {
                    error_log('Shoutbox insert query failed: ' . $query);
                    $shoutbox_error = 'Greska pri slanju poruke, pokusaj ponovo.';
                } elseif (!empty($site_config['MOTM'])) {
                    // member of the month
                    $motm_value = 20;
                    motmadd($CURUSER["id"], $motm_value, $CURUSER['class'], $site_config['maxmotm']);
                }
            }
        }
    }

    // ---- THEME / LANGUAGE ----
    $THEME = $site_config['default_theme'] ?? '';
    if (!empty($CURUSER)) {
        $ss_result = SQL_Query_exec("select uri from stylesheets where id=" . (int) $CURUSER["stylesheet"]);
        $ss_a = $ss_result ? mysqli_fetch_assoc($ss_result) : null;
        if ($ss_a) {
            $THEME = $ss_a["uri"];
        }
    } else { // not logged in, use the default theme/language
        $ss_result = SQL_Query_exec("select uri from stylesheets where id=" . sqlesc($site_config['default_theme']));
        $ss_a = $ss_result ? mysqli_fetch_assoc($ss_result) : null;
        if ($ss_a) {
            $THEME = $ss_a["uri"];
        }
    }

    // ---- SHOUTBOX WIDGET (index / small box) ----
    if (!isset($_GET['history'])) {
        ?>
        <html>
        <head>
        <title><?php echo $site_config['SITENAME'] . T_("SHOUTBOX"); ?></title>

        <?php
        /* If you do change the refresh interval, you should also change index.php printf(T_("SHOUTBOX_REFRESH"), 5) the 5 is in minutes */
        ?>
        <meta http-equiv="refresh" content="300" />
        <link rel="stylesheet" type="text/css" href="<?php echo $site_config['SITEURL']; ?>/themes/<?php echo $THEME; ?>/theme-themable.css" />

<style type="text/css">
html,
body.shoutbox_body {
	background-color: var(--bg-soft) !important;
	color: var(--text);
}
<style type="text/css">

html,
body.shoutbox_body {
	background-color: var(--bg-soft) !important;
	color: var(--text);
}

/* Shoutbox tabela */
.shoutbox_table {
	border-collapse: separate;
	border-spacing: 0 5px;
	width: 100%;
}

/* Svaki red ima svoju pozadinu i border */
.shoutbox_table tr.shoutbox_alt > td {
	background: var(--shout-row-alt-bg) !important;
	border: 1px solid var(--shout-row-border) !important;
}

.shoutbox_table tr.shoutbox_noalt > td {
	background: var(--shout-row-noalt-bg) !important;
	border: 1px solid var(--shout-row-border) !important;
}

.shoutbox_date {
	display: inline-block;
	background: var(--shout-date-bg);
	padding: 2px 5px;
	margin-right: 3px;
	border: 1px solid var(--shout-date-border);
	border-radius: 3px;
}

.shoutbox_user {
	display: inline-block;
	background: var(--shout-user-bg);
	padding: 2px 5px;
	margin-right: 3px;
	border: 1px solid var(--shout-user-border);
	border-radius: 3px;
}

.shoutbox_actions {
	display: inline-block;
	background: var(--shout-actions-bg);
	padding: 2px 5px;
	margin-right: 3px;
	border: 1px solid var(--shout-actions-border);
	border-radius: 3px;
}

.shoutbox_message {
	display: inline-block;
	background: var(--shout-message-bg);
	padding: 2px 6px;
	margin-left: 2px;
	border: 1px solid var(--shout-message-border);
	border-radius: 3px;
}
</style>


<script type="text/javascript">
(function () {

	function syncShoutboxTheme() {
		try {
			var parentTheme = window.parent.document.documentElement.getAttribute('data-theme');

			if (parentTheme === 'light' || parentTheme === 'dark') {
				document.documentElement.setAttribute('data-theme', parentTheme);
			}
		} catch (e) {
			/* iframe fallback - nothing to do */
		}
	}

	/* Initial theme */ 
	syncShoutboxTheme();

	/* Follow the main page when the toggle changes */
	try {
		var parentHtml = window.parent.document.documentElement;

		var observer = new MutationObserver(function () {
			syncShoutboxTheme();
		});

		observer.observe(parentHtml, {
			attributes: true,
			attributeFilter: ['data-theme']
		});
	} catch (e) {
		/* iframe fallback - nothing to do */
	}

})();
</script>

<script type="text/javascript" src="<?php echo $site_config['SITEURL']; ?>/backend/java_klappe.js"></script>
        </head>
        <body class="shoutbox_body">
        <?php
        echo '<div class="shoutbox_contain"><table class="shoutbox_table" border="0" style="width: 100%; table-layout: fixed;">';
        // rooms removed - public only, no room selector needed
    } else {
        // ---- SHOUTBOX HISTORY PAGE ----
        if (!empty($site_config["MEMBERSONLY"])) {
            loggedinonly();
        }

        stdhead();
        begin_frame(T_("SHOUTBOX_HISTORY"));

        echo '<div class="shoutbox_history">';

        $query = 'SELECT COUNT(*) FROM shoutbox';
        $result = SQL_Query_exec($query);
        $row = $result ? mysqli_fetch_row($result) : [0];
        $pages = (int) round($row[0] / 100) + 1;
        $i = 1;
        echo '<div align="center">';

        while ($pages > 0) {
            echo "<a href='" . $site_config['SITEURL'] . "/shoutbox.php?history=1&amp;page=" . $i . "'>[" . $i . "]</a>&nbsp;";
            $i++;
            $pages--;
        }

        echo '</div><br /><table border="0" style="width: 99%; table-layout:fixed">';
    }

    // ---- LIST MESSAGES ----
    if (isset($_GET['history'])) {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page > 1) {
            $lowerlimit = $page * 100 - 100;
            $upperlimit = 100;
        } else {
            $lowerlimit = 0;
            $upperlimit = 100;
        }
        $query = 'SELECT s.* FROM shoutbox s INNER JOIN users u ON u.id = s.userid ORDER BY s.msgid DESC LIMIT ' . $lowerlimit . ',' . $upperlimit;
    } else {
        $query = 'SELECT s.* FROM shoutbox s INNER JOIN users u ON u.id = s.userid ORDER BY s.msgid DESC LIMIT 50';
    }

    $result = SQL_Query_exec($query);
    $alt = false;
    $canEdit = !empty($CURUSER) && (($CURUSER["edit_users"] ?? "no") == "yes");

    if (!$result) {
        error_log('Shoutbox list query failed: ' . $query);
    }

while ($result && ($row = mysqli_fetch_assoc($result))) {

    if ($alt) {
        echo '<tr class="shoutbox_noalt">';
        $alt = false;
    } else {
        echo '<tr class="shoutbox_alt">';
        $alt = true;
    }

    /* ---------------------------------------------------------
       DATE
       --------------------------------------------------------- */
    $date = "<span class='shoutbox_date'>"
        . date(' d M. H:i', utc_to_tz_time($row['date']))
        . "</span>";

    /* ---------------------------------------------------------
       USER
       --------------------------------------------------------- */
    if ($row['user'] == "System") {

        $name = "<span class='shoutbox_user'>System</span>";

    } else {

        $name = "<a class='shoutbox_user' href='"
            . $site_config['SITEURL']
            . "/account-details.php?id="
            . (int) $row['userid']
            . "' target='_parent'><b>"
            . class_user($row['user'])
            . "</b></a>";
    }

    /* ---------------------------------------------------------
       EDIT / DELETE / REPLY
       --------------------------------------------------------- */
    $replyUser = addslashes($row['user']);

    $reply = "<a href=\"javascript:Reply_code('&bull;&nbsp;"
        . $replyUser
        . ",','shoutboxform','message')\">"
        . "<img src='" . $site_config['SITEURL']
        . "/images/blue reply.png' height='12' border='0' title='"
        . T_("REPLY")
        . "'></a>";

    $edit = $canEdit
        ? "<a href='"
        . $site_config['SITEURL']
        . "/shoutedit.php?action=edit&amp;msgid="
        . (int) $row['msgid']
        . "'>"
        . "<img src='" . $site_config['SITEURL']
        . "/images/edit.png' height='12' border='0' title='"
        . T_("EDIT")
        . "'></a>"
        : "";

    $delete = $canEdit
        ? "<a href='"
        . $site_config['SITEURL']
        . "/shoutbox.php?del="
        . (int) $row['msgid']
        . "'>"
        . "<img src='" . $site_config['SITEURL']
        . "/images/delete.png' height='12' border='0' title='"
        . T_("DELETE")
        . "'></a>"
        : "";

    $actions = "<span class='shoutbox_actions'>"
        . $edit
        . $delete
        . $reply
        . "</span>";

    /* ---------------------------------------------------------
       MESSAGE
       --------------------------------------------------------- */
    $message = "<span class='shoutbox_message'>"
        . nl2br(format_comment($row['message']))
        . "</span>";

    /* ---------------------------------------------------------
       OUTPUT
       --------------------------------------------------------- */
    echo '<td style="font-size:12px">'
        . $date
        . ' '
        . $name
        . ' '
        . $actions
        . ' '
        . $message
        . '</td></tr>';
}
    ?>

    </table>
    </div>

    <?php
    // if the user is logged in, show the shoutbox form, otherwise don't.
    if (!isset($_GET['history'])) {
        if (isset($_COOKIE["pass"])) {
            if ($shoutbox_error) {
                echo "<div class='shoutbox_error'>" . htmlspecialchars($shoutbox_error) . "</div>";
            }
            echo "<form name='shoutboxform' action='shoutbox.php' method='post'>";
            echo "<table width='100%' align='center' border='0' cellpadding='1' cellspacing='0'><tr class='shoutbox_messageboxback'><td align='center'>";
            echo "<input type='text' name='message' class='btnChat' />&nbsp;";
            echo "<input type='submit' name='submit' value='&nbsp;" . T_("SHOUT") . "&nbsp;'>&nbsp; &nbsp;";
            echo '<a href="javascript:PopSmiles(\'shoutboxform\', \'message\');"><img src="images/smilies/grin.gif" border="0" title="' . T_("MORE_SMILIES") . '"></a>&nbsp;';
            echo "<a href='shoutbox.php?t=" . time() . "'><img src='images/refresh.gif' alt='' title='" . T_("REFRESH") . "' border='0'></a>&nbsp;";
            echo "<a href='" . $site_config['SITEURL'] . "/shoutbox.php?history=1' target='_blank'><img src='images/history.gif' alt='' title='" . T_("HISTORY") . "' border='0'></a>";
            echo "</td></tr></table>";
            echo quickbbshout();
            echo "</form>";
        } else {
            echo "<br /><div class='shoutbox_error'>" . T_("SHOUTBOX_MUST_LOGIN") . "</div>";
        }
    }

    if (!isset($_GET['history'])) {
        echo "</body></html>";
    } else {
        end_frame();
        stdfoot();
    }
} else { // SHOUTBOX disabled
    echo T_("SHOUTBOX_DISABLED");
}
?>