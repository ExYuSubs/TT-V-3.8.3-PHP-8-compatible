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
require_once("backend/bbcode.php");
dbconn();

if (!$site_config["FORUMS_GUESTREAD"])
    loggedinonly();

function showerror($heading = "Error", $text, $sort = "Error") {
    stdhead("$sort: $heading");
    begin_frame("<font class='error'>$sort: $heading</font>");
    echo $text;
    end_frame();
    stdfoot();
    die;
}

$action = strip_tags($_REQUEST["action"] ?? '');

if (!$CURUSER && ($action == "newtopic" || $action == "post"))
    showerror(T_("FORUM_ERROR"), T_("FORUM_NO_ID"));

if ($CURUSER && ($CURUSER["forumbanned"] == "yes" || $CURUSER["view_forum"] == "no"))
    showerror(T_("FORUM_BANNED"), T_("FORUM_BANNED"));

if ($site_config["FORUMS"]) {

$themedir = "themes/".$THEME."/forums/";

/* Forum UI: Bootstrap buttons with consistent icon sizing, color and spacing. */
print("
<style>
.forum-main-btn,.forum-status-btn,.forum-post-btn{display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;box-sizing:border-box;text-decoration:none!important;white-space:nowrap;line-height:1.2;border-radius:4px;transition:all .15s ease-in-out}
.forum-main-btn{min-height:34px;padding:7px 13px!important;font-size:13px;font-weight:600}
.forum-main-btn i{font-size:14px;margin-right:7px}
.forum-status-btn{min-width:38px;height:32px;padding:6px 9px!important;margin:0 2px;font-size:15px}
.forum-status-btn i{font-size:15px;margin:0!important}
.forum-status-new{color:#fff!important;background:#5cb85c!important;border-color:#4cae4c!important}
.forum-status-old{color:#ddd!important;background:#444!important;border-color:#666!important}
.forum-status-locked{color:#fff!important;background:#d9534f!important;border-color:#d43f3a!important}
.forum-post-actions{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:5px}
.forum-post-left-actions{display:flex;align-items:center;flex-wrap:wrap;gap:5px}
.forum-post-btn{width:34px;height:32px;padding:5px!important;margin:0!important;font-size:15px}
.forum-post-btn i{font-size:15px;margin:0!important}
.forum-post-btn:hover,.forum-main-btn:hover,.forum-status-btn:hover{text-decoration:none!important;opacity:.92}
.forum-action-profile{color:#fff!important;background:#337ab7!important;border-color:#2e6da4!important}
.forum-action-pm{color:#fff!important;background:#5bc0de!important;border-color:#46b8da!important}
.forum-action-report{color:#fff!important;background:#f0ad4e!important;border-color:#eea236!important}
.forum-action-top{color:#fff!important;background:#777!important;border-color:#666!important}
.forum-action-edit{color:#fff!important;background:#5bc0de!important;border-color:#46b8da!important}
.forum-action-delete{color:#fff!important;background:#d9534f!important;border-color:#d43f3a!important}
.forum-action-quote{color:#fff!important;background:#777!important;border-color:#666!important}
.forum-action-reply{color:#fff!important;background:#5cb85c!important;border-color:#4cae4c!important}
.forum-top-actions .forum-main-btn{min-width:92px}
.forum-status-legend{display:flex;align-items:center;flex-wrap:wrap;gap:7px;margin:8px 0}
.forum-status-legend .forum-status-btn{margin:0}
.forum-status-legend-label{margin-right:8px}
@media(max-width:600px){.forum-post-actions{justify-content:flex-start;margin-top:5px}}
</style>
");


function forumheader($location){
echo "<div class='f-header'>
  <div class='f-logo'>
  <table width='100%' cellspacing='6'>
    <tr>
      <td align='left' valign='top'><a href='forums.php'>".T_("FORUM_WELCOME")."</a></td>
      <td align='right' valign='top'><i class='fa fa-question-circle'></i>&nbsp;<a href='faq.php'>".T_("FORUM_FAQ")."</a>&nbsp;&nbsp;&nbsp;<i class='fa fa-search'></i>&nbsp;<a href='forums.php?action=search'>".T_("SEARCH")."</a></td>
    </tr>
    <tr>
      <td align='left' valign='bottom'>&nbsp;</td>
      <td align='right' valign='bottom'><b>".T_("FORUM_CONTROL")."</b> &middot; <a href='?action=viewunread'><i class='fa fa-envelope'></i> ".T_("FORUM_NEW_POSTS")."</a> &middot; <a href='?catchup'><i class='fa fa-check'></i> ".T_("FORUM_MARK_READ")."</a></td>
    </tr>
  </table>
  </div>
</div>
<br />";
print ("<div class='f-location'><div class='f-nav'>".T_("YOU_ARE_IN").": &nbsp;<a href='forums.php'>".T_("FORUMS")."</a> <b style='vertical-align:middle'>/ $location</b></div></div>");
}

function catch_up(){
    global $CURUSER;
    if (!$CURUSER) return;
    $userid = (int)$CURUSER["id"];
    $res = SQL_Query_exec("SELECT id, lastpost FROM forum_topics");
    while ($arr = mysqli_fetch_assoc($res)) {
        $topicid = (int)$arr["id"];
        $postid = (int)$arr["lastpost"];
        $r = SQL_Query_exec("SELECT id,lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
        if (mysqli_num_rows($r) == 0) {
            SQL_Query_exec("INSERT INTO forum_readposts (userid, topicid, lastpostread) VALUES($userid, $topicid, $postid)");
        } else {
            $a = mysqli_fetch_assoc($r);
            if ((int)$a["lastpostread"] < $postid)
                SQL_Query_exec("UPDATE forum_readposts SET lastpostread=$postid WHERE id=" . (int)$a["id"]);
        }
    }
}

function get_forum_access_levels($forumid){
    $forumid = (int)$forumid;
    $res = SQL_Query_exec("SELECT minclassread, minclasswrite FROM forum_forums WHERE id=$forumid");
    if (mysqli_num_rows($res) != 1) return false;
    $arr = mysqli_fetch_assoc($res);
    return array("read" => $arr["minclassread"], "write" => $arr["minclasswrite"]);
}

function get_topic_forum($topicid) {
    $topicid = (int)$topicid;
    $res = SQL_Query_exec("SELECT forumid FROM forum_topics WHERE id=$topicid");
    if (mysqli_num_rows($res) != 1) return false;
    $arr = mysqli_fetch_row($res);
    return $arr[0];
}

function update_topic_last_post($topicid) {
    $topicid = (int)$topicid;
    $res = SQL_Query_exec("SELECT id FROM forum_posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1");
    $arr = mysqli_fetch_row($res) or showerror(T_("FORUM_ERROR"), "No post found");
    $postid = (int)$arr[0];
    SQL_Query_exec("UPDATE forum_topics SET lastpost=$postid WHERE id=$topicid");
}

function get_forum_last_post(int $forumid): int
{
    $forumid = (int)$forumid;
    $res = SQL_Query_exec("
        SELECT lastpost
        FROM forum_topics
        WHERE forumid = $forumid
        ORDER BY lastpost DESC
        LIMIT 1
    ");
    if (!$res) return 0;
    $arr = mysqli_fetch_row($res);
    if (!$arr || !isset($arr[0])) return 0;
    return (int)$arr[0];
}

function forumpostertable($res) {
    print("<br /><table class='f-topten' width='160' cellspacing='0'><tr><td>\n");
    print("<table class='ttable_headinner' width='100%'>");
    ?>
    <tr class='ttable_head'>
      <th width='10' align='center'><font size='1'><?php echo T_("FORUM_RANK"); ?></font></th>
      <th width='140' align='center'><font size='1'><?php echo T_("FORUM_USER"); ?></font></th>
      <th width='10' align='center'><font size='1'><?php echo T_("FORUM_POST"); ?></font></th>
    </tr>
    <?php
    $num = 0;
    while ($a = mysqli_fetch_assoc($res)) {
        ++$num;
        print("<tr class='t-row'><td align='center' class='ttable_col1'>$num</td><td class='ttable_col2' style='text-align: justify'><a href='account-details.php?id=$a[id]'><b>$a[username]</b></a></td><td align='center' class='ttable_col1'>$a[num]</td></tr>\n");
    }
    if ($num == 0)
        print("<tr class='t-row'><td align='center' class='ttable_col1' colspan='3'><b>No Forum Posters</b></td></tr>");
    print("</table></td></tr></table>\n");
}

function insert_quick_jump_menu($currentforum = 0) {
    print("<div style='text-align:right'><form method='get' action='forums.php' name='jump'>\n");
    print("<input type='hidden' name='action' value='viewforum' />\n");
    $res = SQL_Query_exec("SELECT * FROM forum_forums ORDER BY name");
    $CURUSER = $CURUSER ?? null;
    if (mysqli_num_rows($res) > 0) {
        print(T_("FORUM_JUMP") . ": ");
        print("<select class='styled' name='forumid' onchange='if(this.options[this.selectedIndex].value != -1){ forms[jump].submit() }'>\n");
        while ($arr = mysqli_fetch_assoc($res)) {
            if (get_user_class() >= $arr["minclassread"] || (!$CURUSER && $arr["guest_read"] == "yes"))
                print("<option value='" . $arr["id"] . "'" . ($currentforum == $arr["id"] ? " selected='selected'>" : ">") . $arr["name"] . "</option>\n");
        }
        print("</select>\n");
        print("<input type='submit' value='".T_("GO")."' />\n");
    }
    print("</form>\n</div>");
}

function insert_compose_frame($id, $newtopic = true) {
    global $maxsubjectlength;

    $id = (int)$id;

    if ($newtopic) {
        $res = SQL_Query_exec("SELECT name FROM forum_forums WHERE id=$id");
        $arr = mysqli_fetch_assoc($res) or showerror(T_("FORUM_ERROR"), T_("FORUM_BAD_FORUM_ID"));
        $forumname = stripslashes($arr["name"]);
        print("<p align='center'><b>".T_("FORUM_NEW_TOPIC")." <a href='forums.php?action=viewforum&amp;forumid=$id'>$forumname</a></b></p>\n");
    } else {
        $res = SQL_Query_exec("SELECT * FROM forum_topics WHERE id=$id");
        $arr = mysqli_fetch_assoc($res) or showerror(T_("FORUM_ERROR"), T_("FORUMS_NOT_FOUND_TOPIC"));
        $subject = stripslashes($arr["subject"]);
        print("<p align='center'>".T_("FORUM_REPLY_TOPIC").": <a href='forums.php?action=viewtopic&amp;topicid=$id'>$subject</a></p>");
    }

    print("<p align='center'>".T_("FORUM_RULES")."\n");
    print("<br />".T_("FORUM_RULES2")."<br /></p>\n");

    print("<fieldset class='download'>");
    print("<legend><b><i class='fa fa-pencil'></i> Compose Message</b></legend>");
    print("<div>");
    print("<form name='Form' method='post' action='forums.php?action=post'>\n");

    if ($newtopic)
        print("<input type='hidden' name='forumid' value='$id' />\n");
    else
        print("<input type='hidden' name='topicid' value='$id' />\n");

    print("<center><br /><table cellpadding='3' cellspacing='0'>");
    if ($newtopic)
        print("<tr><td><strong>Subject:</strong> <input type='text' size='70' maxlength='$maxsubjectlength' name='subject' /></td></tr>");
    print("<tr><td align='center'>");
    textbbcode("Form", "body");
    print("</td></tr><tr><td align='center'><br /><input type='submit' value='".T_("SUBMIT")."' /><br /><br /></td></tr></table>");
    print("<br /></center>");
    print("</form>");
    print("</div>");
    print("</fieldset><br />");

    insert_quick_jump_menu();
}

function latestforumposts() {
    print("<div class='f-border f-latestpost'><table width='100%' cellspacing='0'><tr class='f-title'>".
    "<th align='left'>Latest Topic Title</th>".
    "<th align='center' width='47'>Replies</th>".
    "<th align='center' width='47'>Views</th>".
    "<th align='center' width='85'>Author</th>".
    "<th align='right' width='150'>Last Post</th>".
    "</tr>");

    $for = SQL_Query_exec("SELECT * FROM forum_topics ORDER BY lastpost DESC LIMIT 5");

    if (mysqli_num_rows($for) == 0)
        print("<tr class='f-row'><td class='alt1' align='center' colspan='5'><b>No Latest Topics</b></td></tr>");

    while ($topicarr = mysqli_fetch_assoc($for)) {
        $res = SQL_Query_exec("SELECT name,minclassread,guest_read FROM forum_forums WHERE id=$topicarr[forumid]");
        $forum = mysqli_fetch_assoc($res);

        if ($forum && (get_user_class() >= $forum["minclassread"] || $forum["guest_read"] == "yes")) {
            $forumname = "<a href='?action=viewforum&amp;forumid=$topicarr[forumid]'><b>" . htmlspecialchars($forum["name"]) . "</b></a>";
            $topicid = (int)$topicarr["id"];
            $topic_title = stripslashes($topicarr["subject"]);
            $topic_userid = (int)$topicarr["userid"];
            $views = (int)$topicarr["views"];

            $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
            $arr = mysqli_fetch_row($res);
            $posts = (int)$arr[0];
            $replies = max(0, $posts - 1);

            $res = SQL_Query_exec("SELECT * FROM forum_posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1");
            $arr = mysqli_fetch_assoc($res);
            $postid = (int)$arr["id"];
            $userid = (int)$arr["userid"];
            $added = utc_to_tz($arr["added"]);

            $res = SQL_Query_exec("SELECT id, username FROM users WHERE id=$userid");
            if (mysqli_num_rows($res) == 1) {
                $arr = mysqli_fetch_assoc($res);
                $username = "<a href='account-details.php?id=$userid'>$arr[username]</a>";
            } else {
                $username = "Unknown[$topic_userid]";
            }

            $res = SQL_Query_exec("SELECT username FROM users WHERE id=$topic_userid");
            if (mysqli_num_rows($res) == 1) {
                $arr = mysqli_fetch_assoc($res);
                $author = "<a href='account-details.php?id=$topic_userid'>$arr[username]</a>";
            } else {
                $author = "Unknown[$topic_userid]";
            }

            $r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
            $a = mysqli_fetch_row($r);
            $new = !$a || $postid > $a[0];

            $subject = "<a href='forums.php?action=viewtopic&amp;topicid=$topicid'><b>" . stripslashes(encodehtml($topicarr["subject"])) . "</b></a>";

            print("<tr class='f-row'><td class='f-img' width='100%'>$subject</td>".
            "<td class='alt2' align='center'><small>$replies</small></td>".
            "<td class='alt3' align='center'><small>$views</small></td>".
            "<td class='alt2' align='center'><small>$author</small></td>".
            "<td class='alt3' align='right'><small>by&nbsp;$username<br /> $added</small></td></tr>");
        }
    }
    print("</table></div><br />");
}

$postsperpage = 20;
$maxsubjectlength = 50;

if ($action == "newtopic") {
    $forumid = (int)($_GET["forumid"] ?? 0);
    if (!is_valid_id($forumid))
        showerror(T_("FORUM_ERROR"), "No Forum ID $forumid");

    stdhead("New topic");
    begin_frame("New topic");
    forumheader("Compose New Thread");
    insert_compose_frame($forumid);
    end_frame();
    stdfoot();
    die;
}

if ($action == "post") {
    $forumid = (int)($_POST["forumid"] ?? 0);
    $topicid = (int)($_POST["topicid"] ?? 0);

    if (!is_valid_id($forumid) && !is_valid_id($topicid))
        showerror(T_("FORUM_ERROR"), "w00t");

    $newtopic = $forumid > 0;
    $subject = $_POST["subject"] ?? '';

    if ($newtopic) {
        if (!$subject)
            showerror(T_("ERROR"), "You must enter a subject.");
        $subject = trim($subject);
    } else {
        $forumid = get_topic_forum($topicid) or showerror(T_("FORUM_ERROR"),"Bad topic ID");
    }

    $arr = get_forum_access_levels($forumid) or showerror(T_("FORUM_ERROR"),"Bad forum ID");
    if (get_user_class() < $arr["write"])
        showerror(T_("FORUM_ERROR"),T_("FORUMS_NOT_PERMIT"));

    $body = trim($_POST["body"] ?? '');
    if (!$body)
        showerror(T_("ERROR"), "No body text.");

    $userid = (int)$CURUSER["id"];

    if ($newtopic) {
        $subject = sqlesc($subject);
        SQL_Query_exec("INSERT INTO forum_topics (userid, forumid, subject) VALUES($userid, $forumid, $subject)");
        $topicid = mysqli_insert_id($GLOBALS["DBconnector"]) or showerror(T_("FORUM_ERROR"),"No topic ID returned");
    } else {
        $res = SQL_Query_exec("SELECT * FROM forum_topics WHERE id=$topicid");
        $arr = mysqli_fetch_assoc($res) or showerror(T_("FORUM_ERROR"),"Topic id n/a");
        if ($arr["locked"] == 'yes')
            showerror(T_("FORUM_ERROR"),"Topic locked");
        $forumid = (int)$arr["forumid"];
    }

    $added = "'" . get_date_time() . "'";
    $body = sqlesc($body);
    SQL_Query_exec("INSERT INTO forum_posts (topicid, userid, added, body) VALUES($topicid, $userid, $added, $body)");
    $postid = mysqli_insert_id($GLOBALS["DBconnector"]) or showerror(T_("FORUM_ERROR"),"Post id n/a");

    update_topic_last_post($topicid);

    $headerstr = "Location: $site_config[SITEURL]/forums.php?action=viewtopic&topicid=$topicid&page=last";
    if ($newtopic)
        header($headerstr);
    else
        header("$headerstr#post$postid");
    die;
}

if ($action == "viewtopic") {
    $topicid = (int)($_GET["topicid"] ?? 0);
    $page = $_GET["page"] ?? 1;
    if (!is_valid_id($topicid))
        showerror(T_("FORUM_ERROR"),"Topic Not Valid");

    $userid = (int)$CURUSER["id"];

    $res = SQL_Query_exec("SELECT * FROM forum_topics WHERE id=$topicid");
    $arr = mysqli_fetch_assoc($res) or showerror(T_("FORUM_ERROR"), "Topic not found");
    $locked = ($arr["locked"] == 'yes');
    $subject = stripslashes($arr["subject"]);
    $sticky = $arr["sticky"] == "yes";
    $forumid = (int)$arr["forumid"];

    $res2 = SQL_Query_exec("SELECT minclassread, guest_read FROM forum_forums WHERE id=$forumid");
    $arr2 = mysqli_fetch_assoc($res2);
    if (!$arr2 || (get_user_class() < $arr2["minclassread"] && $arr2["guest_read"] == "no"))
        show_error_msg("Error: Access Denied","You do not have access to the forum this topic is in.");

    $viewsq = SQL_Query_exec("SELECT views FROM forum_topics WHERE id=$topicid");
    $viewsa = mysqli_fetch_array($viewsq);
    $views = (int)$viewsa[0];
    $new_views = $views + 1;
    SQL_Query_exec("UPDATE forum_topics SET views = $new_views WHERE id=$topicid");

    $res = SQL_Query_exec("SELECT * FROM forum_forums WHERE id=$forumid");
    $arr = mysqli_fetch_assoc($res) or showerror(T_("FORUM_ERROR"), "Forum is empty");
    $forum = stripslashes($arr["name"]);

    $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
    $arr = mysqli_fetch_row($res);
    $postcount = (int)$arr[0];

    $pagemenu = "<br /><small>\n";
    $perpage = $postsperpage;
    $pages = floor($postcount / $perpage);
    if ($pages * $perpage < $postcount) ++$pages;
    if ($pages < 1) $pages = 1;

    if ($page === "last")
        $page = $pages;
    else {
        $page = filter_var($page, FILTER_VALIDATE_INT);
        if (!$page || $page < 1) $page = 1;
        if ($page > $pages) $page = $pages;
    }

    $offset = max(0, ($page * $perpage) - $perpage);

    if ($page == 1)
        $pagemenu .= "<b>&lt;&lt; Prev</b>";
    else
        $pagemenu .= "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page - 1) . "'><b>&lt;&lt; Prev</b></a>";

    $pagemenu .= "&nbsp;&nbsp;";
    for ($i = 1; $i <= $pages; ++$i) {
        if ($i == $page)
            $pagemenu .= "<b>$i</b>\n";
        else
            $pagemenu .= "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'><b>$i</b></a>\n";
    }

    $pagemenu .= "&nbsp;&nbsp;";
    if ($page == $pages)
        $pagemenu .= "<b>Next &gt;&gt;</b><br /><br />\n";
    else
        $pagemenu .= "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page + 1) . "'><b>Next &gt;&gt;</b></a><br /><br />\n";
    $pagemenu .= "</small>";

    $res = SQL_Query_exec("SELECT * FROM forum_posts WHERE topicid=$topicid ORDER BY id LIMIT $offset,$perpage");

    stdhead("View Topic: $subject");
    begin_frame("$forum &gt; $subject");
    print("<a id='top'></a>");
    forumheader("<a href='forums.php?action=viewforum&amp;forumid=$forumid'>$forum</a> <b style='font-size:16px; vertical-align:middle'>/</b> $subject");

    print("<div style='padding: 6px'>");

    $levels = get_forum_access_levels($forumid) or die;
    $maypost = get_user_class() >= $levels["write"];

    if (!$locked && $maypost) {
        print("<div align='right' class='forum-top-actions'>
            <a href='forums.php?action=reply&amp;topicid=$topicid' class='btn btn-success forum-main-btn'>
                <i class='fa fa-reply'></i> Reply
            </a>
        </div>");
    } else {
        print("<div align='right' class='forum-top-actions'>
            <span class='btn btn-danger forum-main-btn'>
                <i class='fa fa-lock'></i> ".T_("FORUMS_LOCKED")."
            </span>
        </div>");
    }
    print("</div>");

    $pc = mysqli_num_rows($res);
    $pn = 0;
    $lpr = null;

    if ($CURUSER) {
        $r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$CURUSER[id] AND topicid=$topicid");
        $a = mysqli_fetch_row($r);
        $lpr = $a[0] ?? null;
        if (!$lpr)
            SQL_Query_exec("INSERT INTO forum_readposts (userid, topicid) VALUES($userid, $topicid)");
    }

    while ($arr = mysqli_fetch_assoc($res)) {
        ++$pn;
        $postid = (int)$arr["id"];
        $posterid = (int)$arr["userid"];
        $added = utc_to_tz($arr["added"])."(" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " ago)";

        $res4 = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE userid=$posterid");
        $arr33 = mysqli_fetch_row($res4);
        $forumposts = (int)$arr33[0];

        $res2 = SQL_Query_exec("SELECT * FROM users WHERE id=$posterid");
        $arr2 = mysqli_fetch_assoc($res2);
        $postername = $arr2["username"];

        if ($postername == "") {
            $by = "Deluser";
            $title = "Deleted Account";
            $privacylevel = "strong";
            $usersignature = "";
            $userdownloaded = "0";
            $useruploaded = "0";
            $avatar = "<span class='btn btn-default forum-post-btn'><i class='fa-regular fa-circle-user fa-10x'></i></span>";
            $nposts = "-";
            $tposts = "-";
        } else {
            $userdownloaded = mksize($arr2["downloaded"]);
            $useruploaded = mksize($arr2["uploaded"]);
            $privacylevel = $arr2["privacy"];
            $usersignature = stripslashes(format_comment($arr2["signature"]));

            if ($arr2["downloaded"] > 0) {
                $userratio = number_format($arr2["uploaded"] / $arr2["downloaded"], 2);
            } elseif ($arr2["uploaded"] > 0) {
                $userratio = "Inf.";
            } else {
                $userratio = "---";
            }

            if (!$arr2["country"]) {
                $usercountry = "unknown";
            } else {
                $res4 = SQL_Query_exec("SELECT name,flagpic FROM countries WHERE id=$arr2[country] LIMIT 1");
                $arr4 = mysqli_fetch_assoc($res4);
                $usercountry = $arr4["name"];
            }

            $title = format_comment($arr2["title"]);
            $donated = $arr2['donated'];
            $by = "<a href='account-details.php?id=$posterid'>$postername</a>" . ($donated > 0 ? "<i class='fa fa-star forum-donated' title='Donated'></i>" : "");
            $avatar = "<span><i class='fa-regular fa-circle-user' style='font-size: 80px !important; color: silver !important;'></i></span>";
        }

        print("<a id='post$postid'></a>");

        if ($pn == $pc) {
            print("<a name='last'></a>\n");
            if ($postid > $lpr && $CURUSER)
                SQL_Query_exec("UPDATE forum_readposts SET lastpostread=$postid WHERE userid=$userid AND topicid=$topicid");
        }

        print("<div class='f-border f-post'><table width='100%' cellspacing='0'><tr class='p-title'><th width='150'>$by</th><th align='left'><small>Posted at $added </small></th></tr>");

        $body = stripslashes(format_comment($arr["body"]));

        if (is_valid_id($arr['editedby'])) {
            $res2 = SQL_Query_exec("SELECT username FROM users WHERE id=$arr[editedby]");
            if (mysqli_num_rows($res2) == 1) {
                $arr2 = mysqli_fetch_assoc($res2);
                $body .= "<br /><br /><small><i>Last edited by <a href='account-details.php?id=$arr[editedby]'>$arr2[username]</a> on ".utc_to_tz($arr["editedat"])."</i></small><br />\n";
                $body .= "\n";
            }
        }

        $quote = htmlspecialchars($arr["body"]);

        if ($privacylevel == "strong" && $CURUSER["control_panel"] != "yes") {
            $useruploaded = "---";
            $userdownloaded = "---";
            $userratio = "---";
            $nposts = "-";
            $tposts = "-";
        }

        print("<tr valign='top'><td width='150' align='left' class='comment-details'>
            <center><i>$title</i></center>
            <br /><center>$avatar</center>
            <br /><small>Uploaded: $useruploaded</small><br />
            <small>Downloaded: $userdownloaded</small><br />
            <small>Posts: $forumposts</small><br /><br />
            <small>Ratio: $userratio</small><br />
            <small>Location: $usercountry</small><br />
        </td>");

        print("<td class='comment'><br />$body<br />");

        if (!$usersignature) {
            print("<br /></td></tr>\n");
        } else {
            print("<br /><hr /><br /><div class='f-sig' align='center'>$usersignature</div></td></tr>\n");
        }

        print("<tr class='p-foot'><td width='150' align='center'>
            <div class='forum-post-actions' style='justify-content:center;'>
                <a href='account-details.php?id=$posterid' class='btn btn-primary btn-sm forum-post-btn forum-action-profile' title='Profile'><i class='fa fa-user'></i></a>
                <a href='mailbox.php?compose&amp;id=$posterid' class='btn btn-info btn-sm forum-post-btn forum-action-pm' title='Private Message'><i class='fa fa-envelope'></i></a>
            </div>
        </td><td>");

        print("<div class='forum-post-actions'>
            <div class='forum-post-left-actions'>
                <a href='report.php?forumid=$topicid&amp;forumpost=$postid' class='btn btn-warning btn-sm forum-post-btn forum-action-report' title='".T_("FORUMS_REPORT_POST")."'><i class='fa fa-flag'></i></a>
                <a href='#top' class='btn btn-default btn-sm forum-post-btn forum-action-top' title='".T_("FORUMS_GOTO_TOP_PAGE")."'><i class='fa fa-arrow-up'></i></a>
            </div>
            <div class='forum-post-left-actions'>");

        if ($CURUSER["id"] == $posterid || $CURUSER["edit_forum"] == "yes" || $CURUSER["delete_forum"] == "yes") {
            print("<a href='forums.php?action=editpost&amp;postid=$postid' class='btn btn-info btn-sm forum-post-btn forum-action-edit' title='Edit'><i class='fa fa-pencil'></i></a>");
        }

        if ($CURUSER["delete_forum"] == "yes") {
            print("<a href='forums.php?action=deletepost&amp;postid=$postid&amp;sure=0' class='btn btn-danger btn-sm forum-post-btn forum-action-delete' title='Delete'><i class='fa fa-trash'></i></a>");
        }

        if (!$locked && $maypost) {
            print("<a href=\"javascript:SmileIT('[quote=$postername] $quote [/quote]', 'Form', 'body');\" class='btn btn-default btn-sm forum-post-btn forum-action-quote' title='Quote'><i class='fa fa-quote-left'></i></a>");
            print("<a href='forums.php?action=reply&amp;topicid=$topicid' class='btn btn-success btn-sm forum-post-btn forum-action-reply' title='Reply'><i class='fa fa-reply'></i></a>");
        }

        print("</div></div></td></tr></table></div>");
    }

    print($pagemenu);

    if (!$locked && $CURUSER) {
        print("<fieldset class='download'><legend><b><i class='fa fa-reply'></i> ".T_("FORUMS_POST_REPLY")."</b></legend>");
        $newtopic = false;
        print("<a name='bottom' id='bottom'></a>");
        print("<form name='Form' method='post' action='forums.php?action=post'>\n");
        print("<input type='hidden' name='topicid' value='$topicid' />\n");
        print("<table cellspacing='0' cellpadding='0' align='center'>");
        echo "<tr><td align='center' colspan='3'>";
        textbbcode("Form", "body");
        echo "</td></tr>\n";
        print("<tr><td colspan='3' align='center'><br /><button type='submit' class='button'><i class='fa fa-paper-plane'></i> ".T_("SUBMIT")."</button></td></tr>\n");
        print("</table></form>\n");
        print("</fieldset>");
    } else {
        print("<span class='forum-locked'><i class='fa fa-lock'></i> ".T_("FORUMS_LOCKED")."</span><br />");
    }

    if ($locked)
        print(T_("FORUMS_TOPIC_LOCKED")."\n");
    elseif (!$maypost)
        print("<i>".T_("FORUMS_YOU_NOT_PERM_POST_FORUM")."</i>\n");

    if ($CURUSER["delete_forum"] == "yes" || $CURUSER["edit_forum"] == "yes") {
        print("<br /><div class='f-border f-mod_options' align='center'><table width='100%' cellspacing='0'><tr class='f-title'><th>".T_("FORUMS_MOD_OPTIONS")."</th></tr>\n");
        $res = SQL_Query_exec("SELECT id,name,minclasswrite FROM forum_forums ORDER BY name");
        print("<tr><td class='ttable_col2'>\n");

        print("<form method='post' action='forums.php?action=renametopic'>\n");
        print("<input type='hidden' name='topicid' value='$topicid' />");
        print("<input type='hidden' name='returnto' value='forums.php?action=viewtopic&amp;topicid=$topicid' />");
        print("<div align='center' style='padding:3px'>Rename topic: <input type='text' name='subject' size='60' maxlength='$maxsubjectlength' value='" . stripslashes(htmlspecialchars($subject)) . "' />");
        print("<input type='submit' value='Apply' /></div></form>\n");

        print("<form method='post' action='forums.php?action=movetopic&amp;topicid=$topicid'>");
        print("<div align='center' style='padding:3px'>Move this thread to: <select name='forumid'>");
        while ($arr = mysqli_fetch_assoc($res))
            if ($arr["id"] != $forumid && get_user_class() >= $arr["minclasswrite"])
                print("<option value='" . $arr["id"] . "'>" . $arr["name"] . "</option>\n");
        print("</select> <input type='submit' value='Apply' /></div></form>\n");

        print("<div align='center'>");
        if ($locked)
            print(T_("FORUMS_LOCKED").": <a href='forums.php?action=unlocktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='Unlock'><i class='fa fa-unlock'></i></a>\n");
        else
            print(T_("FORUMS_LOCKED").": <a href='forums.php?action=locktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='Lock'><i class='fa fa-lock'></i></a>\n");

        print("Delete Entire Topic: <a href='forums.php?action=deletetopic&amp;topicid=$topicid&amp;sure=0' title='Delete'><i class='fa fa-trash'></i></a>\n");

        if ($sticky)
            print(T_("FORUMS_STICKY").": <a href='forums.php?action=unsetsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='UnStick'><i class='fa fa-thumb-tack'></i></a>\n");
        else
            print(T_("FORUMS_STICKY").": <a href='forums.php?action=setsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='Stick'><i class='fa fa-thumb-tack'></i></a>\n");

        print("</div><br /></td></tr></table></div>");
    }

    end_frame();
    stdfoot();
    die;
}

if ($action == "reply") {
    $topicid = (int)($_GET["topicid"] ?? 0);
    if (!is_valid_id($topicid))
        showerror(T_("FORUM_ERROR"), sprintf(T_("FORUMS_NO_ID_FORUM"), $topicid));

    $forumid = get_topic_forum($topicid);
    if (!$forumid)
        showerror(T_("FORUM_ERROR"), "Bad topic ID");

    $levels = get_forum_access_levels($forumid);
    if (!$levels || get_user_class() < $levels["write"])
        showerror(T_("FORUM_ERROR"), T_("FORUMS_NOT_PERMIT"));

    $res = SQL_Query_exec("SELECT locked FROM forum_topics WHERE id=$topicid");
    $topic = mysqli_fetch_assoc($res);
    if (!$topic)
        showerror(T_("FORUM_ERROR"), T_("FORUMS_NOT_FOUND_TOPIC"));
    if ($topic["locked"] == "yes")
        showerror(T_("FORUM_ERROR"), T_("FORUMS_TOPIC_LOCKED"));

    stdhead(T_("FORUMS_POST_REPLY"));
    begin_frame(T_("FORUMS_POST_REPLY"));
    forumheader("Reply");
    insert_compose_frame($topicid, false);
    end_frame();
    stdfoot();
    die;
}

if ($action == "movetopic") {
    $forumid = (int)($_POST["forumid"] ?? 0);
    $topicid = (int)($_GET["topicid"] ?? 0);

    if (!is_valid_id($forumid) || !is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes" || $CURUSER["edit_forum"] != "yes")
        showerror(T_("FORUM_ERROR"), sprintf(T_("FORUMS_NO_ID_FORUM"),$forumid,$topicid));

    $res = @SQL_Query_exec("SELECT minclasswrite FROM forum_forums WHERE id=$forumid");
    if (mysqli_num_rows($res) != 1)
        showerror(T_("ERROR"), T_("FORUMS_NOT_FOUND"));

    $arr = mysqli_fetch_row($res);
    if (get_user_class() < $arr[0])
        showerror(T_("FORUM_ERROR"), T_("FORUMS_NOT_ALLOWED"));

    $res = @SQL_Query_exec("SELECT subject,forumid FROM forum_topics WHERE id=$topicid");
    if (mysqli_num_rows($res) != 1)
        showerror(T_("ERROR"), T_("FORUMS_NOT_FOUND_TOPIC"));

    $arr = mysqli_fetch_assoc($res);
    if ($arr["forumid"] != $forumid)
        @SQL_Query_exec("UPDATE forum_topics SET forumid=$forumid, moved='yes' WHERE id=$topicid");

    header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid");
    die;
}

if ($action == "deletetopic") {
    $topicid = (int)($_GET["topicid"] ?? 0);
    if (!is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes")
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    $sure = $_GET["sure"] ?? '';
    if ($sure == "0")
        showerror(T_("FORUMS_DEL_TOPIC"), sprintf(T_("FORUMS_DEL_TOPIC_SANITY_CHK"), $topicid));

    SQL_Query_exec("DELETE FROM forum_topics WHERE id=$topicid");
    SQL_Query_exec("DELETE FROM forum_posts WHERE topicid=$topicid");
    SQL_Query_exec("DELETE FROM forum_readposts WHERE topicid=$topicid");
    header("Location: $site_config[SITEURL]/forums.php");
    die;
}

if ($action == "editpost") {
    $postid = (int)($_GET["postid"] ?? 0);
    if (!is_valid_id($postid))
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    $res = SQL_Query_exec("SELECT * FROM forum_posts WHERE id=$postid");
    if (mysqli_num_rows($res) != 1)
        showerror(T_("ERROR"), sprintf(T_("FORUMS_NO_ID_POST"), $postid));

    $arr = mysqli_fetch_assoc($res);
    if ($CURUSER["id"] != $arr["userid"] && $CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes")
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $body = $_POST['body'] ?? '';
        if ($body == "")
            showerror(T_("ERROR"), "Body cannot be empty!");

        $body = sqlesc($body);
        $editedat = sqlesc(get_date_time());
        SQL_Query_exec("UPDATE forum_posts SET body=$body, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$postid");

        $returnto = $_POST["returnto"] ?? '';
        if ($returnto)
            header("Location: $returnto");
        else
            showerror(T_("SUCCESS"), "Post was edited successfully.");
        die;
    }

    stdhead();
    begin_frame(T_("FORUMS_EDIT_POST"));
    print("<form name='Form' method='post' action='?action=editpost&amp;postid=$postid'>\n");
    print("<input type='hidden' name='returnto' value='" . htmlspecialchars($_SERVER["HTTP_REFERER"] ?? 'forums.php') . "' />\n");
    print("<center><table cellspacing='0' cellpadding='5'>\n");
    print("<tr><td colspan='2'>\n");
    textbbcode("Form", "body", htmlspecialchars($arr["body"]));
    print("</td></tr>");
    print("<tr><td align='center' colspan='2'><button type='submit' class='button'><i class='fa fa-save'></i> ".T_("SUBMIT")."</button></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
    end_frame();
    stdfoot();
    die;
}

if ($action == "deletepost") {
    $postid = (int)($_GET["postid"] ?? 0);
    $sure = $_GET["sure"] ?? '';

    if ($CURUSER["delete_forum"] != "yes" || !is_valid_id($postid))
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    if ($sure == "0")
        showerror(T_("FORUMS_DEL_POST"), sprintf(T_("FORUMS_DEL_POST_SANITY_CHK"), $postid));

    $res = SQL_Query_exec("SELECT topicid FROM forum_posts WHERE id=$postid");
    $arr = mysqli_fetch_row($res) or showerror(T_("ERROR"), T_("FORUMS_NOT_FOUND_POST"));
    $topicid = (int)$arr[0];

    $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
    $arr = mysqli_fetch_row($res);
    if ($arr[0] < 2)
        showerror(T_("ERROR"), sprintf(T_("FORUMS_DEL_POST_ONLY_POST"), $topicid));

    SQL_Query_exec("DELETE FROM forum_posts WHERE id=$postid");
    update_topic_last_post($topicid);
    header("Location: $site_config[SITEURL]/forums.php?action=viewtopic&topicid=$topicid");
    die;
}

if ($action == "locktopic") {
    $forumid = (int)($_GET["forumid"] ?? 0);
    $topicid = (int)($_GET["topicid"] ?? 0);
    $page = (int)($_GET["page"] ?? 1);

    if (!is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes" || $CURUSER["edit_forum"] != "yes")
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    SQL_Query_exec("UPDATE forum_topics SET locked='yes' WHERE id=$topicid");
    header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
    die;
}

if ($action == "unlocktopic") {
    $forumid = (int)($_GET["forumid"] ?? 0);
    $topicid = (int)($_GET["topicid"] ?? 0);
    $page = (int)($_GET["page"] ?? 1);

    if (!is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes" || $CURUSER["edit_forum"] != "yes")
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    SQL_Query_exec("UPDATE forum_topics SET locked='no' WHERE id=$topicid");
    header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
    die;
}

if ($action == "setsticky") {
    $forumid = (int)($_GET["forumid"] ?? 0);
    $topicid = (int)($_GET["topicid"] ?? 0);
    $page = (int)($_GET["page"] ?? 1);

    if (!is_valid_id($topicid) || ($CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes"))
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    SQL_Query_exec("UPDATE forum_topics SET sticky='yes' WHERE id=$topicid");
    header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
    die;
}

if ($action == "unsetsticky") {
    $forumid = (int)($_GET["forumid"] ?? 0);
    $topicid = (int)($_GET["topicid"] ?? 0);
    $page = (int)($_GET["page"] ?? 1);

    if (!is_valid_id($topicid) || ($CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes"))
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    SQL_Query_exec("UPDATE forum_topics SET sticky='no' WHERE id=$topicid");
    header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
    die;
}

if ($action == 'renametopic') {
    if ($CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes")
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    $topicid = (int)($_POST['topicid'] ?? 0);
    if (!is_valid_id($topicid))
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    $subject = $_POST['subject'] ?? '';
    if ($subject == '')
        showerror(T_("ERROR"), T_("FORUMS_YOU_MUST_ENTER_NEW_TITLE"));

    $subject = sqlesc($subject);
    SQL_Query_exec("UPDATE forum_topics SET subject=$subject WHERE id=$topicid");

    $returnto = $_POST['returnto'] ?? '';
    if ($returnto)
        header("Location: $returnto");
    die;
}

if ($action == "viewforum") {
    $forumid = (int)($_GET["forumid"] ?? 0);
    if (!is_valid_id($forumid))
        showerror(T_("ERROR"), T_("FORUMS_DENIED"));

    $page = (int)($_GET["page"] ?? 1);
    if ($page < 1) $page = 1;
    $userid = (int)$CURUSER["id"];

    $res = SQL_Query_exec("SELECT name, minclassread, guest_read FROM forum_forums WHERE id=$forumid");
    $arr = mysqli_fetch_assoc($res);
    $forumname = $arr["name"];

    if (!$forumname || (get_user_class() < $arr["minclassread"] && $arr["guest_read"] == "no"))
        showerror(T_("ERROR"), T_("FORUMS_NOT_PERMIT"));

    $perpage = 20;
    $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_topics WHERE forumid=$forumid");
    $arr = mysqli_fetch_row($res);
    $num = (int)$arr[0];

    $first = ($page * $perpage) - $perpage + 1;
    $last = $first + $perpage - 1;
    if ($last > $num) $last = $num;

    $pages = floor($num / $perpage);
    if ($perpage * $pages < $num) ++$pages;
    if ($pages < 1) $pages = 1;
    if ($page > $pages) $page = $pages;

    $menu = "<p align='center'><b>\n";
    $lastspace = false;

    for ($i = 1; $i <= $pages; ++$i) {
        if ($i == $page) {
            $menu .= "<span class='next-prev'>$i</span>\n";
        } elseif ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3)) {
            if ($lastspace) continue;
            $menu .= "... \n";
            $lastspace = true;
        } else {
            $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=$i'>$i</a>\n";
            $lastspace = false;
        }
        if ($i < $pages) $menu .= "</b>|<b>\n";
    }

    $menu .= "<br />\n";

    if ($page == 1)
        $menu .= "<span class='next-prev'>&lt;&lt; Prev</span>";
    else
        $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page - 1) . "'>&lt;&lt; Prev</a>";

    $menu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($last == $num)
        $menu .= "<span class='next-prev'>Next &gt;&gt;</span>";
    else
        $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&page=" . ($page + 1) . "'>Next &gt;&gt;</a>";

    $menu .= "</b></p>\n";
    $offset = max(0, $first - 1);

    $topicsres = SQL_Query_exec("SELECT * FROM forum_topics WHERE forumid=$forumid ORDER BY sticky, lastpost DESC LIMIT $offset,$perpage");

    stdhead("Forum : $forumname");
    $numtopics = mysqli_num_rows($topicsres);
    begin_frame("$forumname");
    forumheader("<a href='forums.php?action=viewforum&amp;forumid=$forumid'>$forumname</a>");

    if ($CURUSER)
        print("<table cellpadding='0' cellspacing='5' width='100%'><tr><td><div align='right'><a href='forums.php?action=newtopic&amp;forumid=$forumid' class='btn btn-primary forum-main-btn'><i class='fa fa-plus'></i> New Post</a></div></td></tr></table>");

    if ($numtopics > 0) {
        print("<div class='f-border f-sub_forum'><table width='100%' cellspacing='0'>");
        print("<tr class='f-title'><th align='left' colspan='2' width='100%'>Topic</th><th>Replies</th><th>Views</th><th>Author</th><th align='right'>Last post</th>\n");
        if ($CURUSER["edit_forum"] == "yes" || $CURUSER["delete_forum"] == "yes")
            print("<th>Moderator</th>");
        print("</tr>\n");

        while ($topicarr = mysqli_fetch_assoc($topicsres)) {
            $topicid = (int)$topicarr["id"];
            $topic_userid = (int)$topicarr["userid"];
            $locked = $topicarr["locked"] == "yes";
            $sticky = $topicarr["sticky"] == "yes";

            $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
            $arr = mysqli_fetch_row($res);
            $posts = (int)$arr[0];
            $replies = max(0, $posts - 1);

            $tpages = floor($posts / $postsperpage);
            if ($tpages * $postsperpage != $posts) ++$tpages;

            if ($tpages > 1) {
                $topicpages = " <span title='Pages'><i class='fa fa-files-o'></i>";
                for ($i = 1; $i <= $tpages; ++$i)
                    $topicpages .= " <a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'>$i</a>";
                $topicpages .= "</span>";
            } else {
                $topicpages = "";
            }

            $res = SQL_Query_exec("SELECT * FROM forum_posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1");
            $arr = mysqli_fetch_assoc($res);
            $lppostid = (int)$arr["id"];
            $lpuserid = (int)$arr["userid"];
            $lpadded = utc_to_tz($arr["added"]);

            if ($lpuserid > 0) {
                $res = SQL_Query_exec("SELECT * FROM users WHERE id=$lpuserid");
                if (mysqli_num_rows($res) == 1) {
                    $arr = mysqli_fetch_assoc($res);
                    $lpusername = "<a href='account-details.php?id=$lpuserid'>$arr[username]</a>";
                } else {
                    $lpusername = "Deluser";
                }
            } else {
                $lpusername = "Deluser";
            }

            if ($topic_userid > 0) {
                $res = SQL_Query_exec("SELECT username FROM users WHERE id=$topic_userid");
                if (mysqli_num_rows($res) == 1) {
                    $arr = mysqli_fetch_assoc($res);
                    $lpauthor = "<a href='account-details.php?id=$topic_userid'>$arr[username]</a>";
                } else {
                    $lpauthor = "Deluser";
                }
            } else {
                $lpauthor = "Deluser";
            }

            $viewsq = SQL_Query_exec("SELECT views FROM forum_topics WHERE id=$topicid");
            $viewsa = mysqli_fetch_array($viewsq);
            $views = (int)$viewsa[0];

            if ($CURUSER) {
                $r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
                $a = mysqli_fetch_row($r);
            } else {
                $a = null;
            }

            $new = !$a || $lppostid > $a[0];
            $topicpic = ($locked ? ($new ? "lock" : "lock") : ($new ? "folder-open" : "folder"));

            $subject = ($sticky ? "<b>".T_("FORUMS_STICKY").": </b>" : "") .
                "<a href='forums.php?action=viewtopic&amp;topicid=$topicid'><b>" .
                encodehtml(stripslashes($topicarr["subject"])) . "</b></a>$topicpages";

            print("<tr class='f-row'>");
            if ($locked) {
                $status_class = "forum-status-locked";
                $status_icon = "fa-lock";
                $status_title = T_("FORUMS_LOCKED");
            } elseif ($new) {
                $status_class = "forum-status-new";
                $status_icon = "fa-folder-open";
                $status_title = "New posts";
            } else {
                $status_class = "forum-status-old";
                $status_icon = "fa-folder";
                $status_title = "No new posts";
            }

            print("<td class='f-img' valign='middle' align='center'><span class='btn btn-sm forum-status-btn $status_class' title='$status_title'><i class='fa $status_icon'></i></span></td>");
            print("<td class='alt1' align='left' width='100%'>$subject</td>");
            print("<td class='alt2' align='center'>$replies</td>");
            print("<td class='alt3' align='center'>$views</td>");
            print("<td class='alt2' align='center'>$lpauthor</td>");
            print("<td class='alt3' align='right'><span class='small'>by&nbsp;$lpusername<br /><span style='white-space: nowrap'>$lpadded</span></span></td>");

            if ($CURUSER["edit_forum"] == "yes" || $CURUSER["delete_forum"] == "yes") {
                print("<td class='alt2' align='center'><span style='white-space: nowrap'>");

                if ($locked)
                    print("<a href='forums.php?action=unlocktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' class='btn btn-default btn-sm forum-post-btn forum-action-top' title='Unlock'><i class='fa fa-unlock'></i></a>\n");
                else
                    print("<a href='forums.php?action=locktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' class='btn btn-danger btn-sm forum-post-btn forum-action-delete' title='Lock'><i class='fa fa-lock'></i></a>\n");

                print("<a href='forums.php?action=deletetopic&amp;topicid=$topicid&amp;sure=0' class='btn btn-danger btn-sm forum-post-btn forum-action-delete' title='Delete'><i class='fa fa-trash'></i></a>\n");

                if ($sticky)
                    print("<a href='forums.php?action=unsetsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' class='btn btn-warning btn-sm forum-post-btn forum-action-report' title='UnStick'><i class='fa fa-thumb-tack'></i></a>\n");
                else
                    print("<a href='forums.php?action=setsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' class='btn btn-warning btn-sm forum-post-btn forum-action-report' title='Stick'><i class='fa fa-thumb-tack'></i></a>\n");

                print("</span></td>");
            }

            print("</tr>\n");
        }

        print("</table></div>");
        print($menu);
    } else {
        print("<p align='center'>No topics found</p>\n");
    }

    print("<div class='forum-status-legend'>");
    print("<span class='forum-status-legend-label'>Status:</span>");
    print("<span class='btn btn-sm forum-status-btn forum-status-new' title='New posts'><i class='fa fa-folder-open'></i>&nbsp; &nbsp; New posts</span>");
    print("<span class='btn btn-sm forum-status-btn forum-status-old' title='No new posts'><i class='fa fa-folder'></i>&nbsp; &nbsp; No new posts</span>");
    print("<span class='btn btn-sm forum-status-btn forum-status-locked' title='".T_("FORUMS_LOCKED")."'><i class='fa fa-lock'></i>&nbsp; &nbsp; ".T_("FORUMS_LOCKED")."</span>");
    print("</div>");

    $arr = get_forum_access_levels($forumid) or die;
    $maypost = get_user_class() >= $arr["write"];

    if (!$maypost)
        print("<p><i>".T_("FORUMS_YOU_NOT_PERM_POST_FORUM")."</i></p>\n");

    print("<table cellspacing='0' cellpadding='0'><tr>\n");
    if ($maypost)
        print("<td><a href='forums.php?action=newtopic&amp;forumid=$forumid' class='btn btn-primary forum-main-btn'><i class='fa fa-plus'></i> New Post</a></td>\n");
    print("</tr></table>\n");

    insert_quick_jump_menu($forumid);
    end_frame();
    stdfoot();
    die;
}

if ($action == "viewunread") {
    $userid = (int)$CURUSER['id'];
    $maxresults = 25;
    $res = SQL_Query_exec("SELECT id, forumid, subject, lastpost FROM forum_topics ORDER BY lastpost");

    stdhead();
    begin_frame("Topics with unread posts");
    forumheader("New Topics");

    $n = 0;
    $uc = get_user_class();

    while ($arr = mysqli_fetch_assoc($res)) {
        $topicid = (int)$arr['id'];
        $forumid = (int)$arr['forumid'];

        $r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
        $a = mysqli_fetch_row($r);

        if ($a && $a[0] == $arr['lastpost'])
            continue;

        $r = SQL_Query_exec("SELECT name, minclassread, guest_read FROM forum_forums WHERE id=$forumid");
        $a = mysqli_fetch_assoc($r);

        if ($uc < $a['minclassread'] && $a["guest_read"] == "no")
            continue;

        ++$n;
        if ($n > $maxresults) break;

        $forumname = $a['name'];

        if ($n == 1) {
            print("<div class='f-border f-unread'><table width='100%' cellspacing='0'>\n");
            print("<tr class='f-title'><th align='left'>Topic</th><th align='left' colspan='2'>Forum</th></tr>\n");
        }

        print("<tr class='f-row'><td class='f-img' valign='middle'><i class='fa fa-envelope forum-topic-icon'></i></td><td class='alt1'><a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=last#last'><b>" . stripslashes(htmlspecialchars($arr["subject"])) ."</b></a></td><td class='alt2' align='left'><a href='forums.php?action=viewforum&amp;forumid=$forumid'><b>$forumname</b></a></td></tr>\n");
    }

    if ($n > 0) {
        print("</table></div><br />\n");
        if ($n > $maxresults)
            print("<p>More than $maxresults items found, displaying first $maxresults.</p>\n");
        print("<center><a href='forums.php?catchup'><b>Mark All Forums Read.</b></a></center><br />\n");
    } else {
        print("<b>Nothing found</b>");
    }

    end_frame();
    stdfoot();
    die;
}

if ($action == "search") {
    stdhead("Forum Search");
    begin_frame("Search Forum");
    forumheader("Search Forums");

    $keywords = trim($_GET["keywords"] ?? '');

    if ($keywords != "") {
        print("<p>Search Phrase: <b>" . htmlspecialchars($keywords) . "</b></p>\n");
        $maxresults = 50;
        $ekeywords = sqlesc($keywords);

        $res = "SELECT forum_posts.topicid, forum_posts.userid, forum_posts.id, forum_posts.added,
                MATCH ( forum_posts.body ) AGAINST ( ". $ekeywords ." ) AS relevancy
                FROM forum_posts
                WHERE MATCH ( forum_posts.body ) AGAINST ( ". $ekeywords ." IN BOOLEAN MODE )
                ORDER BY relevancy DESC";

        $res = SQL_Query_exec($res);
        $num = mysqli_num_rows($res);

        if ($num > $maxresults) {
            $num = $maxresults;
            print("<p>Found more than $maxresults posts; displaying first $num.</p>\n");
        }

        if ($num == 0) {
            print("<p><b>Sorry, nothing found!</b></p>");
        } else {
            print("<p><center><div class='f-border f-srch_results'><table width='100%' cellspacing='0'>\n");
            print("<tr class='f-title'><th>Post ID</th><th align='left'>Topic</th><th align='left'>Forum</th><th align='left'>Posted by</th></tr>\n");

            for ($i = 0; $i < $num; ++$i) {
                $post = mysqli_fetch_assoc($res);

                $res2 = SQL_Query_exec("SELECT forumid, subject FROM forum_topics WHERE id=$post[topicid]");
                $topic = mysqli_fetch_assoc($res2);

                $res2 = SQL_Query_exec("SELECT name,minclassread, guest_read FROM forum_forums WHERE id=$topic[forumid]");
                $forum = mysqli_fetch_assoc($res2);

                if ($forum["name"] == "" || ($forum["minclassread"] > $CURUSER["class"] && $forum["guest_read"] == "no"))
                    continue;

                $res2 = SQL_Query_exec("SELECT username FROM users WHERE id=$post[userid]");
                $user = mysqli_fetch_assoc($res2);

                if ($user["username"] == "")
                    $user["username"] = "Deluser";

                print("<tr class='f-row'><td>$post[id]</td><td align='left'><a href='forums.php?action=viewtopic&amp;topicid=$post[topicid]#post$post[id]'><b>" . htmlspecialchars($topic["subject"]) . "</b></a></td><td align='left'><a href='forums.php?action=viewforum&amp;forumid=$topic[forumid]'><b>" . htmlspecialchars($forum["name"]) . "</b></a></td><td align='left'><a href='account-details.php?id=$post[userid]'><b>$user[username]</b></a><br />at ".utc_to_tz($post["added"])."</td></tr>\n");
            }

            print("</table></div></center></p>\n");
            print("<p><b>Search again</b></p>\n");
        }
    }

    print("<center><form method='get' action='forums.php'>\n");
    print("<input type='hidden' name='action' value='search' />\n");
    print("<table cellspacing='0' cellpadding='5'>\n");
    print("<tr><td valign='bottom' align='right'>Search For: </td><td align='left'><input type='text' size='40' name='keywords' /><br /></td></tr>\n");
    print("<tr><td colspan='2' align='center'><input type='submit' value='Search' /></td></tr>\n");
    print("</table>\n</form></center>\n");

    end_frame();
    stdfoot();
    die;
}

if ($action != "")
    showerror("Forum Error", "Unknown action '$action'.");

if (isset($_GET["catchup"]))
    catch_up();

$forums_res = SQL_Query_exec("SELECT forumcats.id AS fcid, forumcats.name AS fcname, forum_forums.* FROM forum_forums LEFT JOIN forumcats ON forumcats.id = forum_forums.category ORDER BY forumcats.sort, forum_forums.sort, forum_forums.name");

stdhead("Forums");
begin_frame("Forum Home");
forumheader("Index");
latestforumposts();

print("<div class='f-border f-forums'><table width='100%' cellspacing='0'>");
print("<tr class='f-title'><th align='left' colspan='2'>Forum</th><th width='37' align='right'>Topics</th><th width='47' align='right'>Posts</th><th align='right' width='180'>Last post</th></tr>\n");

if (mysqli_num_rows($forums_res) == 0)
    print("<tr class='f-cat'><td colspan='5' align='center'>No Forum Categories</td></tr>\n");

$fcid = 0;

while ($forums_arr = mysqli_fetch_assoc($forums_res)) {
    if (get_user_class() < $forums_arr["minclassread"] && $forums_arr["guest_read"] == "no")
        continue;

    if ($forums_arr['fcid'] != $fcid) {
        print("<tr class='f-cat'><td colspan='5' align='center'>".htmlspecialchars($forums_arr['fcname'])."</td></tr>\n");
        $fcid = $forums_arr['fcid'];
    }

    $forumid = (int)$forums_arr["id"];
    $forumname = htmlspecialchars($forums_arr["name"]);
    $forumdescription = htmlspecialchars($forums_arr["description"]);

    $postcount = number_format(get_row_count("forum_posts", "WHERE topicid IN (SELECT id FROM forum_topics WHERE forumid=$forumid)"));
    $topiccount = number_format(get_row_count("forum_topics", "WHERE forumid = $forumid"));

    $lastpostid = get_forum_last_post($forumid);

    $post_res = SQL_Query_exec("SELECT added,topicid,userid FROM forum_posts WHERE id=$lastpostid");

    if (mysqli_num_rows($post_res) == 1) {
        $post_arr = mysqli_fetch_assoc($post_res);
        $lastposterid = (int)$post_arr["userid"];
        $lastpostdate = utc_to_tz($post_arr["added"]);
        $lasttopicid = (int)$post_arr["topicid"];

        $user_res = SQL_Query_exec("SELECT username FROM users WHERE id=$lastposterid");
        $user_arr = mysqli_fetch_assoc($user_res);
        $lastposter = htmlspecialchars($user_arr['username']);

        $topic_res = SQL_Query_exec("SELECT subject FROM forum_topics WHERE id=$lasttopicid");
        $topic_arr = mysqli_fetch_assoc($topic_res);
        $lasttopic = stripslashes(htmlspecialchars($topic_arr['subject']));

        $latestleng = 10;
        $lastpost = "<small><a href='forums.php?action=viewtopic&amp;topicid=$lasttopicid&amp;page=last#last'>" . CutName($lasttopic, $latestleng) . "</a> by <a href='account-details.php?id=$lastposterid'>$lastposter</a><br />$lastpostdate</small>";

        if ($CURUSER) {
            $r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$CURUSER[id] AND topicid=$lasttopicid");
            $a = mysqli_fetch_row($r);
        }

        $img = ($a && $a[0] == $lastpostid) ? "fa-folder" : "fa-folder-open";
    } else {
        $lastpost = "<span class='small'>No Posts</span>";
        $img = "fa-folder";
    }

    print("<tr class='f-row'><td class='f-img'><i class='fa $img forum-topic-icon'></i></td><td align='left' width='100%' class='alt1'><a href='forums.php?action=viewforum&amp;forumid=$forumid'><b>$forumname</b></a><br />" .
    "<small>- $forumdescription</small></td><td class='alt2' align='center' width='40'>$topiccount</td><td class='alt3' align='center' width='40'>$postcount</td>" .
    "<td class='alt2' align='right' width='110'><small style='white-space: nowrap'>$lastpost</small></td></tr>\n");
}

print("</table></div>");

print("<table cellspacing='0' cellpadding='3'><tr valign='middle'>\n");
print("<span class='btn btn-sm forum-status-btn forum-status-new'><i class='fa fa-folder-open forum-key-icon'></i>&nbsp; &nbsp; New posts </span>");
print("<span class='btn btn-sm forum-status-btn forum-status-old'><i class='fa fa-folder forum-key-icon'></i>&nbsp; &nbsp;  No New posts </span>");
print("<span class='btn btn-sm forum-status-btn forum-status-locked'><i class='fa fa-lock forum-key-icon'></i>&nbsp; &nbsp;  ".T_("FORUMS_LOCKED")." topic </span>");
print("<span class='btn btn-sm forum-status-btn forum-status-old'><i class='fa fa-thumb-tack forum-key-icon'></i>&nbsp; &nbsp;  ".T_("FORUMS_STICKY")." topic </span>");
print("</tr></table>\n");

$r = SQL_Query_exec("SELECT users.id, users.username, COUNT(forum_posts.userid) as num FROM forum_posts LEFT JOIN users ON users.id = forum_posts.userid GROUP BY userid ORDER BY num DESC LIMIT 10");
forumpostertable($r);

$postcount = number_format(get_row_count("forum_posts"));
$topiccount = number_format(get_row_count("forum_topics"));

print("<br /><center>Our members have made " . $postcount . " posts in  " . $topiccount . " topics</center><br />");

insert_quick_jump_menu();
end_frame();
stdfoot();

}else{
    showerror("Notice", "Unfortunatley the forums are not currently available.");
}

?>
