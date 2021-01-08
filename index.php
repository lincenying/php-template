<?php
include 'inc/conn.php';

$do = isset($_GET['do']) ? $_GET['do'] : 'index'; //从url中取出do参数，如果没有提供do参数，就设置一个默认的'index'作为参数
$file = 'pages/' . $do . '.php';
if (!file_exists($file)) {
    $file = 'pages/404.php';
}
include $file;

?>