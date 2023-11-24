<?php
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: application/json');
// 允许跨域的域名
if (strpos($origin, 'mmxiaowu.com') !== false) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods:OPTIONS,POST,GET,PUT');
// 带 cookie 的跨域访问
header('Access-Control-Allow-Credentials: true');
// 响应头设置
header('Access-Control-Allow-Headers:X-Requested-With,Content-Type,X-CSRF-Token,Authorization');

include 'inc/conn.php';

$action = isset($action) ? $action : 'other';
$action = str_replace("/", "-", $action);

$file = 'pages/api/' . $action . '.php';

if (!file_exists($file)) {
    $file = 'pages/api/other.php';
}

include $file;
