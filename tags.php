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
dbconn(false);

function insert_tag($name, $description, $syntax, $example, $remarks)
{
	$result = format_comment($example);
	print("<div class=\"card tag-card\">\n");
	print("<h3 class=\"tag-name\">$name</h3>\n");
	print("<table class=\"tag-table\">\n");
	print("<tr valign='top'><td width='25%'>".T_("DESCRIPTION").":</td><td>$description</td></tr>\n");
	print("<tr valign='top'><td>".T_("SYNTAX").":</td><td><span class='teletype'>$syntax</span></td></tr>\n");
	print("<tr valign='top'><td>".T_("EXAMPLE").":</td><td><span class='teletype'>$example</span></td></tr>\n");
	print("<tr valign='top'><td>".T_("RESULT").":</td><td>$result</td></tr>\n");
	if ($remarks != "")
		print("<tr><td>".T_("REMARKS").":</td><td>$remarks</td></tr>\n");
	print("</table>\n");
	print("</div>\n");
}

$test = $_POST['test'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<title>BBCode Tags</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script type="text/javascript" src="backend/java_klappe.js"></script>
<style type="text/css">
	:root {
		--bg: #0e1116;
		--panel: #161b22;
		--panel-alt: #1c2230;
		--border: #2a3140;
		--text: #d7dde5;
		--muted: #8b96a5;
		--accent: #4f8cff;
		--radius: 10px;
	}

	* { box-sizing: border-box; }

	html, body {
		margin: 0;
		padding: 0;
		background: var(--bg);
		color: var(--text);
		font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, Arial, sans-serif;
		font-size: 14px;
		line-height: 1.5;
	}

	.wrap {
		max-width: 820px;
		margin: 0 auto;
		padding: 28px 20px 60px;
	}

	.intro {
		color: var(--muted);
		margin-bottom: 18px;
	}

	form.test-form {
		background: var(--panel);
		border: 1px solid var(--border);
		border-radius: var(--radius);
		padding: 16px;
		margin-bottom: 28px;
	}

	form.test-form textarea {
		width: 100%;
		resize: vertical;
		background: var(--panel-alt);
		border: 1px solid var(--border);
		border-radius: 8px;
		color: var(--text);
		font-family: inherit;
		font-size: 13px;
		padding: 10px;
		margin-bottom: 10px;
	}

	form.test-form textarea:focus {
		outline: none;
		border-color: var(--accent);
	}

	form.test-form input[type="submit"] {
		background: var(--accent);
		border: none;
		color: #fff;
		padding: 8px 18px;
		border-radius: 8px;
		font-size: 13px;
		cursor: pointer;
	}

	form.test-form input[type="submit"]:hover {
		filter: brightness(1.08);
	}

	.preview {
		background: var(--panel);
		border: 1px solid var(--border);
		border-radius: var(--radius);
		padding: 16px;
		margin-bottom: 28px;
	}

	.preview .preview-label {
		text-transform: uppercase;
		letter-spacing: .04em;
		font-size: 12px;
		color: var(--muted);
		font-weight: 600;
		margin-bottom: 10px;
		border-bottom: 1px solid var(--border);
		padding-bottom: 8px;
	}

	.card {
		background: var(--panel);
		border: 1px solid var(--border);
		border-radius: var(--radius);
		padding: 14px 16px;
		margin-bottom: 16px;
	}

	.tag-name {
		margin: 0 0 10px;
		font-size: 15px;
		font-weight: 600;
		color: #fff;
	}

	table.tag-table {
		width: 100%;
		border-collapse: collapse;
		font-size: 13px;
	}

	table.tag-table td {
		padding: 7px 10px;
		border: 1px solid var(--border);
		vertical-align: top;
	}

	table.tag-table td:first-child {
		color: var(--muted);
		white-space: nowrap;
		width: 25%;
		background: var(--panel-alt);
	}

	span.teletype {
		font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
		background: var(--panel-alt);
		border: 1px solid var(--border);
		border-radius: 4px;
		padding: 1px 5px;
		font-size: 12px;
	}

	hr {
		border: none;
		border-top: 1px solid var(--border);
		margin: 10px 0;
	}
</style>
</head>
<body>
<div class="wrap">

<p class="intro"><?php echo T_("TAGS_MSG"); ?></p>

<form class="test-form" method=post action=?>
<textarea name="test" cols="60" rows="3"><?php print($test ? htmlspecialchars($test) : ""); ?></textarea>
<input type=submit value="Test this code!" />
</form>
<?php

if ($test != "")
  print("<div class=\"preview\"><div class=\"preview-label\">TEST PREVIEW</div>" . format_comment($test) . "</div>\n");

insert_tag(
	"Bold",
	"Makes the enclosed text bold.",
	"[b]<i>Text</i>[/b]",
	"[b]This is bold text.[/b]",
	""
);

insert_tag(
	"Italic",
	"Makes the enclosed text italic.",
	"[i]<i>Text</i>[/i]",
	"[i]This is italic text.[/i]",
	""
);

insert_tag(
	"Underline",
	"Makes the enclosed text underlined.",
	"[u]<i>Text</i>[/u]",
	"[u]This is underlined text.[/u]",
	""
);

insert_tag(
	"Color (alt. 1)",
	"Changes the color of the enclosed text.",
	"[color=<i>Color</i>]<i>Text</i>[/color]",
	"[color=blue]This is blue text.[/color]",
	"What colors are valid depends on the browser. If you use the basic colors (red, green, blue, yellow, pink etc) you should be safe."
);

insert_tag(
	"Color (alt. 2)",
	"Changes the color of the enclosed text.",
	"[color=#<i>RGB</i>]<i>Text</i>[/color]",
	"[color=#0000ff]This is blue text.[/color]",
	"<i>RGB</i> must be a six digit hexadecimal number."
);

insert_tag(
	"Size",
	"Sets the size of the enclosed text.",
	"[size=<i>n</i>]<i>text</i>[/size]",
	"[size=4]This is size 4.[/size]",
	"<i>n</i> must be an integer in the range 1 (smallest) to 7 (biggest). The default size is 2."
);

insert_tag(
	"Font",
	"Sets the type-face (font) for the enclosed text.",
	"[font=<i>Font</i>]<i>Text</i>[/font]",
	"[font=Impact]Hello world![/font]",
	"You specify alternative fonts by separating them with a comma."
);

insert_tag(
	"Hyperlink (alt. 1)",
	"Inserts a hyperlink.",
	"[url]<i>URL</i>[/url]",
	"[url]".$site_config["SITEURL"]."[/url]",
	"This tag is superfluous; all URLs are automatically hyperlinked."
);

insert_tag(
	"Hyperlink (alt. 2)",
	"Inserts a hyperlink.",
	"[url=<i>URL</i>]<i>Link text</i>[/url]",
	"[url=".$site_config["SITEURL"]."]".$site_config["SITENAME"]."[/url]",
	"You do not have to use this tag unless you want to set the link text; all URLs are automatically hyperlinked."
);

insert_tag(
	"Image (alt. 1)",
	"Inserts a picture.",
	"[img=<i>URL</i>]",
	"[img=".$site_config["SITEURL"]."/images/banner.jpg]",
	"The URL must end with <b>.gif</b>, <b>.jpg</b> or <b>.png</b>."
);

insert_tag(
	"Image (alt. 2)",
	"Inserts a picture.",
	"[img]<i>URL</i>[/img]",
	"[img]".$site_config["SITEURL"]."/images/banner.jpg[/img]",
	"The URL must end with <b>.gif</b>, <b>.jpg</b> or <b>.png</b>."
);

insert_tag(
	"Quote (alt. 1)",
	"Inserts a quote.",
	"[quote]<i>Quoted text</i>[/quote]",
	"[quote]The quick brown fox jumps over the lazy dog.[/quote]",
	""
);

insert_tag(
	"Quote (alt. 2)",
	"Inserts a quote.",
	"[quote=<i>".T_("AUTHOR")."</i>]<i>Quoted text</i>[/quote]",
	"[quote=John Doe]The quick brown fox jumps over the lazy dog.[/quote]",
	""
);

insert_tag(
	"List",
	"Inserts a list item.",
	"[*]<i>Text</i>",
	"[*] This is item 1\n[*] This is item 2",
	""
);

insert_tag(
	"Spoiler (alt. 1)",
	"Inserts a spoiler.",
	"[spoiler]<i>Text</i>[/spoiler]",
	"[spoiler]The quick brown fox jumps over the lazy dog.[/spoiler]",
	""
);

insert_tag(
	"Spoiler (alt. 2)",
	"Inserts a spoiler.",
	"[spoiler=<i>Heading</i>]<i>Text</i>[/spoiler]",
	"[spoiler=Heading]The quick brown fox jumps over the lazy dog.[/spoiler]",
	""
);

?>

</div><!-- /.wrap -->
</body>
</html>