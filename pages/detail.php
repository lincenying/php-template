<?php
include LCY_ROOT . 'inc/func.ubb.php';
require LCY_ROOT . 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(LCY_ROOT . 'twig');
$twig = new \Twig\Environment($loader, [
    'cache' => LCY_ROOT . 'cache',
    'auto_reload' => true, //根据文件更新时间，自动更新缓存
    'debug' => true,
]);
$twigFile = 'detail.twig';

// 业务代码开始 ======>

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

$ubb = new Ubb();
$ubb->setString($row['c_content']);
$row['c_content'] = $ubb->parse();

$seo = [
    'title' => $row['c_title'],
    'keyword' => $row['c_title'],
    'desc' => $row['c_title'],
];

$twigData = [
    'global' => $global,
    'seo' => $seo,
    'row' => $row,
];

// <=========== 业务代码结束

echo $twig->render($twigFile, $twigData);

$db->CloseConnection();
if ($onmemcache && $memcache) {
    $memcache->close();
}

exit();