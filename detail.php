<?php
include 'inc/conn.php';
include cyRoom_ROOT . 'inc/func.page.php';
include cyRoom_ROOT . 'inc/func.template.php';

$id = isset($id) ? intval($id) : '';
if (empty($id)) {
    exit('参数错误');
}

// 详情数据
$params = [$id];
$sql = 'SELECT * FROM cyxw_archive where c_id = ?';
$memcache_key = getSqlMd5($sql, $params);
$row = [];
if ($onmemcache == false || !($row = $memcache->get($memcache_key))) {
    $row = $db->row($sql, $params);
    $onmemcache && $memcache->set($memcache_key, $row, 0, 86400);
}

$seo = [
    'title' => $row['c_title'],
    'keyword' => $row['c_title'],
    'desc' => $row['c_title'],
];

$Template = new Template($templateDir, $isRewrite);
include Template::showTemplate('detail');
$output = ob_get_contents();
ob_end_clean();
echo $output;
$db->CloseConnection();
if ($onmemcache && $memcache) {
    $memcache->close();
}

exit();
