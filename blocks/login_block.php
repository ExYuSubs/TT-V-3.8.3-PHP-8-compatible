<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

if (!function_exists('begin_block')) {
    function begin_block(string $title = ''): void {
        echo "<div class='card mb-3'>";
        if ($title !== '') {
            echo "<div class='card-header text-center'><strong>" . htmlspecialchars($title) . "</strong></div>";
        }
        echo "<div class='card-body text-center'>";
    }
}

if (!function_exists('end_block')) {
    function end_block(): void {
        echo "</div></div>";
    }
}

// =====================================================
// LOGIN BLOCK
// =====================================================
if (empty($CURUSER)) {

    begin_block(T_("LOGIN"));
    ?>
    <form method="post" action="account-login.php">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center">
                    <b><?= T_("USERNAME"); ?>:</b><br>
                    <input type="text" size="15" name="username">
                </td>
            </tr>
            <tr>
                <td align="center">
                    <b><?= T_("PASSWORD"); ?>:</b><br>
                    <input type="password" size="15" name="password">
                </td>
            </tr>
            <tr>
                <td align="center">
                    <input type="submit" value="<?= T_("LOGIN"); ?>">
                </td>
            </tr>
            <tr>
                <td align="center">
                    [<a href="account-signup.php"><?= T_("SIGNUP"); ?></a>]
                    <br>
                    [<a href="account-recover.php"><?= T_("RECOVER_ACCOUNT"); ?></a>]
                </td>
            </tr>
        </table>
    </form>
    <?php
    end_block();

} else {

    // =================================================
    // USER BLOCK
    // =================================================
    $username = htmlspecialchars($CURUSER["username"] ?? "User");

    begin_block($username);

    // Avatar
    $avatar = !empty($CURUSER["avatar"])
        ? htmlspecialchars($CURUSER["avatar"])
        : ($site_config["SITEURL"] . "/images/default_avatar.png");

    // ==============================
    // USER STATS
    // ==============================
    $userdownloaded = mksize((float)($CURUSER["downloaded"] ?? 0));
    $useruploaded   = mksize((float)($CURUSER["uploaded"] ?? 0));
    $privacylevel   = T_($CURUSER["privacy"] ?? '');

    if (($CURUSER["uploaded"] ?? 0) > 0 && ($CURUSER["downloaded"] ?? 0) == 0) {
        $userratio = "Inf.";
    } elseif (($CURUSER["downloaded"] ?? 0) > 0) {
        $userratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 2);
    } else {
        $userratio = "---";
    }

    echo "
        <center>
            <img width='80' height='125' src='{$avatar}' alt='{$username}' class='avatar'>
        </center><br>
        ".T_("DOWNLOADED").": {$userdownloaded}<br>
        ".T_("UPLOADED").": {$useruploaded}<br>
        ".T_("CLASS").": ".T_($CURUSER["level"] ?? '')."<br>
        ".T_("ACCOUNT_PRIVACY_LVL").": {$privacylevel}<br>
        ".T_("RATIO").": {$userratio}
    ";

    // ==============================
    // CONNECTABLE STATUS (SAFE)
    // ==============================
    $connectable    = function_exists('get_row_count')
        ? (int)get_row_count("peers", "WHERE connectable='yes' AND userid=".(int)$CURUSER["id"])
        : 0;

    $unconnectable  = function_exists('get_row_count')
        ? (int)get_row_count("peers", "WHERE connectable='no' AND userid=".(int)$CURUSER["id"])
        : 0;

    if ($unconnectable) {
        echo "<br>".T_('CONNECTABLE')." <b><span style='color:red;'>".T_('CONNECTABLENO')."</span></b>";
    } elseif ($connectable) {
        echo "<br>".T_('CONNECTABLE')." <b><span style='color:green;'>".T_('CONNECTABLEYES')."</span></b>";
    } else {
        echo "<br>".T_('CONNECTABLE')." <b><span style='color:orange;'>".T_('CONNECTABLENA')."</span></b>";
    }
    ?>

    <br><br>
    <center>
        <a href="account.php"><?= T_("ACCOUNT"); ?></a><br>
        <?php if (($CURUSER["control_panel"] ?? '') === "yes") : ?>
            <a href="admincp.php"><?= T_("STAFFCP"); ?></a>
        <?php endif; ?>
    </center>

    <?php
    end_block();
}
?>
