<?php
header("Content-type: text/html; charset=utf-8");
require_once 'config.db.php';
require_once 'db.class.php';
$db = new db();
//删除一周内的登陆操作日志
$start_time = strtotime("-1week");
$res = $db->table("log")->where("t<'{$start_time}'")->delete();
var_dump($res);

?>