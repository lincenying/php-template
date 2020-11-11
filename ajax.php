<?php
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type:text/html;charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods:POST,GET');
// 带 cookie 的跨域访问
header('Access-Control-Allow-Credentials: true');
// 响应头设置
header('Access-Control-Allow-Headers:X-Requested-With,Content-Type,X-CSRF-Token,Authorization');

include 'inc/conn.php';
require cyRoom_ROOT . 'inc/func.upload.php';

$action = isset($action) ? $action : '';
if ($action === 'upload') {
    $config = [
        'files' => $_FILES['file'],
        'isSmall' => 0,
        'isMark' => 0,
        'waterText' => 'test',
        'isResize' => 0,
    ];
    $upload = new upload($config);
    $data = $upload->upMore();
    $return = [];
    $return['code'] = 200;
    $return['action'] = $action;
    $return['data'] = $data;
    $jsonStr = json_encode($return);
    echo $jsonStr;
}
