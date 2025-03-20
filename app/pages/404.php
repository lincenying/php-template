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
    'title' => '404 Page Not Found',
    'keyword' => '404 Page Not Found',
    'desc' => '404 Page Not Found',
];

$twigData = [
    'global' => $global,
    'router' => $routeVars,
    'seo' => $seo,
];

// <=========== 业务代码结束

echo $twig->render($twigFile, $twigData);
$db->CloseConnection();
if ($onMemCache && $memCache) {
    $memCache->close();
}

exit();
