<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


require_once("functions.php");  

// Array of smilies and their corresponding image files
$smilies = [
  ":senile:"  =>   "to_become_senile.gif",
  ":)"        =>   "smile.gif",
  ":]"        =>   "smiley.gif",
  ":D"        =>   "grin.gif",
  ":lol:"     =>   "lol.gif",
  ":rofl:"    =>   "rofl.gif",
  ":S"        =>   "sarcastic.gif",
  ":w00t:"    =>   "w00t.gif",
  ":-/"       =>   "confused.gif",
  ":|"        =>   "noexpression.gif",
  ":("        =>   "sad.gif",
  ":P"        =>   "tongue.gif",
  ":8)"       =>   "cool.gif",
  ":wave:"    =>   "wave.gif",
  ":ok:"      =>   "ok.gif",
  ":bad:"     =>   "bad.gif",
  ":evil:"    =>   "evil.gif",
  ":rant:"    =>   "rant.gif",
  ":love:"    =>   "love.png",
  ":idea:"    =>   "idea.png",
  ":quest:"   =>   "question.gif",
  ":!:"       =>   "important.png",
  ":fbd:"     =>   "forbidden.png",
  ":warn:"    =>   "warn.png",
  ":dis:"     =>   "disable.gif",
  ":stoned:"  =>   "stoned.gif",
  ":bomb:"    =>   "bomb.png",
  ":+y:"      =>   "afirmative.gif",
  ":-n:"      =>   "negative.gif",
  ":angry:"   =>   "angry.gif",
  ":shit:"    =>   "shit.gif",
  ":weep:"    =>   "weep.gif",
  ":crazy:"   =>   "crazy.gif",
  ":ilv:"     =>   "in-love.gif",
  ":secret:"  =>   "secret.gif",
  ":geek:"    =>   "geek.gif",
  ":yahoo:"   =>   "yahoo.gif",
  ":tease:"   =>   "tease.gif",
  ":moon:"    =>   "mooning.gif",
  ":good:"    =>   "good.gif",
  ":read:"    =>   "read.gif",
  ":scratch:" =>   "scratch.gif",
  ":victory:" =>   "victory.gif",
  ":whistle:" =>   "whistle.gif",
  ":pardon:"  =>   "pardon.gif",
  ":punish:"  =>   "punish.gif",
  ":gamer:"   =>   "gamer.gif",
  ":dance:"   =>   "dance.gif",
  ":mail:"    =>   "mail.gif",
  ":resent:"  =>   "resent.gif",
  ":t-up:"    =>   "thumbsup.gif",
  ":t-down:"  =>   "thumbsdown.gif",
  ":hmm:"     =>   "hmm.gif",
  ":shoot:"   =>   "shooting.gif",
  ":hunter:"  =>   "hunter.gif",
  ":rroule:"  =>   "russian-roulette.gif",
  ":suicid:"  =>   "suicide.gif",
  ":dash:"    =>   "dash.gif",
  ":vip:"     =>   "vip.gif",
  ":bdance:"  =>   "bananadance.gif",
  ":heat:"    =>   "heat.gif",
  ":fishing:" =>   "fishing.gif",
  ":clapp:"   =>   "clapping.gif",
  ":popcorm:" =>   "popcorm.gif",
  ":pepsi:"   =>   "pepsi.gif",
  ":pimp:"    =>   "pimp.gif",
  ":sponge:"  =>   "alcoholic.gif",
  ":drinks:"  =>   "drinks.gif",
  ":friends:" =>   "friends.gif",
  ":happy:"   =>   "happy.gif",
  ":santa:"   =>   "santa.gif",
  ":yard:"    =>   "construction.gif",
  ":helpme:"  =>   "help-me.gif",
  ":hbd:"     =>   "hbd.gif",
  ":party:"   =>   "party.gif",
  ":google:"  =>   "google.gif",
  ":please:"  =>   "please.gif",
  ":sorry:"   =>   "sorry.gif",
  ":oops:"    =>   "oops.gif",
  ":spam:"    =>   "spam.gif",
  ":otp:"     =>   "offtopic.gif",
  ":super:"   =>   "super.gif",
  ":rofl:"    =>   "rofl.gif",
  ":wacko:"   =>   "wacko.gif",
  ":sheep1:"  =>   "sheep1.gif",
  ":newyear:" =>   "newyear.gif",
  ":xmas:"    =>   "xmas.gif",
];


// Function to do smiley stuff
function insert_smilies_frame($site_config, $smilies) {
    echo '<div class="smiley-grid">';

    foreach ($smilies as $code => $url) {
        $form = isset($_GET['form']) ? htmlspecialchars($_GET['form'], ENT_QUOTES, 'UTF-8') : '';
        $text = isset($_GET['text']) ? htmlspecialchars($_GET['text'], ENT_QUOTES, 'UTF-8') : '';

        echo "<button type=\"button\" class=\"smiley-tile\" title=\"$code\" onclick=\"window.opener.SmileIT(" .
             json_encode($code) . ", " . json_encode($form) . ", " . json_encode($text) . ")\">" .
             "<img src=\"{$site_config['SITEURL']}/images/smilies/$url\" alt=\"$code\">" .
             "<span class=\"code\">$code</span></button>";
    }

    echo '</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Smilies</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style type="text/css">
	:root {
		--bg: #0e1116;
		--panel: #161b22;
		--panel-alt: #1c2230;
		--border: #2a3140;
		--text: #d7dde5;
		--muted: #8b96a5;
		--accent: #4f8cff;
	}

	* { box-sizing: border-box; }

	html, body {
		margin: 0;
		padding: 0;
		background: var(--bg);
		color: var(--text);
		font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, Arial, sans-serif;
		font-size: 13px;
	}

	.wrap {
		padding: 18px;
	}

	.header {
		text-align: center;
		margin-bottom: 16px;
	}

	.header h1 {
		font-size: 16px;
		font-weight: 600;
		margin: 0 0 2px;
		color: #fff;
	}

	.header .subtitle {
		color: var(--muted);
		font-size: 12px;
	}

	.smiley-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));
		gap: 8px;
	}

	.smiley-tile {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 6px;
		background: var(--panel);
		border: 1px solid var(--border);
		border-radius: 8px;
		padding: 10px 6px;
		cursor: pointer;
		color: var(--text);
		font-family: inherit;
		transition: border-color .15s ease, background .15s ease, transform .1s ease;
	}

	.smiley-tile:hover {
		border-color: var(--accent);
		background: var(--panel-alt);
		transform: translateY(-1px);
	}

	.smiley-tile img {
		max-width: 28px;
		max-height: 28px;
	}

	.smiley-tile .code {
		font-size: 10px;
		color: var(--muted);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 100%;
	}

	.empty {
		text-align: center;
		color: var(--muted);
		padding: 24px 0;
	}
</style>
</head>
<body>
<div class="wrap">
	<div class="header">
		<h1>Insert a Smiley</h1>
		<div class="subtitle">Click an icon to insert it into your post</div>
	</div>
	<?php
	if (isset($_GET['action']) && $_GET['action'] == "display") {
	    insert_smilies_frame($site_config, $smilies);
	} else {
	    echo '<div class="empty">No smilies to display.</div>';
	}
	?>
</div>
</body>
</html>