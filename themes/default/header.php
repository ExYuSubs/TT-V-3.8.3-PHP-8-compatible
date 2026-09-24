<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#       Theme: default           #
#       Redesign: navy / cream   #
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
	<meta name="generator" content="TorrentTrader <?php echo $site_config['ttversion']; ?>" />
	<meta name="description" content="TorrentTrader is a feature packed and highly customisable PHP/MySQL Based BitTorrent tracker." />
<!-- Sprijecava "flash" pogresne teme prilikom ucitavanja stranice -->
<script>
(function () {
	try {
		var saved = localStorage.getItem('tt-theme');
		if (saved === 'dark' || saved === 'light') {
			document.documentElement.setAttribute('data-theme', saved);
		}
	} catch (e) {}
})();
</script>
<!-- CSS -->
<!-- Theme css: prvo varijable (boje), pa struktura -->
	<link rel="shortcut icon" href="<?php echo $site_config["SITEURL"]; ?>/themes/default/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/themes/default/theme-themable.css?v=2" />
	<link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/themes/default/theme.css?v=2" />
<!-- JS -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js" type="text/javascript"></script>
	<script type="text/javascript" src="<?php echo $site_config["SITEURL"]; ?>/backend/java_klappe.js"></script>
	<script src="https://kit.fontawesome.com/0868fc9509.js" crossorigin="anonymous"></script>
<!-- Pretty photo files -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prettyPhoto/3.1.6/css/prettyPhoto.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prettyPhoto/3.1.6/js/jquery.prettyPhoto.min.js"></script>
</head>
<body>
    <div class='wrapper'>
      <!-- START HEADER -->
      <div style='display:flex; align-items:center; justify-content:space-between; width:100%;'>
        <a class='logo' href='index.php' title='Home' target='_self'><img src='<?php echo $site_config["SITEURL"]; ?>/images/tt-logo.png' alt='logo' width='350' height='81' /></a>
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
                        print("[<a href='mailbox.php?inbox'><b><span style=\"color: var(--danger);\">$unreadmail</span> ".P_("NEWPM", $unreadmail)."</b></a>]");  
                    }else{
                        print("[<a href='mailbox.php'>".T_("YOUR_MESSAGES")."</a>] ");
                    }
                    //end check for pm's
                }
                ?>

          <button type="button" class="theme-toggle" data-theme-toggle aria-pressed="false" aria-label="Switch theme">
            <i class="fa-solid fa-sun theme-icon-light" aria-hidden="true"></i>
            <i class="fa-solid fa-moon theme-icon-dark" aria-hidden="true"></i>
          </button>
        </div>
      </div>
      <!-- END TOP BAR -->
      <!-- START NAVIGATION -->
<?php
if (!$site_config["MEMBERSONLY"] || $CURUSER) {
?>
      <div class='navigation'>
        <div class='menu'>
          <ul id='nav-one' class='dropmenu'>
            <li><a href="index.php"><?php echo T_("HOME");?></a></li>
            <li><a href="forums.php"><?php echo T_("FORUMS");?></a></li>
            <li><a href="torrents-upload.php"><?php echo T_("UPLOAD_TORRENT");?></a></li>
            <li><a href="torrents.php"><?php echo T_("BROWSE_TORRENTS");?></a></li>
            <li><a href="torrents-today.php"><?php echo T_("TODAYS_TORRENTS");?></a></li>
            <li><a href="torrents-search.php"><?php echo T_("SEARCH_TORRENTS");?></a></li>
          </ul>
        </div>
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
          <td valign="top" width="220">
		  <?php leftblocks();?>
          </td>
          <!-- END LEFT COLUM -->
          <?php } //LEFTNAV ON/OFF END?>
          <!-- START MAIN COLUM -->
          <td valign="top">
