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

$site_config["LEFTNAV"] = false;
$site_config["MIDDLENAV"] = false;
$site_config["RIGHTNAV"]  = false;

$message = '';
$nowarn  = '';

if (!empty($_POST["username"]) && !empty($_POST["password"])) {
    $input_pass = $_POST["password"];

    $res = SQL_Query_exec("SELECT id, password, secret, status, enabled 
                           FROM users 
                           WHERE username = " . sqlesc($_POST["username"]) . " LIMIT 1");
    $row = mysqli_fetch_array($res);

    if (!$row) {
        $message = T_("USERNAME_INCORRECT");
    } elseif ($row["status"] == "pending") {
        $message = T_("ACCOUNT_PENDING");
    } elseif ($row["enabled"] == "no") {
        $message = T_("ACCOUNT_DISABLED");
    } else {
        $stored = $row['password'];
        $password_ok = tt_verify_password($input_pass, $stored);

        if ($password_ok && password_needs_rehash($stored, PASSWORD_ARGON2ID, [
            'memory_cost' => 1<<16,
            'time_cost'   => 4,
            'threads'     => 2,
        ])) {
            $newhash = tt_hash_password($input_pass);
            SQL_Query_exec("UPDATE users SET password = '".addslashes($newhash)."' WHERE id = ".(int)$row['id']);
        }

        if (!$password_ok) {
            $message = T_("PASSWORD_INCORRECT");
        }
    }

    if (empty($message)) {
        $now = get_date_time();
        SQL_Query_exec("INSERT INTO iplog (ip, userid, added, lastused) 
                        VALUES('".getip()."', ".$row['id'].", '$now', '$now') 
                        ON DUPLICATE KEY UPDATE timesused=timesused+1, lastused='$now'");

        $remember = $_POST["remember"] ?? '';
        $pw_res = SQL_Query_exec("SELECT password, secret FROM users WHERE id = ".(int)$row['id']." LIMIT 1");
        $pw_row = mysqli_fetch_assoc($pw_res);

        logincookie($row["id"], $pw_row['password'] ?? $row['password'], $pw_row['secret'] ?? $row['secret'], $remember);

        if (!empty($_POST["returnto"])) {
            header("Location: " . $_POST["returnto"]);
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        show_error_msg(T_("ACCESS_DENIED"), $message, 1);
    }
}

stdhead(T_("LOGIN"));
if ($nowarn) {
    show_error_msg("Error", $nowarn, 0);
}

if($site_config['INVITEONLY']) {
    $signup = "<font size='2' color='red'><b>".T_("CLOSED")."</b></font>"; 
} else { 
    $signup = "<font size='2' color='limegreen'><b>".T_("OPEN")."</b></font>"; 
}

begin_frame(T_("LOGIN"));
?>

<div style="margin-bottom:10px" align="center" class="css-right">
    <font size="2"><b><?php echo T_("FREE_REG_IS"); ?></b>:</font> <a href="account-signup.php"><?php echo $signup; ?></a>
</div>


<form method="post" action="account-login.php">
    <table border="0" cellpadding="5" cellspacing="5" align="center" width="50%">
        <tr>
            <td align="left" class="css"><b><?php echo T_("USERNAME"); ?>:</b></td><td class="css-right"> <input type="text" size="50" name="username" /> </td>
        </tr>
        <tr>
            <td align="left" class="css"><b><?php echo T_("PASSWORD"); ?>:</b></td><td class="css-right"><input type="password" size="50" name="password" /></td>
    </tr>
<!--        <tr>
            <td align="left" class="css"> BOT check:</td><td class="css-right" align="center">
                <div class="cf-turnstile" data-sitekey="<?php echo $site_config['CLOUDSITEKEY']; ?>"></div>
            </td>
        </tr> -->
        <tr>
            <td colspan="2" align="center" class="css">
                <input type="submit" class="button" value="<?php echo T_("LOGIN"); ?>" />
                <br />
                <br />
                <i><?php echo T_("COOKIES");?></i>
            </td>
        </tr>
    </table>

    <?php
    if (!empty($_REQUEST["returnto"])) {
        ?>
        <input type="hidden" name="returnto" value="<?php echo cleanstr($_REQUEST["returnto"]); ?>" />
        <?php
    }
    ?>
</form>
<p align="center">
    <a href="account-signup.php"><?php echo T_("SIGNUP"); ?></a> |
    <a href="account-recover.php"><?php echo T_("RECOVER_ACCOUNT"); ?></a>
</p>

<?php
end_frame();
stdfoot();
?>

<!-- <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script> -->