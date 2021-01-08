<?php
require LCY_ROOT . 'inc/func.upload.php';

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
$return['action'] = $action;
$return['data'] = $data;
if ($data['err_msg'] === '') {
    $return['code'] = 200;
} else {
    $return['code'] = 300;
}
$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;
?>