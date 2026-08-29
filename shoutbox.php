<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

require_once "backend/functions.php";

dbconn();
loggedinonly();

// Set the last visit cookie for the current room only
if (!isset($_GET['check_activity'])) {
    $room = isset($_COOKIE['room']) ? (int)$_COOKIE['room'] : (isset($_REQUEST['room']) ? (int)$_REQUEST['room'] : 0);
    if (get_user_class() < 3 && $room > 0) $room = 0;
    if (get_user_class() < 5 && $room > 1) $room = 0;
    setcookie("last_visit_room_$room", get_date_time(), time() + 3600, '/'); // 1 hour expiry
}

// AJAX endpoint for room activity check
if (isset($_GET['check_activity']) && $_GET['check_activity'] == 1) {
    $current_room = isset($_GET['room']) ? (int)$_GET['room'] : 0;
    $activity = get_room_activity($current_room, $CURUSER['id']);
    header('Content-Type: application/json');
    echo json_encode($activity);
    exit;
}

// Function to get room activity (new shouts since last visit to each room)
function get_room_activity($current_room, $user_id) {
    $activity = [];
    $rooms = [0 => 'Public', 1 => 'VIP', 2 => 'Staff'];
    
    foreach ($rooms as $room_id => $room_name) {
        if ($room_id > 0 && get_user_class() < 3) continue; // Restrict VIP
        if ($room_id > 1 && get_user_class() < 5) continue; // Restrict Staff
        
        $last_visit = isset($_COOKIE["last_visit_room_$room_id"]) ? $_COOKIE["last_visit_room_$room_id"] : get_date_time(time() - 3600); // Default to 1 hour ago
        
        $query = "SELECT COUNT(*) as new_count 
                  FROM shoutbox 
                  WHERE room = $room_id 
                  AND date > '$last_visit'";
        $result = SQL_Query_exec($query);
        $row = mysqli_fetch_assoc($result);
        $activity[$room_id] = $row['new_count'];
    }
    
    return $activity;
}
?>

<script type="text/javascript">
function bbshout(repdeb, repfin) {
  var input = document.forms['shoutboxform'].elements['message'];
  input.focus();
  if(typeof document.selection != 'undefined') {
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
  else if(typeof input.selectionStart != 'undefined') {
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
    while(!re.test(pos)) {
      pos = prompt("Insertion à la position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    var insText = prompt("Veuillez entrer le texte à formater:");
    input.value = input.value.substr(0, pos) + repdeb + insText + repfin + input.value.substr(pos);
  }
}

function bbcolor() {
    var colorvalue = document.forms['shoutboxform'].elements['color'].value;
    bbshout("[color="+colorvalue+"]", "[/color]");
}

function bbfont() {
    var fontvalue = document.forms['shoutboxform'].elements['font'].value;
    bbshout("[font="+fontvalue+"]", "[/font]");
}

function bbsize() {
    var sizevalue = document.forms['shoutboxform'].elements['size'].value;
    bbshout("[size="+sizevalue+"]", "[/size]");
}

function bbimg() {
    var imgvalue = document.forms['shoutboxform'].elements['img'].value;
    bbshout("[img="+imgvalue+"]", "[/img]");
}
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

<script language='javascript'>
function SmileIT(smile,form,text){
   document.forms[form].elements[text].value = document.forms[form].elements[text].value+" "+smile+" ";
   document.forms[form].elements[text].focus();
}
function mySubmit() {
   setTimeout('document.shbox.reset()',150);
}
function Smilies(Smilie) {
document.shoutboxform.message.value+=Smilie+" ";
document.shoutboxform.message.focus();
}
</script>
<script>
function clearShoutboxCookies() {
    document.cookie = "consent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "room=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "last_visit_room_0=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "last_visit_room_1=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "last_visit_room_2=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    alert('Shoutbox cookies cleared!');
}
</script>
<?php

function quickbbshout() {
    echo "<table align='center' width='50%' border='0' cellpadding='5' cellspacing='5'><tr>";
    echo "<td width='5%' align='center'>";
    echo "<a href=\"javascript:bbshout('[b]', '[/b]')\"><img src='images/shoutbox/bbcode_bold.gif' alt='Bold' title='Bold' height='28px' style='margin-bottom: 0px;' /></a>";
    echo "<a href=\"javascript:bbshout('[i]', '[/i]')\"><img src='images/shoutbox/bbcode_italic.gif' border='0' alt='Italic' title='Italic' height='28px' style='margin-bottom: 0px;' /></a>";
    echo "<a href=\"javascript:bbshout('[u]', '[/u]')\"><img src='images/shoutbox/bbcode_underline.gif' border='0' alt='Underline' title='Underline' height='28px' style='margin-bottom: 0px;' /></a>";
    echo "<a href=\"javascript:bbshout('[img]', '[/img]')\"><img src='images/shoutbox/bbcode_image.gif' border='0' alt='Image' title='Image' height='28px' style='margin-bottom: 0px;' /></a>";
    echo "</td><td width='25%' align='center'>";
    echo "<select name='color' class='bb_icon' onChange='javascript:bbcolor()' style='font-size: 16px; padding: 5px;'>
    <option selected='selected'>Color</option>
    <option value='#FF0000' style='color:#FF0000'>Red</option>
    <option value='#00FFFF' style='color:#00FFFF'>Turquoise</option>
    <option value='#0000FF' style='color:#0000FF'>Light Blue</option>
    <option value='#0000A0' style='color:#0000A0'>Dark Blue</option>
    <option value='#FF0080' style='color:#FF0080'>Light Purple</option>
    <option value='#800080' style='color:#800080'>Dark Purple</option>
    <option value='#FFFF00' style='color:#FFFF00'>Yellow</option>
    <option value='#FF8040' style='color:#FF8040'>Orange</option>
    <option value='#00FF00' style='color:#00FF00'>Pastel Green</option>
    <option value='#C0C0C0' style='color:#C0C0C0'>Light Gray</option>
    <option value='#808000' style='color:#808000'>Dark Green</option>
  </select>";
    echo "</td><td width='10%' align='center'>";
    echo "<select name='font' class='bb_icon' onChange=\"javascript:bbfont()\" style='font-size: 16px; padding: 5px;'>
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
    <option value='Tahoma'>Tahoma</option>
    <option value='Times New Roman'>Times New Roman</option>
    <option value='Trebuchet MS'>Trebuchet MS</option>
    <option value='Verdana'>Verdana</option>
    <option value='Symbol'>Symbol</option>
  </select>";
    echo "</td><td width='10%' align='center'>";
echo "<select name='size' class='bb_icon' onChange=\"javascript:bbsize()\" style='font-size: 16px; padding: 5px;'>
    <option selected='selected'>Size</option>
    <option value=1>1</option>
    <option value=2>2</option>
    <option value=3>3</option>
    <option value=4>4</option>
    <option value=5>5</option>
    <option value=6>6</option>
    <option value=7>7</option>
  </select>";
    echo "</td></tr><tr><td width='75%' align='center'>";
    echo "<a href=\"javascript: SmileIT(':)','shoutboxform','message')\"><img border=0 src=images/smilies/smile.gif></a>
          <a href=\"javascript: SmileIT(':(','shoutboxform','message')\"><img border=0 src=images/smilies/sad.gif></a>
          <a href=\"javascript: SmileIT(':senile:','shoutboxform','message')\"><img border=0 src=images/smilies/to_become_senile.gif></a>
          <a href=\"javascript: SmileIT(':w00t:','shoutboxform','message')\"><img border=0 src=images/smilies/w00t.gif></a>
          <a href=\"javascript: SmileIT(':D','shoutboxform','message')\"><img border=0 src=images/smilies/grin.gif></a>
          <a href=\"javascript: SmileIT(':ok:','shoutboxform','message')\"><img border=0 src=images/smilies/ok.gif></a>
          <a href=\"javascript: SmileIT(':wave:','shoutboxform','message')\"><img border=0 src=images/smilies/wave.gif></a>
          <a href=\"javascript: SmileIT(':-/','shoutboxform','message')\"><img border=0 src=images/smilies/confused.gif></a>
          <a href=\"javascript: SmileIT(':drinks:','shoutboxform','message')\"><img border=0 src=images/smilies/drinks.gif></a>
          <a href=\"javascript: SmileIT(':wacko:','shoutboxform','message')\"><img border=0 src=images/smilies/wacko.gif></a>
          <a href=\"javascript: SmileIT(':t-up:','shoutboxform','message')\"><img border=0 src=images/smilies/thumbsup.gif></a>
          <a href=\"javascript: SmileIT(':t-down:','shoutboxform','message')\"><img border=0 src=images/smilies/thumbsdown.gif></a>
          <a href=\"javascript: SmileIT(':read:','shoutboxform','message')\"><img border=0 src=images/smilies/read.gif></a>";
    echo "</td></tr></table>";
}

// Read room from cookie or URL parameter, default to Public (0) if not set
$room = isset($_COOKIE['room']) ? (int)$_COOKIE['room'] : (isset($_REQUEST['room']) ? (int)$_REQUEST['room'] : 0);

// Validate room based on user class
if (get_user_class() < 3 && $room > 0) {
    $room = 0;
}
if (get_user_class() < 5 && $room > 1) {
    $room = 0;
}

$GLOBALS['room'] = $room;

// Updated room selection with buttons and "NEW" indicators
function therooms() {
    global $room;

    // Ensure $CURUSER is set and valid
    $CURUSER = $_SESSION['CURUSER'] ?? null;
    
    $activity = [0 => 0, 1 => 0, 2 => 0]; // Default activity counts
    if (is_array($CURUSER) && isset($CURUSER['id'])) {
        $result = get_room_activity($room, $CURUSER['id']);
        if (is_array($result)) {
            $activity = array_replace($activity, $result); // Merge with defaults
        } else {
            error_log("get_room_activity returned invalid data: " . json_encode($result));
        }
    } else {
        error_log("CURUSER is missing or invalid in shoutbox code");
    }
    
    $data = '<div class="room-buttons" style="text-align: center; margin-bottom: 10px;">';
    $data .= '<button class="room-btn ' . ($room == 0 ? 'active' : '') . '" data-room="0" onclick="setRoom(0)">Public' . ($activity[0] > 0 ? " - $activity[0] new " . ($activity[0] == 1 ? "message" : "messages") : '') . '</button>';
    $data .= '<button class="room-btn ' . ($room == 1 ? 'active' : '') . '" data-room="1" onclick="setRoom(1)">VIP' . ($activity[1] > 0 ? " - $activity[1] new " . ($activity[1] == 1 ? "message" : "messages") : '') . '</button>';
    
    if (is_callable('get_user_class') && get_user_class() >= 5) {
        $data .= '<button class="room-btn ' . ($room == 2 ? 'active' : '') . '" data-room="2" onclick="setRoom(2)">Staff' . ($activity[2] > 0 ? " - $activity[2] new " . ($activity[2] == 1 ? "message" : "messages") : '') . '</button>';
    }
    
    $data .= '</div>';
    
    $data .= '<script>
        function setRoom(room) {
            if (!document.cookie.includes("consent=true")) {
                if (confirm("We use cookies to remember your shoutbox room selection. Do you want to allow cookies? You can cancel, and the shoutbox will work fine. You will just always default to the public shoutbox when you visit")) {
                    document.cookie = "consent=true; path=/; max-age=31536000";
                    document.cookie = "room=" + room + "; path=/; max-age=31536000";
                } else {
                    sessionStorage.setItem("room", room);
                }
            } else {
                document.cookie = "room=" + room + "; path=/; max-age=31536000";
            }
            window.location = "shoutbox.php?room=" + room;
        }
    </script>';
    
    return $data;
}

function theroomsh() {
    $data = '<div class="room-buttons" style="text-align: center; margin-bottom: 10px;">';
    $data .= '<button class="room-btn ' . ($GLOBALS['room'] == 0 ? 'active' : '') . '" onclick="window.location=\'shoutbox.php?history=0&room=0\'">Public</button>';
    $data .= '<button class="room-btn ' . ($GLOBALS['room'] == 1 ? 'active' : '') . '" onclick="window.location=\'shoutbox.php?history=0&room=1\'">VIP</button>';
    
    if (get_user_class() >= 5) {
        $data .= '<button class="room-btn ' . ($GLOBALS['room'] == 2 ? 'active' : '') . '" onclick="window.location=\'shoutbox.php?history=1&room=2\'">Staff</button>';
    }
    
    $data .= '</div>';
    return $data;
}

if ($site_config['SHOUTBOX'] && $CURUSER) {
    function linkit($al_url, $al_msg) {
        echo "\n<meta http-equiv=\"refresh\" content=\"3; url=$al_url\">\n";
        echo "<center>\n<b>$al_msg</b>\n<b>Redirection ...</b>\n[ <a href='$al_url'>lien</a> ]\n</center>";
        exit;
    }
    
    // DELETE MESSAGES
    if (isset($_GET['del'])) {
        if (is_numeric($_GET['del'])) {
            $query = "SELECT * FROM shoutbox WHERE msgid=" . $_GET['del'];
            $result = SQL_Query_exec($query);
        } else {
            echo "invalid msg id STOP TRYING TO INJECT SQL";
            exit;
        }
        $row = mysqli_fetch_row($result);
        if ($row && ($CURUSER["edit_users"] == "yes" || $CURUSER['username'] == $row[1])) {
            $query = "DELETE FROM shoutbox WHERE msgid=" . $_GET['del'];
            write_log("<b><font color='orange'>Shout Deleted: </font> Deleted by " . $CURUSER['username'] . "</b>");
            SQL_Query_exec($query);
        }
    }
    
    // INSERT MESSAGE
    if (!empty($_POST['message']) && $CURUSER) {
        $_POST['message'] = sqlesc($_POST['message']);
        $query = "SELECT COUNT(*) FROM shoutbox WHERE message=" . $_POST['message'] . " AND user='" . $CURUSER['username'] . "' AND UNIX_TIMESTAMP('" . get_date_time() . "')-UNIX_TIMESTAMP(date) < 30";
        $result = SQL_Query_exec($query);
        $row = mysqli_fetch_row($result);
        
        if ($row[0] == '0') {
            $query = "INSERT INTO shoutbox (msgid, user, message, date, userid, room) VALUES (NULL, '" . $CURUSER['username'] . "', " . $_POST['message'] . ", '" . get_date_time() . "', '" . $CURUSER['id'] . "', '" . (int)$_POST['room'] . "')";
            SQL_Query_exec($query);
        }
    }
    
    // GET CURRENT USERS THEME AND LANGUAGE
    if ($CURUSER) {
        $ss_a = @mysqli_fetch_assoc(@SQL_Query_exec("select uri from stylesheets where id=" . $CURUSER["stylesheet"]));
        if ($ss_a) $THEME = $ss_a["uri"];
    } else {
        $ss_a = mysqli_fetch_assoc(SQL_Query_exec("select uri from stylesheets where id='" . $site_config['default_theme'] . "'"));
        if ($ss_a) $THEME = $ss_a["uri"];
    }
    
    if (!isset($_GET['history'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $site_config['SITENAME'] . T_("SHOUTBOX"); ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <meta http-equiv="refresh" content="120" />
    <link rel="stylesheet" type="text/css" href="<?php echo $site_config['SITEURL']; ?>/themes/<?php echo $THEME; ?>/theme.css" />
    <script type="text/javascript" src="<?php echo $site_config['SITEURL']; ?>/backend/java_klappe.js"></script>
    <style>
    .room-buttons { 
        margin: 10px 0; 
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .room-btn {
        padding: 8px 16px;
        border: 2px solid #333;
        background: #ffffff;
        border-radius: 6px; /* Rounds the edges - adjust this value as needed */
        cursor: pointer;
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #333;
        transition: all 0.2s ease;
        text-align: center;
    }
    .room-btn:hover {
        background: #f0f0f0;
        border-color: #666;
    }
    .room-btn.active {
        background: #4CAF50;
        color: white;
        border-color: #45a049;
        font-weight: bold;
    }
    .room-btn.unread {
        color: #ff4500;
        font-weight: bold;
        background: #fff0f0;
        border-color: #ff4500;
    }
    .room-btn.unread:hover {
        background: #ffe6e6;
    }
</style>
</head>
<body class="shoutbox_body">
<?php
        echo '<div class="shoutbox_contain"><table border="0" style="width: 99%; table-layout:fixed">';
        if (get_user_class() >= 3) {
            echo '<div style="position: relative;">';
            echo therooms();
            echo '<button onclick="clearShoutboxCookies()" class="bb_icon" style="position: absolute; right: 60; top: 10px; cursor: pointer;">Clear room cookies</button>';
            echo '</div>';
        }
    } else {
        if ($site_config["MEMBERSONLY"]) {
            loggedinonly();
        }
        stdhead();
        begin_frame(T_("SHOUTBOX_HISTORY"));
        echo '<div class="shoutbox_history">';
        $query = 'SELECT COUNT(*) FROM shoutbox WHERE room = ' . $room;
        $result = SQL_Query_exec($query);
        $row = mysqli_fetch_row($result);
        $pages = round($row[0] / 100) + 1;
        $i = 1;
        echo '<div align="center">';
        while ($pages > 0) {
            echo "<a href='" . $site_config['SITEURL'] . "/shoutbox.php?history=1&room=" . $room . "&page=" . $i . "'>[" . $i . "]</a> ";
            $i++;
            $pages--;
        }
        echo '</div><br /><table border="0" style="width: 99%; table-layout:fixed">';
    }
    
    if (isset($_GET['history'])) {
        if (isset($_GET['page']) && $_GET['page'] > 1) {
            $lowerlimit = $_GET['page'] * 100 - 100;
            $upperlimit = $_GET['page'] * 100;
        } else {
            $lowerlimit = 0;
            $upperlimit = 100;
        }
        $query = 'SELECT s.* FROM shoutbox s INNER JOIN users u ON u.id = s.userid AND s.room = ' . $room . ' ORDER BY s.msgid DESC LIMIT ' . $lowerlimit . ',' . $upperlimit;
    } else {
        $query = 'SELECT s.* FROM shoutbox s INNER JOIN users u ON u.id = s.userid AND s.room = ' . $room . ' ORDER BY s.msgid DESC LIMIT 20';
    }
    
    $result = SQL_Query_exec($query);
    $alt = false;
    
    while ($row = mysqli_fetch_assoc($result)) {
        if ($alt) {
            echo '<tr class="shoutbox_noalt">';
            $alt = false;
        } else {
            echo '<tr class="shoutbox_alt">';
            $alt = true;
        }
        echo '<td style="font-size: 9px; width: 95px;">';
        echo "<div align='left' style='float: left'>" . date('M j, g:ia', utc_to_tz_time($row['date'])) . "</div>";
        $ol3 = mysqli_fetch_array(SQL_Query_exec("SELECT avatar FROM users WHERE id=" . $row["userid"]));
        $av = !empty($ol3['avatar']) ? "<img src='" . $ol3['avatar'] . "' alt='my_avatar' width='27' height='27'>" : "<img src='images/default_avatar.png' alt='my_avatar' width='27' height='27'>";
        if ($row['userid'] == 0) $av = "<img src='images/0_avatar.png' alt='0_avatar' width='27' height='27'>";
        
        if ($CURUSER["edit_users"] == "yes" || $CURUSER['username'] == $row['user']) {
            echo "<div align='right' style='float: right'><a href='" . $site_config['SITEURL'] . "/shoutedit.php?action=edit&msgid=" . $row['msgid'] . "' style='font-size: 8px'><img src='images/shoutbox/shout_edit.png'></a>  <a href='" . $site_config['SITEURL'] . "/shoutedit.php?action=delete&msgid=" . $row['msgid'] . "' style='font-size: 8px'><img src='images/shoutbox/shout_delete.png'></a></div>";
        }
        echo '</td><td style="font-size: 12px; padding-left: 5px">' . $av . ' <a href="' . $site_config['SITEURL'] . '/account-details.php?id=' . $row['userid'] . '" target="_parent"><b>' . (class_user($row['user'])) . ':</b></a> ' . nl2br(format_comment($row['message'])) . '</td></tr>';
    }
?>

</table>
</div>
<br />
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shoutbox</title>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const messageInput = document.querySelector("input[name='message']");
            const savedMessage = localStorage.getItem("shoutboxMessage");
            if (savedMessage) messageInput.value = savedMessage;
            const wasFocused = localStorage.getItem("shoutboxInputFocused") === "true";
            if (wasFocused) messageInput.focus();
            messageInput.addEventListener("input", function() { localStorage.setItem("shoutboxMessage", messageInput.value); });
            messageInput.addEventListener("focus", function() { localStorage.setItem("shoutboxInputFocused", "true"); });
            messageInput.addEventListener("blur", function() { localStorage.setItem("shoutboxInputFocused", "false"); });
            document.querySelector("form[name='shoutboxform']").addEventListener("submit", function() {
                localStorage.removeItem("shoutboxMessage");
                localStorage.removeItem("shoutboxInputFocused");
            });
        });
    </script>
</head>
<body>
    <?php
    if (!isset($_GET['history'])) {
        if (isset($_COOKIE["pass"])) {
            echo "<form name='shoutboxform' action='shoutbox.php' method='post' autocomplete='off'>";
            echo "<center><table width='100%' border='0' cellpadding='1' cellspacing='1'>";
            echo "<tr class='shoutbox_messageboxback'>";
            echo "<td width='75%' align='center'>";
            echo "<input type='hidden' name='room' value=$room />";
            echo "<input type='text' name='message' class='shoutbox_msgbox' />";
            echo "</td>";
            echo "<td> <input type='submit' name='submit' value='" . T_("SHOUT") . "' class='shoutbox_shoutbtn' /></td>";
            echo "<td><a href='shoutbox.php?room=$room'><small>" . T_("REFRESH") . "</small></a> <small>-</small> <a href='" . $site_config['SITEURL'] . "/shoutbox.php?history=1&room=$room' onclick='return confirm(\"Are you sure you want to open this link in a new tab?\");' target='_blank'><small>" . T_("HISTORY") . "</small></a></td>";
            echo "</tr></table>";
            echo quickbbshout();
            echo "</center></form>";
            echo '<script>
                function updateRoomIndicators() {
                    $.get("shoutbox.php?check_activity=1&room=' . $room . '", function(data) {
                        let activity = JSON.parse(data);
                        let buttons = document.querySelectorAll(".room-btn");
                        buttons.forEach(button => {
                            let roomId = parseInt(button.getAttribute("data-room"));
                            let count = activity[roomId] || 0;
                            let baseText = button.textContent.split(" - ")[0];
                            button.textContent = baseText + (count > 0 ? " - " + count + " new " + (count == 1 ? "message" : "messages") : "");
                            if (count > 0) {
                                button.classList.add("unread");
                            } else {
                                button.classList.remove("unread");
                            }
                        });
                    });
                }
                setInterval(updateRoomIndicators, 10000);
                updateRoomIndicators();
            </script>';
        } else {
            echo "<br /><div class='shoutbox_error'>" . T_("SHOUTBOX_MUST_LOGIN") . "</div>";
        }
    }
    ?>
</body>
</html>
<?php
    if (!isset($_GET['history'])) {
        echo "</body></html>";
    } else {
        end_frame();
        stdfoot();
    }
} else {
    echo T_("SHOUTBOX_DISABLED");
}
?>