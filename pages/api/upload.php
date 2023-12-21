<?php
include 'api-header.php';
require LCY_ROOT . 'inc/func.upload.php';

try {
    if (empty($_FILES['file'])) {
        throw new Exception('文件不能为空');
    }
    $config = [
        'files' => $_FILES['file'],
        'isSmall' => 0,
        'isMark' => 0,
        'waterText' => 'test',
        'isResize' => 0,
        'filepath' => '',
    ];
    $upload = new upload($config);
    $data = $upload->upMore();
    $return = [];
    if ($data['err_msg'] === '') {
        $return['code'] = 200;
        $return['data'] = $data;
    } else {
        throw new Exception($data['err_msg']);
    }

} catch (Exception $e) {
    $return['code'] = 300;
    $return['data'] = null;
    $return['msg'] = $e->getMessage();
}

$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;
