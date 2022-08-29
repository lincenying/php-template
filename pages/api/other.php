<?php
$return = [];
$return['code'] = 400;
$return['data'] = null;
$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;
?>