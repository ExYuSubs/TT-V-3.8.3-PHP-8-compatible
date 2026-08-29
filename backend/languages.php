<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


function T_($s) {
    GLOBAL $LANG;

    if (isset($LANG[$s]) && $LANG[$s]) {
        // return "TRANSLATED";
        return $LANG[$s];
    }

    if (isset($LANG["{$s}[0]"]) && $LANG["{$s}[0]"]) {
        // return "TRANSLATED";
        return $LANG["{$s}[0]"];
    }

    return $s;
}

function P_($s, $num) {
    GLOBAL $LANG;

    $num = (int) $num;

    $plural = str_replace("n", $num, $LANG["PLURAL_FORMS"]);
    $i = eval("return intval($plural);");

    if (isset($LANG["{$s}[$i]"]) && $LANG["{$s}[$i]"]) {
        // return "TRANSLATED";
        return $LANG["{$s}[$i]"];
    }

    return $s;
}

?>