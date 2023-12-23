<?php
require LCY_ROOT . 'vendor/autoload.php';
require 'api-header.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

try {
    $accessKey = '4ThBgD0iGXL0Ti-1jrDebfqa1WU5PDk5d2vQJorU';
    $secretKey = '2SG--Q6Uzzn2XhgM90VZKMMKQfJrK5knudSxBSj7';
    $auth = new Auth($accessKey, $secretKey);
    $bucket = 'default';
    // 生成上传Token
    $token = $auth->uploadToken($bucket);
    // 构建 UploadManager 对象
    $uploadMgr = new UploadManager();

    if (empty($_FILES['file'])) {
        throw new Exception('文件不能为空');
    }

    $file = $_FILES['file'];
    $filePath = $file['tmp_name'];
    $fileName = $file['name'];

    $nowDate = date('YmdHis');
    $rondomStr = get_random_str(8, 'Aa');
    $ext = explode('.', $fileName);
    $fileExt = $ext[count($ext) - 1];
    $myFile = $nowDate . '_' . $rondomStr;
    $fileNewName = 'upload/' . $myFile . '.' . $fileExt;

    list($ret, $err) = $uploadMgr->putFile($token, $fileNewName, $filePath);

    if ($err) {
        throw new Exception($err);
    } else {
        $return['code'] = 200;
        $return['data'] = [
            'err_msg' => '',
            'imgurl' => 'http://cdn.mmxiaowu.com/' . $ret['key'],
            'smallurl' => '',
            'markurl' => '',
            'resizeimg' => '',
            'fileText' => '',
            'filepath' => 'http://cdn.mmxiaowu.com/' . $ret['key'],
            'oldurl' => $fileName,
        ];
    }

} catch (Exception $e) {
    $return['code'] = 300;
    $return['data'] = null;
    $return['msg'] = $e->getMessage();
}

$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;
