<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#       Theme: default       #
#       Redesign: navy / cream   #
#================================#

function_exists('T_') or die;

			if ($site_config["MIDDLENAV"]){
				middleblocks();
			} //MIDDLENAV ON/OFF END
			?>
          </td>
          <!-- END MAIN COLUM -->
          <?php if ($site_config["RIGHTNAV"]){ ?>
          <!-- START RIGHT COLUMN -->
          <td valign="top" width="220">
		  <?php rightblocks(); ?>
          </td>
          <!-- END RIGHT COLUMN -->
          <?php } ?>
        </tr>
    </table>
  </div>
<!-- End Content -->
      <!-- START FOOTER CODE -->
      <div class='credits'>
        <?php
        printf (T_("POWERED_BY_TT")."", $site_config["ttversion"]);
        if (!$site_config["MEMBERSONLY"] || $CURUSER) {
        print ("<br /><a href=\"https://www.torrenttrader.uk\" target=\"_blank\">www.torrenttrader.uk</a> -|- <a href='rss.php'>".T_("RSS_FEED")."</a> -|- <a href='rss.php?custom=1'>".T_("FEED_INFO")."</a><br />");
        }
        ?>
      </div>
      <!-- END FOOTER CODE -->
</div>
<!-- JS -->
<script src="<?php echo $site_config["SITEURL"]; ?>/js/modal.js"></script>
<script src="<?php echo $site_config["SITEURL"]; ?>/themes/default/js/toggle.js?v=2"></script>
<script src="<?php echo $site_config["SITEURL"]; ?>/themes/default/js/script.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
