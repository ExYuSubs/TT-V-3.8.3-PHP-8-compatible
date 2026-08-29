<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

if (($site_config["INVITEONLY"] || $site_config["ENABLEINVITES"]) && isset($CURUSER) && isset($CURUSER["invites"])) {
    $invites = $CURUSER["invites"];
    begin_block(T_("INVITES"));
    ?>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
        <?php printf(P_("YOU_HAVE_INVITES", $invites), $invites); ?>
        </td>
    </tr>
    <?php if ($invites > 0) { ?>
    <tr>
        <td align="center">
        <a href="invite.php"><?php echo T_("SEND_AN_INVITE"); ?></a>
        </td>
    </tr>
    <?php } ?>
    </table>
    <?php
    end_block();
}
?>