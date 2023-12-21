<?php
$return = [];

try {
    $return['code'] = 200;
    $return['data'] = $_GET;
} catch (Exception $e) {
    $return['code'] = 300;
    $return['data'] = null;
    $return['msg'] = $e->getMessage();
}

header('Content-Type: application/json');
$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;
