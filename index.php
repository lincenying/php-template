<?php
include 'inc/conn.php';
include cyRoom_ROOT . 'inc/func.page.php';
include cyRoom_ROOT . 'inc/func.template.php';

$page = isset($page) ? intval($page) : 1;
$prevPage = $page - 1;
$nextPage = $page + 1;
$perPage = 10;

// 统计数量
$countSql = 'select count(*) as num from cyxw_archive';
$total = 0;
$memcache_key_total = getSqlMd5($countSql, []);
if ($onmemcache == false || !($total = $memcache->get($memcache_key_total))) {
    $total = $db->single($countSql);
    $onmemcache && $memcache->set($memcache_key_total, $total, 0, 86400);
}

// 分页设置
$classPage = new page(['total' => $total, 'nowindex' => $page, 'perpage' => $perPage, 'pagename' => 'page', 'url' => '', 'rewrite' => false]);
$limitLeft = $classPage->offset;
$pages = $classPage->show(2);

// 列表数据
$order = ' order by c_id desc';
$limit = ' limit ' . $limitLeft . ', ' . $perPage;
$params = [];
$sql = 'SELECT * FROM cyxw_archive' . $order . $limit;
$memcache_key = getSqlMd5($sql, $params);
$list = [];
if ($onmemcache == false || !($list = $memcache->get($memcache_key))) {
    $list = $db->query($sql, $params);
    $onmemcache && $memcache->set($memcache_key, $list, 0, 86400);
}

$seo = [
    'title' => '首页',
    'keyword' => '首页',
    'desc' => '首页',
];
$Template = new Template($templateDir, $isRewrite);
include Template::showTemplate('index');
$output = ob_get_contents();
ob_end_clean();
echo $output;
$db->CloseConnection();
if ($onmemcache && $memcache) {
    $memcache->close();
}

exit();