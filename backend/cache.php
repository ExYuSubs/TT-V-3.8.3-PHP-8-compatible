<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


$GLOBALS["TTCache"] = new TTCache();

class TTCache {
    private $cachedir;
    private $type;
    private $obj;

    public function __construct() {
        global $site_config;
        $this->cachedir = $site_config["cache_dir"] ?? __DIR__ . '/cache';
        $this->type = strtolower(trim($site_config["cache_type"] ?? 'disk'));

        switch ($this->type) {
            case "memcache":
                if (class_exists("Memcache")) {
                    $this->obj = new Memcache();
                    if (!@$this->obj->connect($site_config["cache_memcache_host"] ?? '127.0.0.1', $site_config["cache_memcache_port"] ?? 11211)) {
                        $this->type = "disk";
                    }
                } else {
                    $this->type = "disk";
                }
                break;
            case "apc":
                if (!function_exists("apcu_store")) {
                    $this->type = "disk";
                }
                break;
            case "xcache":
                if (!function_exists("xcache_set")) {
                    $this->type = "disk";
                }
                break;
            default:
                $this->type = "disk";
        }
    }

    public function set(string $var, mixed $val, int $expire = 0): bool {
        if ($expire === 0) {
            return false;
        }

        global $site_config;

        switch ($this->type) {
            case "memcache":
                return $this->obj->set($site_config['SITENAME'] . "_" . $var, $val, 0, $expire);
            case "apc":
                return apcu_store($var, $val, $expire);
            case "disk":
                $cacheFile = $this->cachedir . "/$var.cache";
                file_put_contents($cacheFile, serialize($val));
                return true;
            case "xcache":
                return xcache_set($var, serialize($val), $expire);
            default:
                return false;
        }
    }

    public function delete(string $var): bool {
        global $site_config;

        switch ($this->type) {
            case "memcache":
                return $this->obj->delete($site_config['SITENAME'] . "_" . $var);
            case "apc":
                return apcu_delete($var);
            case "disk":
                $cacheFile = $this->cachedir . "/$var.cache";
                return file_exists($cacheFile) && unlink($cacheFile);
            case "xcache":
                return xcache_unset($var);
            default:
                return false;
        }
    }

    public function get(string $var, int $expire = 0): mixed {
        if ($expire === 0) {
            return false;
        }

        global $site_config;

        switch ($this->type) {
            case "memcache":
                return $this->obj->get($site_config['SITENAME'] . "_" . $var);
            case "apc":
                return apcu_fetch($var);
            case "disk":
                $cacheFile = $this->cachedir . "/$var.cache";
                if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $expire) {
                    return unserialize(file_get_contents($cacheFile));
                }
                return false;
            case "xcache":
                if (xcache_isset($var)) {
                    return unserialize(xcache_get($var));
                }
                return false;
            default:
                return false;
        }
    }
}

// Cached MySQL Functions
function get_row_count_cached(string $table, string $suffix = ""): int {
    global $TTCache;

    $query = "SELECT COUNT(*) FROM $table $suffix";
    $cache = "get_row_count/" . sha1($query);
    $ret = $TTCache->get($cache, 300);

    if ($ret === false) {
        $res = SQL_Query_exec($query);
        $row = mysqli_fetch_row($res);
        $ret = (int)$row[0];
        $TTCache->set($cache, $ret, 300);
    }
    return $ret;
}

	//function SQL_Query_exec_cached(string $query, int $cache_time = 300, bool $cache_blank = true): array|bool {
	function SQL_Query_exec_cached(string $query, int $cache_time = 300, bool $cache_blank = true) {
    global $TTCache;

    $cache = "queries/" . sha1($query);
    $rows = $TTCache->get($cache, $cache_time);

    if ($rows === false) {
        $res = SQL_Query_exec($query);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        if (!empty($rows) || $cache_blank) {
            $TTCache->set($cache, $rows, $cache_time);
        }
    }
    return !empty($rows) ? $rows : false;
}
