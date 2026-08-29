<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#

class BEncode {
    public function encode($data) {
        if (is_int($data)) {
            return 'i' . $data . 'e';
        } elseif (is_string($data)) {
            return strlen($data) . ':' . $data;
        } elseif (is_array($data)) {
            if ($this->isAssoc($data)) {
                ksort($data, SORT_STRING);
                $out = 'd';
                foreach ($data as $key => $value) {
                    $out .= $this->encode((string)$key);
                    $out .= $this->encode($value);
                }
                $out .= 'e';
                return $out;
            } else {
                $out = 'l';
                foreach ($data as $value) {
                    $out .= $this->encode($value);
                }
                $out .= 'e';
                return $out;
            }
        }
        return ''; // unsupported type
    }

    private function isAssoc($arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

function BEncode($data) {
    $encoder = new BEncode;
    return $encoder->encode($data);
}
?>