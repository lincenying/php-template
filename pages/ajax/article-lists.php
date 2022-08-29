<?php
$page = isset($page) ? intval($page) : 1;
$perPage = isset($perPage) ? intval($perPage) : 10;
$prevPage = $page - 1;
$nextPage = $page + 1;

$return = [];
try {
    // 统计数量
    $countSql = 'select count(*) as num from cyxw_archive';
    $total = 0;
    $totalMemCacheKey = getSqlMd5($countSql, []);
    if ($onMemCache == false || !($total = $memCache->get($totalMemCacheKey))) {
        $total = $db->single($countSql);
        $onMemCache && $memCache->set($totalMemCacheKey, $total, 0, 86400);
    }

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
    $return['action'] = $action;
    $return['data'] = [];
    $return['data']['total'] = $total;
    $return['data']['per_page'] = $perPage;
    $return['data']['current_page'] = $page;
    $return['data']['last_page'] = ceil($total / $perPage);
    $return['data']['data'] = $list;
} catch (Exception $e) {
    $return['code'] = 300;
    $return['data'] = null;
    $return['msg'] = $e->getMessage();
}

$jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
echo $jsonStr;
