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
$action = $_GET["action"]; 
dbconn();
$referer = "shoutbox.php";

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
?>
<title><?php echo $site_config['SITENAME'] . T_("SHOUTBOX"); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $site_config['SITEURL']?>/themes/<?php echo $THEME; ?>/theme.css" />
<script type="text/javascript" src="<?= $site_config["SITEURL"]; ?>/js/glossy.js"></script>
<script type="text/javascript">
<!--
function bbshout(repdeb, repfin) {
  var input = document.forms['Form'].elements['message'];
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
} else 

if (typeof input.selectionStart != 'undefined') {
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
} else {
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    
while(!re.test(pos)) {
    pos = prompt("Insertion à la position (0.." + input.value.length + "):", "0");
}
    
if (pos > input.value.length) {
    pos = input.value.length;
}
    var insText = prompt("Veuillez entrer le texte à formater:");
    input.value = input.value.substr(0, pos) + repdeb + insText + repfin + input.value.substr(pos);
}
}

function bbcolor() {
	var colorvalue = document.forms['Form'].elements['color'].value;
	bbshout("[color="+colorvalue+"]", "[/color]");
}

function bbfont() {
	var fontvalue = document.forms['Form'].elements['font'].value;
	bbshout("[font="+fontvalue+"]", "[/font]");
}

function bbsize() {
    var sizevalue = document.forms['Form'].elements['size'].value;
    bbshout("[size="+sizevalue+"]", "[/size]");
}

function bbimg() {
    var imgvalue = document.forms['Form'].elements['img'].value;
    bbshout("[img="+sizevalue+"]", "[/img]");
}

function bbblink() {
    var blink = document.forms['Form'].elements['blink'].value;
    bbshout("[blink="+blinkvalue+"]", "[/blink"); 
}

function bbdf() {
    var df = document.forms['Form'].elements['df'].value;
    bbshout("[df="+dfvalue+"]", "[/df"); 	
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

<script language='javascript'>
function SmileIT(smile,form,text){
   document.forms[form].elements[text].value = document.forms[form].elements[text].value+" "+smile+" ";
   document.forms[form].elements[text].focus();
}
</script>

<script language='javascript'><!--

function Smilies(Smilie) {
document.Form.message.value+=Smilie+" ";
document.Form.message.focus();
}
//--></script>
<?php

function quickbbshout(){
	echo "<table align='center' width='75%' border=0 cellpadding=2 cellspacing=2><tr>";
	echo "<td align='center'>";
	echo "<a href=\"javascript:bbshout('[b]', '[/b]')\"><img src=images/bbcode/bold.png alt='Bold' title='Gras' style='vertical-align: -30%' /></a>";
	echo "<a href=\"javascript:bbshout('[i]', '[/i]')\"><img src=images/bbcode/italic.png border=0 alt='Italic' title='Italique' style='vertical-align: -30%' /></a>";
	echo "<a href=\"javascript:bbshout('[u]', '[/u]')\"><img src=images/bbcode/underline.png border=0 alt='Underline' title='Souligné' style='vertical-align: -30%' /></a>";
	echo "<a href=\"javascript:bbshout('[blink]', '[/blink]')\"><img src=images/bbcode/blink.png border=0 alt='blink' title='Clignotant' style='vertical-align: -30%' /></a>";
	echo "<a href=\"javascript:bbshout('[df]', '[/df]')\"><img src=images/bbcode/df.png border=0 alt='df' title='Défilant' style='vertical-align: -30%' /></a>";
	echo "<a href=\"javascript:bbshout('[url]', '[/url]')\"><img src=images/bbcode/link.png border=0 alt='Url' title='Lien' style='vertical-align: -30%' /></a>";
	echo "<a href=\"javascript:bbshout('[img]', '[/img]')\"><img src=images/bbcode/picture.png border=0 alt='Img' title='Image' style='vertical-align: -30%' /></a>";
	echo "<select name='color' class='bb_icon' onChange=\"javascript:bbcolor()\">
		<option selected='selected'>Couleur</option>
		<option value='#FF0000' style='color:#FF0000'>Rouge</option>
		<option value='#00FFFF' style='color:#00FFFF'>Turquoise</option>
		<option value='#0000FF' style='color:#0000FF'>Bleu clair</option>
		<option value='#0000A0' style='color:#0000A0'>Bleu foncé</option>
		<option value='#FF0080' style='color:#FF0080'>Pourpre clair</option>
		<option value='#800080' style='color:#800080'>Pourpre foncé</option>
		<option value='#FFFF00' style='color:#FFFF00'>Jaune</option>
		<option value='#FF8040' style='color:#FF8040'>Orange</option>
		<option value='#00FF00' style='color:#00FF00'>Vert pastel</option>
		<option value='#C0C0C0' style='color:#C0C0C0'>Vert clair</option>
		<option value='#808000' style='color:#808000'>Vert foncé</option>
	</select>";
	echo "<select name='font' class='bb_icon' onChange=\"javascript:bbfont()\">
		<option selected='selected'>Police</option>
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
	echo "<select name='size' class='bb_icon' onChange=\"javascript:bbsize()\">
		<option selected='selected'>Taille</option>
		<option value=1>1</option>
		<option value=2>2</option>
		<option value=3>3</option>
		<option value=4>4</option>
		<option value=5>5</option>
		<option value=6>6</option>
		<option value=7>7</option>
	</select>";
	echo "</td></tr><tr><td align='center'>";
	echo "<a href=\"javascript: SmileIT(':)','Form','message')\"><img border=0 src=images/smilies/smile.png></a>
		<a href=\"javascript: SmileIT(':(','Form','message')\"><img border=0 src=images/smilies/sad.png></a>
		<a href=\"javascript: SmileIT(';)','Form','message')\"><img border=0 src=images/smilies/wink.png></a>
		<a href=\"javascript: SmileIT(':P','Form','message')\"><img border=0 src=images/smilies/razz.png></a>
		<a href=\"javascript: SmileIT(':D','Form','message')\"><img border=0 src=images/smilies/grin.png></a>
		<a href=\"javascript: SmileIT('-|)','Form','message')\"><img border=0 src=images/smilies/plain.png></a>
		<a href=\"javascript: SmileIT(':O','Form','message')\"><img border=0 src=images/smilies/suprise.png></a>
		<a href=\"javascript: SmileIT(':?','Form','message')\"><img border=0 src=images/smilies/confused.png></a>
		<a href=\"javascript: SmileIT('8)','Form','message')\"><img border=0 src=images/smilies/glasses.png></a>
		<a href=\"javascript: SmileIT('8o','Form','message')\"><img border=0 src=images/smilies/eek.png></a>
		<a href=\"javascript: SmileIT('B)','Form','message')\"><img border=0 src=images/smilies/cool.png></a>
		<a href=\"javascript: SmileIT(':-)','Form','message')\"><img border=0 src=images/smilies/smile-big.png></a>
		<a href=\"javascript: SmileIT(':-(','Form','message')\"><img border=0 src=images/smilies/crying.png></a>
		<a href=\"javascript: SmileIT(':-*','Form','message')\"><img border=0 src=images/smilies/kiss.png></a>
		<a href=\"javascript: SmileIT('O:-D','Form','message')\"><img border=0 src=images/smilies/angel.png></a>
		<a href=\"javascript: SmileIT(':-@','Form','message')\"><img border=0 src=images/smilies/devilish.png></a>
		<a href=\"javascript: SmileIT(':love','Form','message')\"><img border=0 src=images/smilies/love.png></a>";
	echo "</td></tr></table>";
}

// if ($action == "edit") {
// 	$msgid = $_GET["msgid"];
// 	if (!is_valid_id($msgid))
// 		die;
//     $res = SQL_Query_exec("SELECT * FROM shoutbox WHERE msgid=".$msgid);
// 	if (mysqli_num_rows($res) != 1)
// 		show_error_msg("Error", "No message with ID $msgid.");
// 	$arr = mysqli_fetch_assoc($res);

//     if ($CURUSER["id"] != $arr["userid"] && get_user_class() < 6)
// 		show_error_msg("Error", "Denied!", 1);
//     $save = (int)$_GET["save"];
//     if ($save) {
// 		$message = $_POST['message'];
// 			if ($message == "")
			
// 				show_error_msg("Error", "Message cannot be empty!",1); 
// 		$message = sqlesc($message);
// 		SQL_Query_exec("UPDATE shoutbox SET message=$message WHERE msgid=$msgid");
// 		header("Refresh: 0; url=shoutbox.php");

//                // autolink("shoutbox.php", "Edit complete....");
// 	}
//     print("<center><font size=3 color=red><b>Edit Message</b></font></center>\n");
//     print("<form name=Form method=post action=shoutedit.php?action=edit&save=1&msgid=$msgid>\n");
//     print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
//     print("<tr><td>\n");
//     print("</td><td style='padding: 0px'><textarea name=message cols=50 rows=4 >" . stripslashes(htmlspecialchars($arr["message"])) . "</textarea></td></tr>\n");
//     print("<tr><td align=center colspan=2><input type=submit value='Submit Changes' class=btn></td></tr>\n");
//     print("</table></center>\n");
//     print("</form>\n");
// 	print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
// 	print("<form name=Form method=post action=shoutbox.php\n");
//     print("<tr><td align=center colspan=2><input type=submit value='Cancel' class=btn></td></tr>\n");
//     print("</table></center>\n");
//     print("</form>\n");
// }
if ($action == "edit") {
    $msgid = $_GET["msgid"];
    if (!is_valid_id($msgid))
        die;
        
    $res = SQL_Query_exec("SELECT * FROM shoutbox WHERE msgid=" . sqlesc($msgid));
    if (mysqli_num_rows($res) != 1)
        show_error_msg("Error", "No message with ID $msgid.");
        
    $arr = mysqli_fetch_assoc($res);
    
    // Check if the user is the owner of the message or an admin
    if ($CURUSER["id"] != $arr["userid"] && get_user_class() < 6) {
        show_error_msg("Error", "Denied!", 1);
    }
    
    $save = isset($_GET["save"]) ? (int)$_GET["save"] : 0; // Default to 0 if not set

    if ($save) {
        $message = $_POST['message'] ?? ''; // Default to empty string if not set
        if ($message == "") {
            show_error_msg("Error", "Message cannot be empty!", 1); 
        }
    
        
        $message = sqlesc($message);
        SQL_Query_exec("UPDATE shoutbox SET message=$message WHERE msgid=$msgid");
        header("Refresh: 0; url=shoutbox.php");
        exit;
    }

    print("<center><font size=3 color=red><b>Edit Message</b></font></center>\n");
    print("<form name=Form method=post action=shoutedit.php?action=edit&save=1&msgid=$msgid>\n");
    print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
    print("<tr><td></td><td style='padding: 0px'><textarea name=message cols=50 rows=4>" . stripslashes(htmlspecialchars($arr["message"])) . "</textarea></td></tr>\n");
    print("<tr><td align=center colspan=2><input type=submit value='Submit Changes' class=btn></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
    
    print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
    print("<form name=Form method=post action=shoutbox.php>\n");
    print("<tr><td align=center colspan=2><input type=submit value='Cancel' class=btn></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
}


// if ($action == "delete") {
// 	$msgid = $_GET["msgid"];
// 	if (!is_valid_id($msgid))
// 		die;
//     $res = SQL_Query_exec("SELECT * FROM shoutbox WHERE msgid=".$msgid);
// 	if (mysqli_num_rows($res) != 1)
// 		show_error_msg("Error", "No message with ID $msgid.");
// 	$arr = mysqli_fetch_assoc($res);
//     if ($CURUSER["id"] != $arr["userid"] && get_user_class() < 6)
// 		show_error_msg("Error", "Denied!", 1);
//     $save = (int)$_GET["save"];
//     if ($save) {

// 		SQL_Query_exec("DELETE FROM shoutbox WHERE msgid=$msgid");
// 		write_log("<b>Shout<font color='orange'> ".$arr['message']."</font> Deleted by: ".$CURUSER['username']."</b>");
// 		header("Refresh: 0; url=shoutbox.php");

//                // autolink("shoutbox.php", "Edit complete....");
// 	}
//     print("<center><font size=3 color=red><b>Are you sure you want to delete this shoutbox message?</b></font></center>\n");
//     print("<form name=Form method=post action=shoutedit.php?action=delete&save=1&msgid=$msgid>\n");
//     print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
//     print("<tr><td>\n");
// 	print("<tr><td>\n");
//     print("</td><td style='padding: 0px'><textarea name=message cols=50 rows=4 >" . stripslashes(htmlspecialchars($arr["message"])) . "</textarea></td></tr>\n");
//     print("<tr><td align=center colspan=2><input type=submit value='Delete Message' class=btn></td></tr>\n");
//     print("</table></center>\n");
//     print("</form>\n");
//     print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
// 	print("<form name=Form method=post action=shoutbox.php\n");
//     print("<tr><td align=center colspan=2><input type=submit value='Cancel' class=btn></td></tr>\n");
//     print("</table></center>\n");
//     print("</form>\n");
// }
if ($action == "delete") {
    $msgid = $_GET["msgid"];
    if (!is_valid_id($msgid)) {
        die;
    }

    $res = SQL_Query_exec("SELECT * FROM shoutbox WHERE msgid=" . sqlesc($msgid));
    if (mysqli_num_rows($res) != 1) {
        show_error_msg("Error", "No message with ID $msgid.");
    }

    $arr = mysqli_fetch_assoc($res);

    // Check if the user is the owner of the message or an admin
    if ($CURUSER["id"] != $arr["userid"] && get_user_class() < 6) {
        show_error_msg("Error", "Denied!", 1);
    }

    $save = (int)$_GET["save"];
    if ($save) {
        SQL_Query_exec("DELETE FROM shoutbox WHERE msgid=$msgid");
        write_log("<b>Shout<font color='orange'> ".$arr['message']."</font> Deleted by: ".$CURUSER['username']."</b>");
        header("Refresh: 0; url=shoutbox.php");
        exit;
    }

    // JavaScript for confirmation dialog
    echo '
    <script>
        function confirmDelete() {
            var message = "' . addslashes($arr["message"]) . '";
            var result = confirm("Are you sure you want to delete this shoutbox message?\\n\\n" + message);
            if (result) {
                document.getElementById("deleteForm").submit();
            } else {
                window.location.href = "shoutbox.php";
            }
        }
        // Call the confirmation function on page load
        window.onload = confirmDelete;
    </script>

    <form id="deleteForm" name="Form" method="post" action="shoutedit.php?action=delete&save=1&msgid=' . $msgid . '">
        <textarea name="message" cols="50" rows="4" readonly>' . stripslashes(htmlspecialchars($arr["message"])) . '</textarea>
    </form>';
}
?>