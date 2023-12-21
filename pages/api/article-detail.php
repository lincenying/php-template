<?php
include 'api-header.php';
include LCY_ROOT . 'inc/func.ubb.php';

$id = isset($id) ? intval($id) : $routeVars['id'];

$return = [];
$row = null;

try {
    if (empty($id)) {
        throw new Exception('ID不能为空');
    }
    // 详情数据
    $params = [$id];
    $sql = 'SELECT * FROM cyxw_archive where c_id = ?';
    $memCacheKey = get_sql_md5($sql, $params);
    if ($onMemCache == false || !($row = $memCache->get($memCacheKey))) {
        $row = $db->row($sql, $params);
        $onMemCache && $memCache->set($memCacheKey, $row, 0, 86400);
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
