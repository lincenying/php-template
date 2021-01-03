<?php
include 'inc/conn.php';
include cyRoom_ROOT . 'inc/func.ubb.php';
require cyRoom_ROOT . 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/twig');
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ . '/cache',
    'auto_reload' => true, //根据文件更新时间，自动更新缓存
    'debug' => true,
]);
$twigFile = 'company.twig';

// 业务代码开始 ======>

$post = $_POST;

$seo = [
    'title' => $row['c_title'],
    'keyword' => $row['c_title'],
    'desc' => $row['c_title'],
];

$twigData = [
    'global' => $global,
    'seo' => $seo,
];

// <=========== 业务代码结束

echo $twig->render($twigFile, $twigData);
$db->CloseConnection();
if ($onmemcache && $memcache) {
    $memcache->close();
}

exit();