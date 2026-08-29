<?php

#================================#
#   TorrentTrader - Layout Mgmt  #
#--------------------------------#
#  Drag & drop editor that lets  #
#  class >= LAYOUT_EDITOR_CLASS  #
#  propose a new homepage block  #
#  order. Proposals are stored   #
#  in pending_block_order.json   #
#  and must be approved by a     #
#  class >= LAYOUT_SUPERADMIN_-  #
#  CLASS user before they go     #
#  live (see index.php).         #
#================================#

require_once("backend/functions.php");
require_once("backend/block_registry.php");
dbconn(true);
loggedinonly();

if (!isset($CURUSER["class"]) || $CURUSER["class"] < LAYOUT_EDITOR_CLASS) {
	die("You do not have permission to view this page.");
}

$message = null;
$message_type = null; // 'ok' | 'err'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
	$submitted = explode(',', $_POST['order']);
	$submitted = array_map('trim', $submitted);
	$submitted = array_filter($submitted, function ($v) { return $v !== ''; });

	if (layout_validate_order($submitted)) {
		$savedBy = $CURUSER["username"] ?? ($CURUSER["id"] ?? 'unknown');
		if (save_live_block_order($submitted, $savedBy)) {
			header("Location: index.php?layout=saved");
			exit;
		} else {
			$message = "Kunde inte spara den nya ordningen (skrivrättigheter saknas för filen?).";
			$message_type = 'err';
		}
	} else {
		$message = "Ogiltig blockordning mottagen - inget sparades.";
		$message_type = 'err';
	}
}

$current_order = get_live_block_order();
$registry = $GLOBALS['BLOCK_REGISTRY'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Redigera blocklayout</title>
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
		--ok: #35d07f;
		--err: #ff5c5c;
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
		max-width: 640px;
		margin: 0 auto;
		padding: 28px 20px 60px;
	}

	h1 {
		font-size: 19px;
		color: #fff;
		margin: 0 0 6px;
	}

	.subtitle {
		color: var(--muted);
		font-size: 13px;
		margin-bottom: 20px;
	}

	.notice {
		border-radius: 8px;
		padding: 10px 14px;
		margin-bottom: 18px;
		font-size: 13px;
	}

	.notice.ok { background: rgba(53,208,127,.1); border: 1px solid var(--ok); color: var(--ok); }
	.notice.err { background: rgba(255,92,92,.1); border: 1px solid var(--err); color: var(--err); }
	.notice.info { background: var(--panel-alt); border: 1px solid var(--border); color: var(--muted); }

	#sortable-list {
		list-style: none;
		margin: 0 0 18px;
		padding: 0;
		background: var(--panel);
		border: 1px solid var(--border);
		border-radius: var(--radius);
		overflow: hidden;
	}

	#sortable-list li {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 12px 14px;
		border-bottom: 1px solid var(--border);
		background: var(--panel);
		cursor: grab;
		user-select: none;
	}

	#sortable-list li:last-child {
		border-bottom: none;
	}

	#sortable-list li.dragging {
		opacity: .4;
	}

	#sortable-list li.drag-over {
		border-top: 2px solid var(--accent);
	}

	.handle {
		color: var(--muted);
		font-size: 15px;
		letter-spacing: 2px;
	}

	.block-label {
		font-weight: 500;
	}

	.actions {
		display: flex;
		gap: 10px;
	}

	button.primary {
		background: var(--accent);
		border: none;
		color: #fff;
		padding: 9px 18px;
		border-radius: 8px;
		font-size: 13px;
		cursor: pointer;
	}

	button.primary:hover {
		filter: brightness(1.08);
	}

	a.back {
		color: var(--muted);
		text-decoration: none;
		font-size: 13px;
	}

	a.back:hover {
		color: var(--text);
	}
</style>
</head>
<body>
<div class="wrap">
	<h1>Redigera blocklayout</h1>
	<div class="subtitle">Dra och släpp blocken i den ordning du vill att de ska visas på startsidan. Ändringen sparas direkt och gäller omedelbart för alla medlemmar.</div>

	<?php if ($message): ?>
		<div class="notice <?php echo $message_type === 'ok' ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
	<?php endif; ?>

	<form method="post" action="edit_layout.php" id="layout-form" onsubmit="return confirmSubmit();">
		<ul id="sortable-list">
			<?php foreach ($current_order as $key): ?>
				<?php if (!isset($registry[$key])) continue; ?>
				<li draggable="true" data-key="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
					<span class="handle">&#8942;&#8942;</span>
					<span class="block-label"><?php echo htmlspecialchars($registry[$key]['label'], ENT_QUOTES, 'UTF-8'); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<input type="hidden" name="order" id="order-input" value="" />
		<div class="actions">
			<button type="submit" class="primary">Spara ny ordning</button>
			<a class="back" href="index.php">&larr; Tillbaka till startsidan</a>
		</div>
	</form>
</div>

<script>
(function () {
	var list = document.getElementById('sortable-list');
	var input = document.getElementById('order-input');
	var dragging = null;

	function updateOrderInput() {
		var keys = Array.prototype.map.call(list.children, function (li) {
			return li.getAttribute('data-key');
		});
		input.value = keys.join(',');
	}

	list.addEventListener('dragstart', function (e) {
		dragging = e.target;
		dragging.classList.add('dragging');
	});

	list.addEventListener('dragend', function (e) {
		if (dragging) dragging.classList.remove('dragging');
		Array.prototype.forEach.call(list.children, function (li) {
			li.classList.remove('drag-over');
		});
		dragging = null;
		updateOrderInput();
	});

	list.addEventListener('dragover', function (e) {
		e.preventDefault();
		var target = e.target.closest('li');
		if (!target || target === dragging) return;

		Array.prototype.forEach.call(list.children, function (li) {
			li.classList.remove('drag-over');
		});
		target.classList.add('drag-over');

		var rect = target.getBoundingClientRect();
		var before = (e.clientY - rect.top) < (rect.height / 2);
		list.insertBefore(dragging, before ? target : target.nextSibling);
	});

	updateOrderInput();
})();

function confirmSubmit() {
	document.getElementById('order-input').value =
		Array.prototype.map.call(document.getElementById('sortable-list').children, function (li) {
			return li.getAttribute('data-key');
		}).join(',');
	return confirm('Är du säker på att du vill spara den här ordningen? Den blir synlig för alla medlemmar direkt.');
}
</script>
</body>
</html>
