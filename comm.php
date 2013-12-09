<?php
error_reporting(7);

header("Content-type: text/html; charset=utf-8");

require_once 'config.php';
require_once 'Lib/Db.class.php';
require_once 'Lib/Weixin.class.php';
require_once 'Lib/Cb.class.php';
require_once 'Lib/Ord.class.php';
require_once 'Lib/Cmd.class.php';


$db = Db::singleton();



function w($s, $f = 'log.txt')
{
    if (is_object($s) || is_array($s)) {
        $str = print_r($s, true);
    } else {
        $str = $s;
    }

    if ($f != '') {
        file_put_contents($f, $str.PHP_EOL, FILE_APPEND);
    } else {
        echo '<pre>';
        print_r($str);
        echo '</pre>';

    }
}


?>
