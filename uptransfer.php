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
dbconn();
loggedinonly();
$action = $_REQUEST["action"];

if ($action == "searchuser") {
    $query = $_GET['query'];

    $stmt = $GLOBALS["DBconnector"]->prepare("SELECT username FROM users WHERE username LIKE ?");
    $searchTerm = $query . "%"; // match starting characters
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $usernames = [];
    while ($row = $result->fetch_assoc()) {
        $usernames[] = $row['username'];
    }

    echo json_encode($usernames);
    die();
}


if ($action == "taketransfer") {
    $username = trim($_POST["username"]);
    $credit = (int)$_POST["credit"];
    $unit = trim($_POST["unit"]);
    $anonym = isset($_POST["anonym"]) ? $_POST["anonym"] : '';

    if (empty($username)) {
        autolink("uptransfer.php", "No recipient was added...");
    }

    if ($credit < 1 || !$CURUSER) {
        autolink("uptransfer.php", "Transfer is too small...");
    }

    // Define size units
    define('KB', 1024);
    define('MB', 1024 * KB);
    define('GB', 1024 * MB);
    define('TB', 1024 * GB);

    // Convert credit to bytes
    switch ($unit) {
        case 'kb':
            $credit *= KB;
            break;
        case 'mb':
            $credit *= MB;
            break;
        case 'gb':
            $credit *= GB;
            break;
        case 'tb':
            $credit *= TB;
            break;
        default:
            autolink("uptransfer.php", "Invalid unit specified...");
    }

    if ($CURUSER["uploaded"] < $credit) {
        autolink("uptransfer.php", "You are trying to donate upload credits while you currently have insufficient credits...");
    }

    $stmt = $GLOBALS["DBconnector"]->prepare("SELECT id, uploaded, modcomment FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res) {
        autolink("uptransfer.php", T_("USER_NOT_FOUND"));
    }

    $receiver = $res["id"];
    $sender = $CURUSER["id"];
    $modcomment1 = $res["modcomment"];
    $modcomment2 = $CURUSER["modcomment"];

    // Prepare modcomments
    $date = gmdate("Y-m-d");
    $modcomment1 = "$date - Credit upload: $username obtained " . mksize($credit) . " from " . $CURUSER['username'] . ".\n" . $modcomment1;
    $modcomment2 = "$date - Credit upload: " . $CURUSER['username'] . " donated " . mksize($credit) . " to $username.\n" . $modcomment2;

    // Update receiver's uploaded credits and modcomment
    $stmt = $GLOBALS["DBconnector"]->prepare("UPDATE users SET uploaded = uploaded + ?, modcomment = ? WHERE id = ?");
    $stmt->bind_param("isi", $credit, $modcomment1, $receiver);
    $stmt->execute();

    // Update sender's uploaded credits and modcomment
    $stmt = $GLOBALS["DBconnector"]->prepare("UPDATE users SET uploaded = uploaded - ?, modcomment = ? WHERE id = ?");
    $stmt->bind_param("isi", $credit, $modcomment2, $sender);
    $stmt->execute();

    // Send notification
    $subject = "You got an upload credit!";
    if ($anonym != 'anonym') {
        $msg = "Your account has been credited " . mksize($credit) . " compliments of " . $CURUSER['username'] . ".";
        $stmt = $GLOBALS["DBconnector"]->prepare("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES (?, ?, NOW(), ?, 0, ?)");
        $stmt->bind_param("iiss", $sender, $receiver, $msg, $subject);
    } else {
        $msg = "Your account has been credited " . mksize($credit) . " compliments of an anonymous user.";
        $stmt = $GLOBALS["DBconnector"]->prepare("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES (0, ?, NOW(), ?, 0, ?)");
        $stmt->bind_param("iss", $receiver, $msg, $subject);
    }
    $stmt->execute();

    // Log the transfer
    write_log("<font color=lime size=2>Credit Upload:</font><a href=account-details.php?id=$sender target=_blank>" . $CURUSER['username'] . "</a> donated " . mksize($credit) . " to <a href=account-details.php?id=$receiver target=_blank>$username</a>");

    autolink("uptransfer.php", "You have given " . mksize($credit) . " in upload credit to " . $username);
    die();
}

/* Start Page Output */
stdhead("Donate Uploads");

begin_frame(T_('Q&A'));
?>
<b><?php echo T_("WHY_SHOULD_DONATE"); ?></b><br />
<?php echo T_("CREDIT_UP_TEXT1"); ?>
<?php
end_frame();

begin_frame(T_('TRANSFER_UPLOAD'));
?>
<center>
    <form name="transfer" method="post" action="uptransfer.php?action=taketransfer">
        <table width="500" cellpadding="5">
            <tr>
                <td width="100"><b><?php echo T_("DONATE_TO"); ?>:</b></td>
                <td>
                    <input type="text" id="username" name="username" size="20" onkeyup="searchUser(this.value)" autocomplete="off">
                    <input type="checkbox" name="anonym" value="anonym"> <?php echo T_("ANONYMOUS_TRANSFER"); ?>
                    <div id="suggestions" style="border: 1px solid #ccc; display: none; max-height: 100px; overflow-y: auto;"></div>
                </td>
            </tr>
            <tr>
                <td width="100"><b><?php echo T_("AMOUNT_CREDIT"); ?>:</b></td>
                <td>
                    <input type="text" autocomplete="off" name="credit" size="20" value="1" pattern="\d*" title="Only numbers are allowed.">
                    <select name="unit">
                        <option value="kb">KB</option>
                        <option value="mb">MB</option>
                        <option value="gb">GB</option>
                        <option value="tb">TB</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <center><input name="submit" type="submit" value="Transfer!"></center>
                </td>
            </tr>
        </table>
    </form>
</center>
<?php
end_frame();
stdfoot();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function searchUser(query) {
    if (query.length > 0) { // Adjusted to start with the first character
        $.ajax({
            url: 'uptransfer.php?action=searchuser',
            type: 'GET',
            data: { query: query },
            success: function(data) {
                var suggestions = JSON.parse(data);
                var suggestionsList = '';
                if (suggestions.length > 0) {
                    suggestions.forEach(function(username) {
                        suggestionsList += '<div class="suggestion-item" onclick="selectUsername(\'' + username + '\')">' + username + '</div>';
                    });
                } else {
                    suggestionsList = '<div class="suggestion-item">No suggestions found</div>';
                }
                $('#suggestions').html(suggestionsList).show();
            }
        });
    } else {
        $('#suggestions').hide();
    }
}

function selectUsername(username) {
    $('#username').val(username);
    $('#suggestions').hide();
}
</script>

<style>
.suggestion-item {
    padding: 10px;
    cursor: pointer;
}

.suggestion-item:hover {
    background-color: #f0f0f0;
}
</style>
