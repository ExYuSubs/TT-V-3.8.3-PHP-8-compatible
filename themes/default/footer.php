<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#       Theme by Nikkbu          #
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
          <td valign="top" width="170">
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
        print ("<br /><a href=\"https://www.torrenttrader.uk\" target=\"_blank\">www.torrenttrader.uk</a> -|- <a href='rss.php'><img src='".$site_config["SITEURL"]."/images/icon_rss.gif' border='0' width='13' height='13' alt='' /></a> -|- <a href='rss.php'>".T_("RSS_FEED")."</a> -|- <a href='rss.php?custom=1'>".T_("FEED_INFO")."</a><br />");
        }
        ?>
      </div>
      <!-- END FOOTER CODE -->
<script src="<?php echo $site_config["SITEURL"]; ?>/js/modal.js"></script>
</div>
				<!-- Toggle switcher for dark - light thene -->

				<script src="<?php echo $site_config["SITEURL"]; ?>/themes/default/js/theme-toggle.js"></script>
				<script src="<?php echo $site_config["SITEURL"]; ?>/themes/default/js/script.js"></script>
				<script src="http://thecodeplayer.com/uploads/js/prefixfree-1.0.7.js" type="text/javascript" type="text/javascript"></script>
				<!-- Toggle switcher for dark - light thene -->
</body>
</html>
<?php ob_end_flush(); ?>
