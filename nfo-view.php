<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

  if($_SERVER["QUERY_STRING"]) {
//  $nfofilelocation = $_GET["nfofilelocation"];
  $id = $_GET["id"];
    include("backend/nfo2png.php");
    include("backend/config.php");
    $nfo_dir = $site_config["nfo_dir"];
    $nfofilelocation = "$nfo_dir/$id.nfo";
    $f = file($nfofilelocation);
    $f = implode("",$f);
    buildNFO($f, "Powered by ".$site_config['SITENAME']);
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>NFO-Test</title>
</head>
<body bgcolor="blue">
  <img src="nfo-view.php?1">
  <a href="nfo-view.php?1" rel="prettyPhoto" title="This is the description"><img src="nfo-view.php?1" width="60" height="60" alt="This is the title" /></a>
</body>
</html>