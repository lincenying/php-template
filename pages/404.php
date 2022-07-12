<?php
require LCY_ROOT . 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(LCY_ROOT . 'twig');
$twig = new \Twig\Environment($loader, [
    'cache' => LCY_ROOT . 'cache',
    'auto_reload' => true, //根据文件更新时间，自动更新缓存
    'debug' => true,
]);
$twigFile = '404.twig';

// 业务代码开始 ======>

$seo = [
    'title' => '404 page not found',
    'keyword' => '404 page not found',
    'desc' => '404 page not found',
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