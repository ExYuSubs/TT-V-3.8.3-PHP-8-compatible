<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


//Access control
if (php_sapi_name() !== 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    http_response_code(403);
    exit('Access denied.');
}

//Filesize stuff. DO NOT CHANGE THIS SECTION
define('KB', 1024);
define('MB', 1024 * KB);
define('GB', 1024 * MB);

$site_config = [];
// File size limits
$site_config['image_max_filesize'] = 1024 * KB;  // 512 kB (kilobytes). Change as you need
$site_config['avatar_max_filesize'] = 4 * MB;   // 1 MB (megabyte). Change as you need

// Version number and stuff
$site_config['ttversion'] = '3.8.3'; //DONT CHANGE THIS!

// CLOUDFLARE TURNSTILE RECAPTCHA
$site_config['CLOUDSITEKEY'] = 'YOUR_OWN_CLOUDSITE_KEY'; // Cloudflare turnstile captcha Sitekey
$site_config['CLOUDSECRET'] = 'YOUR_OWN_CLOUDSECRET_KEY'; // Cloudflare turnstile captcha Secretkey

// NEW MEMEBER UPLOAD RATIO AND INVITES
$site_config['new_member_upload_ratio'] = 5 * GB; // change the 2 for whatever you want in GB upload
$site_config['new_member_invites'] = 0 ; // Every new user will get 0 invites to start with as a default. Change as you need

// UPLOAD AVATAR
$site_config['AVATARUPLOAD'] = true;      // Enable / Disable upload avatar
$site_config['avatar_dir'] = '/avatars';   // Dir where avatars are stored. chmod 777

$site_config['SITENAME'] = 'TT v 3.8.3 by TT Forum guys - Page';					//Site Name
$site_config['SITEEMAIL'] = 'ttv383@yourpage.com';		//Emails will be sent from this address
$site_config['SITEURL'] = 'https://luigi.eu.org';	//Main Site URL
$site_config['default_language'] = "1";						//DEFAULT LANGUAGE ID
$site_config['default_theme'] = "1";						//DEFAULT THEME ID
$site_config['CHARSET'] = "utf-8";						//Site Charset
$site_config['announce_list'] = "$site_config[SITEURL]/announce.php"; //seperate via comma
$site_config['MEMBERSONLY'] = true;							//MAKE MEMBERS SIGNUP
$site_config['MEMBERSONLY_WAIT'] = false;					//ENABLE WAIT TIMES FOR BAD RATIO
$site_config['ALLOWEXTERNAL'] = false;		//Enable Uploading of external tracked torrents
$site_config['UPLOADERSONLY'] = false;		//Limit uploading to uploader group only
$site_config['INVITEONLY'] = false;			//Only allow signups via invite
$site_config['ENABLEINVITES'] = false;		// Enable invites regardless of INVITEONLY setting
$site_config['CONFIRMEMAIL'] = false;		//Enable / Disable Signup confirmation email
$site_config['ACONFIRM'] = false;			//Enable / Disable ADMIN CONFIRM ACCOUNT SIGNUP
$site_config['ANONYMOUSUPLOAD'] = false;		//Enable / Disable anonymous uploads
$site_config['PASSKEYURL'] =  "$site_config[SITEURL]/announce.php?passkey=%s"; // Announce URL to use for passkey
$site_config['UPLOADSCRAPE'] = true; // Scrape external torrents on upload? If using mega-scrape.php you should disable this
$site_config['FORUMS'] = true; // Enable / Disable Forums
$site_config['FORUMS_GUESTREAD'] = false; // Allow / Disallow Guests To Read Forums
$site_config["OLD_CENSOR"] = false; // Use the old change to word censor set to true otherwise use the new one.   

//==| Minimum class can view Staff members
$site_config["VIEW_STAFF_MEMBERS"] = 5;  	// Write the class number ID

//==| Ratio-Free Tracker
$site_config["ratiofree_enable"] = false;	// Enable/Disable Ratio-Free

$site_config['maxusers'] = 500; // Max # of enabled accounts
$site_config['maxusers_invites'] = $site_config['maxusers'] + 500; // Max # of enabled accounts when inviting

$site_config['currency_symbol'] = '&euro;'; // Currency symbol (HTML allowed)

//AGENT BANS (MUST BE AGENT ID, USE FULL ID FOR SPECIFIC VERSIONS)
$site_config['BANNED_AGENTS'] = "-AZ21, -BC, LIME";

//PATHS, ENSURE THESE ARE CORRECT AND CHMOD TO 777 (ALSO ENSURE TORRENT_DIR/images is CHMOD 777)
$site_config['torrent_dir'] = getcwd().'/uploads';
$site_config['nfo_dir'] = getcwd().'/uploads';
$site_config['blocks_dir'] = getcwd().'/blocks';

// Allowed image types
$site_config['allowed_image_types'] = [
    "image/gif" => ".gif",    // GIF format
    "image/jpeg" => ".jpg",   // Standard JPEG format
    "image/png" => ".png",    // PNG format
    "image/webp" => ".webp"   // Modern WebP format
];

$site_config['SITE_ONLINE'] = true;									//Turn Site on/off
$site_config['OFFLINEMSG'] = 'Site is down for a little while';	

$site_config['WELCOMEPMON'] = true;			//Auto PM New members
$site_config['WELCOMEPMMSG'] = 'Thank you for registering at our tracker! Please remember to keep your ratio at 1.00 or greater :)';

$site_config['SITENOTICEON'] = true;
$site_config['SITENOTICE'] = "<center> Welcome To TorrentTrader v {$site_config['ttversion']} </center>";

$site_config['UPLOADRULES'] = 'You should also include a .nfo file wherever possible<br />Try to make sure your torrents are well-seeded for at least 24 hours<br />Do not re-release material that is still active';

//Setup Site Blocks
$site_config['LEFTNAV'] = true; //Left Column Enable/Disable
$site_config['RIGHTNAV'] = true; // Right Column Enable/Disable
$site_config['MIDDLENAV'] = true; // Middle Column Enable/Disable
$site_config['SHOUTBOX'] = true; //enable/disable shoutbox
$site_config['NEWSON'] = true;
$site_config['DONATEON'] = true;
$site_config['DISCLAIMERON'] = true;

// Class Colors
$site_config["CLASS_USER"] = 'true';                          // Enable class colors in catalog
$site_config['siteowner_color'] = '#0deab7';				// Owner
$site_config['system_color'] = '#c41b1b';				// System
$site_config['coder_color'] = '#0c7699';				// Coder
$site_config['team_leader_color'] = '#FF2000';			// Team Leader
$site_config['administrator_color'] = '#9172EC';		// Administrator
$site_config['super_moderator_color'] = '#9bd4e5';		// Super Moderator
$site_config['moderator_color'] = '#009000';			// Moderator
$site_config['uploader_color'] = '#00A49E';				// Uploader
$site_config['vip_color'] = '#f2ee07';					// VIP
$site_config['elite_user_color'] = '#F68700';			// Elite User
$site_config['power_user_color'] = '#BD8B00';			// Power User
$site_config['user_color'] = '#856A00';					// User

//WAIT TIME VARS
$site_config['WAIT_CLASS'] = '1,2';		//Classes wait time applies to, comma seperated
$site_config['GIGSA'] = '1';			//Minimum gigs
$site_config['RATIOA'] = '0.50';		//Minimum ratio
$site_config['WAITA'] = '24';			//If neither are met, wait time in hours

$site_config['GIGSB'] = '3';			//Minimum gigs
$site_config['RATIOB'] = '0.65';		//Minimum ratio
$site_config['WAITB'] = '12';			//If neither are met, wait time in hours

$site_config['GIGSC'] = '5';			//Minimum gigs
$site_config['RATIOC'] = '0.80';		//Minimum ratio
$site_config['WAITC'] = '6';			//If neither are met, wait time in hours

$site_config['GIGSD'] = '7';			//Minimum gigs
$site_config['RATIOD'] = '0.95';		//Minimum ratio
$site_config['WAITD'] = '2';			//If neither are met, wait time in hours

//CLEANUP AND ANNOUNCE SETTINGS
$site_config['PEERLIMIT'] = '10000';			//LIMIT NUMBER OF PEERS GIVEN IN EACH ANNOUNCE
$site_config['autoclean_interval'] = '600';		//Time between each auto cleanup (Seconds)
$site_config['LOGCLEAN'] = 28 * 86400;			// How often to delete old entries. (Default: 28 days)
$site_config['announce_interval'] = '900';		//Announce Interval (Seconds)
$site_config['signup_timeout'] = '259200';		//Time a user stays as pending before being deleted(Seconds)
$site_config['maxsiteusers'] = '10000';			//Maximum site members
$site_config['max_dead_torrent_time'] = '21600';//Time until torrents that are dead are set invisible (Seconds)

//AUTO RATIO WARNING
$site_config["ratiowarn_enable"] = true; //Enable/Disable auto ratio warning
$site_config["ratiowarn_minratio"] = 0.4; //Min Ratio
$site_config["ratiowarn_mingigs"] = 5;  //Min GB Downloaded
$site_config["ratiowarn_daystowarn"] = 14; //Days to ban

// category = Category Image/Name, name = Torrent Name, dl = Download Link, uploader, comments = # of comments, completed = times completed, size, seeders, leechers, health = seeder/leecher ratio, external, wait = Wait Time (if enabled), rating = Torrent Rating, added = Date Added, nfo = link to nfo (if exists)
$site_config["torrenttable_columns"] = "category,name,added,size,seeders,leechers,completed,comments,external";
// size, speed, added = Date Added, tracker, completed = times completed
$site_config["torrenttable_expand"] = "";

// Caching settings
$site_config["cache_type"] = "disk"; // disk = Save cache to disk, memcache = Use memcache, apc = Use APC, xcache = Use XCache
$site_config["cache_memcache_host"] = "localhost"; // Host memcache is running on
$site_config["cache_memcache_port"] = 11211; // Port memcache is running on
$site_config['cache_dir'] = getcwd().'/cache'; // Cache dir (only used if type is "disk"). Must be CHMOD 777


//Gmail settings
 $site_config["mail_type"] = "pear";
 $site_config["mail_smtp_host"] = "smtp.gmail.com"; // SMTP server hostname
 $site_config["mail_smtp_port"] = "465"; // SMTP server port
 $site_config["mail_smtp_ssl"] = true; // true to use SSL
 $site_config["mail_smtp_auth"] = true; // true to use auth for SMTP
 $site_config["mail_smtp_user"] = ""; // SMTP username/gmail address
 $site_config["mail_smtp_pass"] = ""; // SMTP password


// Password hashing - Once set, cannot be changed without all users needing to reset their passwords
   $site_config["passhash_method"] = "argon2id";
//$site_config["passhash_method"] = "argon2"; // argon2. Modern hashing

// Remove this line after you edited your config.php
die("You didn't edit your config correctly.");
// You MUST remove this line
