<?php
include LCY_ROOT . 'inc/func.page.php';
require LCY_ROOT . 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(LCY_ROOT . 'twig');
$twig = new \Twig\Environment($loader, [
    'cache' => LCY_ROOT . 'cache',
    'auto_reload' => true, //根据文件更新时间，自动更新缓存
    'debug' => true,
]);
$twigFile = 'home.twig';

// 业务代码开始 ======>

$page = isset($page) ? intval($page) : 1;
$perPage = isset($perPage) ? intval($perPage) : 10;
$prevPage = $page - 1;
$nextPage = $page + 1;

// 统计数量
$countSql = 'select count(*) as num from cyxw_archive';
$total = 0;
$totalMemCacheKey = getSqlMd5($countSql, []);
if ($onMemCache == false || !($total = $memCache->get($totalMemCacheKey))) {
    $total = $db->single($countSql);
    $onMemCache && $memCache->set($totalMemCacheKey, $total, 0, 86400);
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
$memCacheKey = getSqlMd5($sql, $params);
$list = [];
if ($onMemCache == false || !($list = $memCache->get($memCacheKey))) {
    $list = $db->query($sql, $params);
    $onMemCache && $memCache->set($memCacheKey, $list, 0, 86400);
}

$seo = [
    'title' => '首页',
    'keyword' => '首页',
    'desc' => '首页',
];

$twigData = [
    'global' => $global,
    'seo' => $seo,
    'total' => $total,
    'list' => $list,
    'pages' => $pages,
];

// <=========== 业务代码结束

echo $twig->render($twigFile, $twigData);
$db->CloseConnection();
if ($onMemCache && $memCache) {
    $memCache->close();
}

exit();
