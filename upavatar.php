<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

require_once ("backend/functions.php");
dbconn();
loggedinonly();
$action = $_REQUEST["action"];
if ($site_config['AVATARUPLOAD']){

if (isset($_FILES['avatar']) && $_FILES['avatar']['name'] !== "") {
    $max_avatar_size = $site_config['avatar_max_filesize'];

    if ($_FILES['avatar']['size'] > $max_avatar_size)
        show_error_msg("Invalid file size!", "Image must be less than " . mksize($site_config['avatar_max_filesize']),1);


    if (preg_match('/^(.+)\.(jpg|gif|png)$/si', $_FILES['avatar']['name'])){
        define("MAX_WIDTH", 80);   // Define width sizes what you want.
        define("MAX_HEIGHT", 80);  // Define height sizes what you want.
		
        $avatar_dir = "".str_replace("\\","/",getcwd())."".$site_config['avatar_dir']."";
        $avatar_url = "".$site_config['SITEURL']."".$site_config['avatar_dir']."";
        $shit = explode('.', $_FILES['avatar']['name']);
        $type = strtolower(end($shit));
		
        if (extension_loaded('gd')){
            $gdinfo = gd_info();
            list($width, $height) = getimagesize($_FILES['avatar']['tmp_name']);
            $scale = min(MAX_WIDTH/$width, MAX_HEIGHT/$height);

        if ($scale < 1) {
            $n_w = floor($scale*$width);
            $n_h = floor($scale*$height);
        }
        else{
            $n_w = $width;
            $n_h = $height;
        }

        $tmp_image = imagecreatetruecolor($n_w, $n_h);

        if ($type == 'png' OR $type == 'gif' OR $type == 'jpg'){
            $avatar = $CURUSER['id'] . "." . $type;
            if (($type == 'jpg' OR $type == 'jpeg') AND ($gdinfo['JPG Support'] OR $gdinfo['JPEG Support']) ){
                $nimage = imagecreatefromjpeg($_FILES['avatar']['tmp_name']);
                imagecopyresampled($tmp_image, $nimage, 0, 0, 0, 0, $n_w, $n_h, $width, $height);
                if (!imagejpeg($tmp_image, $avatar_dir . "/" . $avatar))
                    show_error_msg("Error", "Error, uploading JPG files type. <br>Maybe your GD library not support JPG",1);
        }

        if ($type == 'gif' and $gdinfo['GIF Create Support']){
            if ($scale < 1) {
                $nimage = imagecreatefromgif($_FILES['avatar']['tmp_name']);
                imagecopyresampled($tmp_image, $nimage, 0, 0, 0, 0, $n_w, $n_h, $width, $height);
                if (!imagegif($tmp_image, $avatar_dir . "/" . $avatar))
                    show_error_msg("Error", "Error, uploading GIF files type. <br>Maybe your GD library not support GIF Create",1);
                }
                else{
                    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_dir . "/" . $avatar))
                        show_error_msg("Error", "Error, moving uploaded GIF file.",1);
                    }
        }

        if ($type == 'png' and $gdinfo['PNG Support'] == 1 ){
            $nimage = imagecreatefrompng($_FILES['avatar']['tmp_name']);
            imagecopyresampled($tmp_image, $nimage, 0, 0, 0, 0, $n_w, $n_h, $width, $height);
            if (!imagepng($tmp_image, $avatar_dir . "/" . $avatar))
                show_error_msg("Error", "Error, uploading PNG files type. <br>Maybe your GD library not support PNG",1);
        }
        SQL_Query_exec("UPDATE users SET avatar = '$avatar_url/$avatar' WHERE username = '$CURUSER[username]'");
        }
    }
    else { show_error_msg("PHP - Error", "PHP on your server, not support GD library. <br>Reinstall your PHP with support GD",1); }
    }
    else {
    write_log("<b>HACKING ATTEMPT !!! </b>- User <B>" . $CURUSER["username"] . "</B> try to upload avatar file: <B>" . $_FILES['avatar']["name"] . "</B>");
    show_error_msg("Invalid type", "Is not allowed type file: (" . $_FILES['avatar']["name"] .")",1);
    }
}

stdhead("Upload Avatar");
begin_frame("" . T_("UP_AVATAR") . "");

if ($action == "post") {
    if (empty($_FILES['avatar']['tmp_name'])){
    print ("<br><p><center>" . T_("NO_AVATAR") . "<a href=upavatar.php?action=upload><b>" . T_("UP_AVATAR") . "</b></a></center></p>");
    }
    else{
   print ("<br><p><center>" . T_("AVATAR_OK") . "<a href=account.php?action=edit_settings&do=edit><b>" . T_("ACCOUNT") . "</b></a></center></p>");
   autolink("account.php?action=edit_settings&do=edit", "" . T_("AVATAR_OK") . "");
	}
}



if ($action == "upload") {
    print ("<form method=post action=?action=post enctype=multipart/form-data><table class='table_col1' align=center width=550 border=1 cellpadding=5><tr><td class='table_col2' align=right><b>". T_("AVATAR_FILE") ." :</b></td><td class='table_col1'><input type=file name=avatar size=60></td></tr><tr><td class='table_col2' align=right><b>". T_("DISCLAIMER") ." :</b></td><td class='table_col1'>". T_("AVATAR_TXT") ."".mksize($site_config['avatar_max_filesize'])."</td></tr><tr><td class='table_col1' colspan=2 align=center><input type=submit name=Submit value='".T_("UP_AVATAR")."' class=btn></td></td></tr></table></form>");
	//print ("<form method=post action=?action=post enctype=multipart/form-data><table align=center width=550 border=1 cellpadding=5><tr><td class=table_col1 align=right>". T_("AVATAR_FILE") ." :</td><td class=table_col1><input type=file name=avatar size=60></td></tr><tr><td class=table_col1 align=right>". T_("DISCLAIMER") ." :</td><td class=table_col1>". T_("AVATAR_TXT") ."</td></tr><tr><td class=table_col2 colspan=2 align=center><input type=submit name=Submit value='".T_("UP_AVATAR")."' class=btn></td></td></tr></table></form>");
}

if (!$action == "post" OR !$action == "upload") {
    write_log("<b>HACKING ATTEMPT !!! </b>- User <b>" . $CURUSER["username"] . "</b> tried to direct access to upavatar.php");
    print ("<br><p><center><b>Direct access not allowed !!!</b></p><p>Go to: <a href=account.php?action=edit_settings&do=edit><b>" . T_("ACCOUNT") . "</b></a></center></p><br>");
}
}
else{
    stdhead("ERROR !");
    write_log("<b>HACKING ATTEMPT !!! </b>- User <b>" . $CURUSER["username"] . "</b> tried to upload avatar!");
    show_error_msg ("Error", "<p><font color=red>Avatar uploading disabled by site admin !</font></p>");
}

end_frame();
stdfoot();
?>