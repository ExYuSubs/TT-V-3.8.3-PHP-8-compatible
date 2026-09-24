<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


require_once("backend/smilies.php");
require_once("backend/functions.php");
require_once("backend/config.php");
dbconn();

//===| GET CURRENT USERS THEME AND LANGUAGE
if (!empty($CURUSER)) {
    $ss_a = @mysqli_fetch_array(@sql_query_exec("select uri from stylesheets where id=" . (int) $CURUSER["stylesheet"]));
    if ($ss_a) {
        $THEME = $ss_a["uri"];
    }
    $lng_a = @mysqli_fetch_array(@sql_query_exec("select uri from languages where id=" . (int) $CURUSER["language"]));
    if ($lng_a) {
        $LANGUAGE = $lng_a["uri"];
    }
} else { //===| Not logged in so get default theme/language
    $ss_a = mysqli_fetch_array(sql_query_exec("select uri from stylesheets where id='" . sqlesc($site_config['default_theme']) . "'"));
    if ($ss_a) {
        $THEME = $ss_a["uri"];
    }
    $lng_a = mysqli_fetch_array(sql_query_exec("select uri from languages where id='" . sqlesc($site_config['default_language']) . "'"));
    if ($lng_a) {
        $LANGUAGE = $lng_a["uri"];
    }
}

echo '<link rel="stylesheet" type="text/css" href="' . $site_config['SITEURL'] . '/themes/' . $THEME . '/theme.css" />';

if (!isset($_GET["form"]) || !isset($_GET["text"])) {
    err_msg("Error!", "Missing parameter!");
    print("</body></html>");
    die();
}

$parentform = htmlentities(urldecode($_GET["form"]));
$parentarea = htmlentities(urldecode($_GET["text"]));
?>

<script language="javascript">
function SmileIT(smile,textarea)
{
//===| Attempt to create a text range (IE)
if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange)
{
var caretPos = textarea.caretPos;
caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? smile + ' ' : smile
caretPos.select();
}
//===| Mozilla text range replace
else if (typeof(textarea.selectionStart) != "undefined")
{
var begin = textarea.value.substr(0, textarea.selectionStart);
var end = textarea.value.substr(textarea.selectionEnd);
var scrollPos = textarea.scrollTop;

textarea.value = begin + smile + end;

if (textarea.setSelectionRange)
{
textarea.focus();
textarea.setSelectionRange(begin.length + smile.length, begin.length + smile.length);
}
textarea.scrollTop = scrollPos;
}
//===| Just put it on the end
else
{
textarea.value += smile;
textarea.focus(textarea.value.length - 1);
}
}
</script>

<body class="shoutbox_body">
<table class="table_table" width="100%" cellpadding="1" cellspacing="1">
<tr>
<?php
$count = 0;
foreach ($smilies as $code => $url) {
    if ($count % 3 == 0) {
        print("<tr>");
    }
    print("<td class=\"table_table\" align=\"center\"><a href=\"javascript: SmileIT('" . addslashes($code) . "',window.opener.document.forms.$parentform.$parentarea);\"><img border=0 src=images/smilies/" . $url . "></a></td>");
    $count++;

    if ($count % 3 == 0) {
        print("</tr>");
    }
}
if ($count % 3 != 0) {
    print("</tr>");
}
?>
</tr>
</table>

<div align="center">
<a href="javascript: window.close()"><?php echo CLOSE; ?></a>
</div>
<br />
</body>