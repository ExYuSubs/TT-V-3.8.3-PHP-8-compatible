<?php
#================================#
#       TorrentTrader 3.8.3       #
#  http://www.torrenttrader.uk   #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by Botanicar    #
#================================#

begin_frame(T_("LATEST_FORUMS_POSTS"));

if (!$site_config["MEMBERSONLY"] || $CURUSER) {
echo ("<div class='torrent-category-detail clearfix' style='padding-left:2%; padding-right:2%;'><table border='0' class='table table-bordered2' width='100%' cellpadding='3' cellspacing='3'><tr class='b-title'>".
"<th class='table_head' align='left'  width='30'>Icon</th>".
"<th class='table_head' align='left'  width=''>Subject</th>". 
"<th class='table_head' align='center' width='50'>Replay</th>".
"<th class='table_head' align='center' width='50'>Views</th>".
"<th class='table_head' align='center' width='100'>Author</th>".
"<th class='table_head' align='center' width='300'>Last post</th>".
"</tr>");
/// HERE GOES THE QUERY TO RETRIEVE DATA FROM THE DATABASE AND WE START LOOPING ///
$for = SQL_Query_exec("SELECT * FROM forum_topics ORDER BY lastpost DESC LIMIT 3");

while ($topicarr = mysqli_fetch_assoc($for)) {
// Set minclass
$res = SQL_Query_exec("SELECT name,minclassread FROM forum_forums WHERE id=$topicarr[forumid]");
$forum = mysqli_fetch_assoc($res);

//if ($forum["minclassread"] > '0') {
if (($forum["minclassread"]  >= '1') && ($forum["minclassread"]  < '5')) {
$forumname = "<a href=?action=viewforum&amp;forumid=$topicarr[forumid]><b>" . htmlspecialchars($forum["name"]) . "</b></a>";
$topicid = $topicarr["id"];
$topic_title = stripslashes($topicarr["subject"]);
$topic_userid = $topicarr["userid"];
// Topic Views
$views = $topicarr["views"];
// End

/// GETTING TOTAL NUMBER OF POSTS ///
$res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
$arr = mysqli_fetch_row($res);
$posts = $arr[0];
$replies = max(0, $posts - 1);

/// GETTING USERID AND DATE OF LAST POST ///
$res = SQL_Query_exec("SELECT * FROM forum_posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1");
$arr = mysqli_fetch_assoc($res);
$postid = 0 + $arr["id"];
$userid = 0 + $arr["userid"];
$added = "<nobr>" . $arr["added"] . "</nobr>";

/// GETTING THE LAST INFO AND MAKE THE TABLE ROWS ///
$r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
$a = mysqli_fetch_row($r);
$new = !$a || $postid > $a[0];
$char1 = 60; //cut length
$subject = "<a href=forums.php?action=viewtopic&topicid=$topicid><b>" . CutName(htmlspecialchars($topicarr["subject"]), $char1) . "</b></a>";

######################
/// GET NAME OF THE AUTHOR ///
$res = SQL_Query_exec("SELECT id, username, class FROM users WHERE id=$topic_userid");
if (mysqli_num_rows($res) == 1) {
$arr = mysqli_fetch_assoc($res);
$author = "<a href=account-details.php?id=$topic_userid>".$arr['username']."</a>";
}
else
$author = "Unknown[$topic_userid]";

#######################
#######################
/// GETTING THE LAST INFO AND MAKE THE TABLE ROWS ///
$r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
$a = mysqli_fetch_row($r);
$new = !$a || $postid > $a[0];
$latestleng = 10;
$subject = "<a href=forums.php?action=viewtopic&topicid=$topicid&amp;page=last#last> " . stripslashes(encodehtml($topicarr["subject"])) . " </a>";


#######################
#######################
/// GET NAME OF LAST POSTER ///
$res = SQL_Query_exec("SELECT id, username, class FROM users WHERE id=$userid");
if (mysqli_num_rows($res) == 1) {
$arr = mysqli_fetch_assoc($res);
$username = "<a href=account-details.php?id=$userid>".$arr['username']."</a>";
}
else
$username = "Unknown[$topic_userid]";

#######################


echo ("<tr><td class='table_col1' width='6%' align='center'><i class='fa fa-envelope faa-shake animated-hover' style='color: green; font-size: 28px;'></i></td>".
"<td class='table_col1' width='55%'>$subject</td>".
"<td class='table_col1' align='center'><button class='Tsiz'><i class='fa-regular fa-message' style='color: Orange; margin-right: 15px;' aria-hidden='true'></i> $replies </button></td>" .
"<td class='table_col1' align='center'><button class='Tsiz'><i class='fa-regular fa-eye' style='color: Orange; margin-right: 15px;' aria-hidden='true'></i> $views </button></td>" .
"<td class='table_col1' align='center'><button class='Tsiz'><i class='fa-solid fa-user-large' style='color: Orange; margin-right: 15px;' aria-hidden='true'></i> $author </button></td>" .
"<td class='table_col1' align='left' width='250'><small>On &nbsp; <a href='forums.php?action=viewtopic&topicid=$topicid&amp;page=last#last'> " . CutName(htmlspecialchars($topicarr["subject"]),$latestleng) . " </a> &nbsp; from &nbsp; $username <br /><button class='Tsiz'> $added </button></small></td></tr>");

} 
// while
}
echo ("</table></div><br />");
}
end_frame();
?>