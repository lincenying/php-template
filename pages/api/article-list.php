<?php
$page = 1;
$perPage = 10;

$return = [];
try {
    // 列表数据
    $order = ' order by c_id desc';
    $limitLeft = ($page - 1) * $perPage;
    $limit = ' limit ' . $limitLeft . ', ' . $perPage;
    $params = [];
    $sql = 'SELECT * FROM cyxw_archive' . $order . $limit;
    $memCacheKey = getSqlMd5($sql, $params);
    $list = [];
    if ($onMemCache == false || !($list = $memCache->get($memCacheKey))) {
        $list = $db->query($sql, $params);
        $onMemCache && $memCache->set($memCacheKey, $list, 0, 86400);
    }
    $return['code'] = 200;
    $return['data'] = $list;
} catch (Exception $e) {
    $return['code'] = 300;
    $return['data'] = null;
    $return['msg'] = $e->getMessage();
}

$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;