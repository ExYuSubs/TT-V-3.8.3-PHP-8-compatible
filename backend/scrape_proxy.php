<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

function scrape_via_proxy(string $info_hash): ?array {
    $proxy = "http://127.0.0.1:9009/scrape?hash=" . urlencode($info_hash);

    $ch = curl_init($proxy);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    if (!$res) return null;

    $json = json_decode($res, true);
    if (!is_array($json) || !isset($json['seeders'])) return null;

    return $json;
}