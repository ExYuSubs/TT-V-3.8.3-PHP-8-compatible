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

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta charset="UTF-8">
	<meta name="description" content="Movies and series with HQ  ExYu Subtitles">
	<meta name="keywords" content="Ex Yu Movies, Ex Yu Series, Ex Yu TV shows, Ex Yu Music">
	<meta name="author" content="ExYu Subtitles in HQ">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $title; ?></title>
	<base href="/" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $site_config["CHARSET"]; ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta name="author" content="Botanicar, TorrentTrader" />
	<meta name="generator" content="TorrentTrader <?php echo $site_config['ttversion']; ?>" />
	<meta name="description" content="TorrentTrader is a feature packed and highly customisable PHP/MySQL Based BitTorrent tracker. Featuring intergrated forums, and plenty of administration options. Please visit www.torrenttrader.org for the support forums. " />
	<meta name="keywords" content="https://exyusubs.com, https://www.torrenttrader.uk" />
<!-- CSS -->
<!-- Theme css -->
	<link rel="shortcut icon" href="<?php echo $site_config["SITEURL"]; ?>/themes/default/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/themes/default/theme.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/themes/default/theme-themable.css" />
<!-- JS -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js" type="text/javascript"></script> 
	<script type="text/javascript" src="<?php echo $site_config["SITEURL"]; ?>/backend/java_klappe.js"></script>
	<script src="https://kit.fontawesome.com/0868fc9509.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class='wrapper'>
      <!-- START HEADER -->
      <div class='header'> <a class='logo' href='index.php' title='Home' target='_self'><img src='<?php echo $site_config["SITEURL"]; ?>/images/tt-logo.png' alt='logo' width='350' height='81' /></a>
        <!-- Start Infobar -->
        <div class='infobar'>
          <?php
                if (!$CURUSER){
                    echo "[<a href=\"account-login.php\">".T_("LOGIN")."</a>]<b> ".T_("OR")." </b>[<a href=\"account-signup.php\">".T_("SIGNUP")."</a>]";
                } else {
                    print (T_("LOGGED_IN_AS").": ".$CURUSER["username"].""); 
                    echo " [<a href=\"account-logout.php\">".T_("LOGOUT")."</a>] ";
                    if ($CURUSER["control_panel"]=="yes") {
                        print("[<a href='admincp.php'>".T_("STAFFCP")."</a>] ");
                    }
            
                    //check for new pm's
                    $res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " and unread='yes' AND location IN ('in','both')");
                    $arr = mysqli_fetch_row($res);
                    $unreadmail = $arr[0];
                    if ($unreadmail){
                        print("[<a href='mailbox.php?inbox'><b><font color='#ff0000'>$unreadmail</font> ".P_("NEWPM", $unreadmail)."</b></a>]");  
                    }else{
                        print("[<a href='mailbox.php'>".T_("YOUR_MESSAGES")."</a>] ");
                    }
                    //end check for pm's
                }
                ?>
 <span style="margin-left: 10px;"> <button class="theme-toggle Tsiz" data-theme-toggle aria-pressed="false" aria-label="Switch theme"> <i class="fa fa-moon" aria-hidden="true"></i> </button>  </span> 
        </div>
        <!-- End Infobar -->
      </div>
      <!-- END HEADER -->
      <!-- START NAVIGATION -->
<?php
if (!$site_config["MEMBERSONLY"] || $CURUSER) {
?>
      <div class='navigation'>
        <div class='menu'>
          <ul id='nav-one' class='dropmenu'>
	    <li></li>
            <li><a href="index.php"><?php echo T_("HOME");?></a></li>
            <li><a href="forums.php"><?php echo T_("FORUMS");?></a></li>
            <li><a href="torrents-upload.php"><?php echo T_("UPLOAD_TORRENT");?></a></li>
            <li><a href="torrents.php"><?php echo T_("BROWSE_TORRENTS");?></a></li>
            <li><a href="torrents-today.php"><?php echo T_("TODAYS_TORRENTS");?></a></li>
            <li><a href="torrents-search.php"><?php echo T_("SEARCH_TORRENTS");?></a></li>
	    <!-- <li><button class="theme-toggle" data-theme-toggle aria-pressed="false" aria-label="Växla tema"><i class="fa fa-moon" aria-hidden="true"></i></button></li>		 -->
          </ul>
        </div>
				<!-- Toggle switcher for dark - light thene -->



				<!-- Toggle switcher for dark - light thene -->
      </div>
<?php
}
?>
      <!-- END NAVIGATION -->
  <!-- Start Content -->
  <div id='main'>
    <table cellspacing="0" cellpadding="7" width="100%" border="0">
     <tr>
          <?php if ($site_config["LEFTNAV"]){?>
          <!-- START LEFT COLUM -->
          <td valign="top" width="170">
		  <?php leftblocks();?>
          </td>
          <!-- END LEFT COLUM -->
          <?php } //LEFTNAV ON/OFF END?>
          <!-- START MAIN COLUM -->
          <td valign="top">
