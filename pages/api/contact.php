<?php
require 'api-header.php';
require LCY_ROOT . 'inc/func.ubb.php';

$return = [];

try {
    $return['code'] = 200;
    $return['data'] = $_POST;
} catch (Exception $e) {
    $return['code'] = 300;
    $return['data'] = null;
    $return['msg'] = $e->getMessage();
}

$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;
