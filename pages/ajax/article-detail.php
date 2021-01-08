<?php
include LCY_ROOT . 'inc/func.ubb.php';

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
?>