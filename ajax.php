<?php
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods:POST,GET');
// 带 cookie 的跨域访问
header('Access-Control-Allow-Credentials: true');
// 响应头设置
header('Access-Control-Allow-Headers:X-Requested-With,Content-Type,X-CSRF-Token,Authorization');

include 'inc/conn.php';
include cyRoom_ROOT . 'inc/func.ubb.php';
require cyRoom_ROOT . 'inc/func.upload.php';

$action = isset($action) ? $action : '';
if ($action === 'upload') {
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
    $return['code'] = 200;
    $return['action'] = $action;
    $return['data'] = $data;
    $jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
    echo $jsonStr;
} elseif ($action === 'article-list') {
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
} elseif ($action === 'article-detail') {
    $id = isset($id) ? intval($id) : '';

    $return = [];
    $return['action'] = $action;
    $row = null;

    try {
        if (empty($id)) {
            throw new Exception('ID不能为空');
        }
        // 详情数据
        $params = [$id];
        $sql = 'SELECT * FROM cyxw_archive where c_id = ?';
        $memcache_key = getSqlMd5($sql, $params);
        if ($onmemcache == false || !($row = $memcache->get($memcache_key))) {
            $row = $db->row($sql, $params);
            $onmemcache && $memcache->set($memcache_key, $row, 0, 86400);
        }
        if (empty($row)) {
            throw new Exception('没有找到该文章');
        }

        $ubb = new Ubb();
        $ubb->setString($row['c_content']);
        $row['c_content'] = $ubb->parse();

        $return['code'] = 200;
        $return['data'] = $row;
    } catch (Exception $e) {
        $return['code'] = 300;
        $return['data'] = null;
        $return['msg'] = $e->getMessage();
    }

    $jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
    echo $jsonStr;
} else {
    $return = [];
    $return['code'] = 400;
    $return['data'] = null;
    $jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
    echo $jsonStr;
}