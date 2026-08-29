<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


function SQL_Query($query) {
    return new SQL_Query($query);
}

function SQL_Query_exec(string $query) {
    try {
        $sql = new SQL_Query($query);
        return $sql->execute();
    } catch (Exception $e) {
        error_log("SQL Query Execution Error: " . $e->getMessage());
        return null; // връщаме null при грешка
    }
}

class SQL_Query {
    private $query = "";
    private $params = [];

    function __construct($query) {
        $this->query = $query;
    }

    function p($param) {
        if (is_numeric($param)) {
            $this->params[] = $param;
        } elseif (is_array($param)) {
            $this->params[] = implode(", ", array_map([$this, "escape"], $param));
        } else {
            $this->params[] = $this->escape($param);
        }
        return $this;
    }

    function p_name($param) {
        $this->params[] = "`".mysqli_real_escape_string($GLOBALS["DBconnector"], $param)."`";
        return $this;
    }

    function escape($s) {
        if (is_numeric($s)) return $s;
        return "'".mysqli_real_escape_string($GLOBALS["DBconnector"], $s)."'";
    }

    function read() {
        $ret = "";
        if (count($this->params)) {
            reset($this->params);
            for ($i = 0; $i < strlen($this->query); $i++) {
                if ($this->query[$i] == "?") {
                    $val = current($this->params);
                    next($this->params);
                    $ret .= $val;
                } else {
                    $ret .= $this->query[$i];
                }
            }
            reset($this->params);
        } else {
            $ret = $this->query;
        }
        return $ret;
    }

    function execute() {
        $query = $this->read();
        $res = mysqli_query($GLOBALS["DBconnector"], $query);

        if ($res || mysqli_errno($GLOBALS["DBconnector"]) == 1062) {
            return $res;
        }

        $mysqli_error = mysqli_error($GLOBALS["DBconnector"]);
        $mysqli_errno = mysqli_errno($GLOBALS["DBconnector"]);

        if (function_exists("debug_backtrace")) {
            $bt = debug_backtrace();
            $i = 1;
            if (in_array($bt[$i]["function"], ["SQL_Query_exec_cached", "get_row_count_cached", "get_row_count"])) {
                $i++;
            }
            $line = $bt[$i]["line"];
            $file = str_replace(getcwd().DIRECTORY_SEPARATOR, "", $bt[$i]["file"]);
            $msg = "Database Error in $file on line $line: $mysqli_error. Query was: $query.";
        } else {
            $file = str_replace(getcwd().DIRECTORY_SEPARATOR, "", $_SERVER["SCRIPT_FILENAME"]);
            $msg = "Database Error in $file: $mysqli_error. Query was: $query";
        }

        mysqli_query(
            $GLOBALS["DBconnector"],
            "INSERT INTO `sqlerr` (`txt`, `time`) VALUES (".sqlesc($msg).", '".get_date_time()."')"
        );

        show_error_msg("Database Error", "Database Error. Please report this to an administrator.", 1);
    }
}

/* Example Usage:

$ids = range(1, 10);
$q = SQL_Query("SELECT `id`, `username` FROM `users` WHERE ? IN (?) ORDER BY ? ASC")
        ->p_name("id")->p($ids)->p_name("id");

echo "Query: ".$q->read()."\n";
$res = $q->execute();

while ($row = mysqli_fetch_array($res)) {
    echo "$row[id] - $row[username]\n";
}

*/
?>