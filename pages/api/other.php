<?php
$return = [];
$return['code'] = 400;
$return['data'] = null;
$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);

header('Content-Type: application/json');
echo $jsonStr;
