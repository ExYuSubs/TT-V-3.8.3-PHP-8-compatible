<?php

#================================#
#   TorrentTrader - Layout Mgmt  #
#--------------------------------#
#  Whitelist + helpers for the   #
#  drag & drop homepage layout   #
#  feature (index.php blocks).   #
#================================#

// ---- Configuration -------------------------------------------------
// Class level that may reorder the homepage blocks (drag & drop UI).
// Changes made by this class take effect immediately for everyone.
define('LAYOUT_EDITOR_CLASS', 7);

// Where the live block order is stored.
define('LAYOUT_LIVE_FILE', __DIR__ . '/../block_order.json');

// ---- Whitelist of blocks --------------------------------------------
// This is the ONLY place block keys are mapped to actual files. The
// order file never contains file paths - only these keys - so a
// corrupted or tampered order file can never cause an arbitrary file
// to be included.
$GLOBALS['BLOCK_REGISTRY'] = [
	'notice'        => ['label' => 'Notice',           'file' => 'blocks/notice_block.php'],
	'news'          => ['label' => 'News',             'file' => 'blocks/news_block.php'],
	'shoutbox'      => ['label' => 'Shoutbox',          'file' => 'blocks/shoutbox_block.php'],
	'last_torrents' => ['label' => 'Latest torrents',   'file' => 'blocks/last_torrents_block.php'],
	'disclamer'     => ['label' => 'Disclaimer',        'file' => 'blocks/disclamer_block.php'],
];

// ---- Helpers ----------------------------------------------------------

function layout_default_order() {
	return array_keys($GLOBALS['BLOCK_REGISTRY']);
}

// An order is only ever valid if it contains EXACTLY the same set of
// keys as the registry, each exactly once. Anything else is rejected.
function layout_validate_order($order) {
	if (!is_array($order)) {
		return false;
	}
	$known = layout_default_order();
	if (count($order) !== count($known)) {
		return false;
	}
	$orderCopy = $order;
	sort($orderCopy);
	sort($known);
	return $orderCopy === $known;
}

function layout_read_json($path) {
	if (!file_exists($path)) {
		return null;
	}
	$raw = @file_get_contents($path);
	if ($raw === false || $raw === '') {
		return null;
	}
	$data = json_decode($raw, true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		return null;
	}
	return $data;
}

function layout_write_json($path, $data) {
	$json = json_encode($data, JSON_PRETTY_PRINT);
	if ($json === false) {
		return false;
	}
	return @file_put_contents($path, $json, LOCK_EX) !== false;
}

// Returns the block order currently shown to all members.
function get_live_block_order() {
	$data = layout_read_json(LAYOUT_LIVE_FILE);
	if (is_array($data) && isset($data['order']) && layout_validate_order($data['order'])) {
		return array_values($data['order']);
	}
	return layout_default_order();
}

// Saves a new order so it takes effect immediately for everyone.
// Returns true on success, false if the order was invalid or the
// file could not be written.
function save_live_block_order($order, $savedBy) {
	if (!layout_validate_order($order)) {
		return false;
	}
	return layout_write_json(LAYOUT_LIVE_FILE, [
		'order'      => array_values($order),
		'saved_by'   => (string) $savedBy,
		'updated_at' => date('c'),
	]);
}
