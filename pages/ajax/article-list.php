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
    $memcache_key_total = getSqlMd5($countSql, []);
    if ($onmemcache == false || !($total = $memcache->get($memcache_key_total))) {
        $total = $db->single($countSql);
        $onmemcache && $memcache->set($memcache_key_total, $total, 0, 86400);
    }

    // 列表数据
    $order = ' order by c_id desc';
    $limitLeft = ($page - 1) * $perPage;
    $limit = ' limit ' . $limitLeft . ', ' . $perPage;
    $params = [];
    $sql = 'SELECT * FROM cyxw_archive' . $order . $limit;
    $memcache_key = getSqlMd5($sql, $params);
    $list = [];
    if ($onmemcache == false || !($list = $memcache->get($memcache_key))) {
        $list = $db->query($sql, $params);
        $onmemcache && $memcache->set($memcache_key, $list, 0, 86400);
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
?>