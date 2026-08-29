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
 
 $updateset = [];
 
 $stylesheet = $_POST['stylesheet'];
 $language = $_POST['language'];
 
 if (is_valid_id($stylesheet))
     $updateset[] = "stylesheet = '$stylesheet'";
 if (is_valid_id($language))
     $updateset[] = "language = '$language'";

 if (count($updateset))
     SQL_Query_exec("UPDATE `users` SET " . implode(', ', $updateset) . " WHERE `id` = " . $CURUSER["id"]);
 
 if (empty($_SERVER["HTTP_REFERER"]))
 {
     header("Location: index.php"); 
     return;
 }     
 
 header("Location: " . $_SERVER["HTTP_REFERER"]); 
 
?>