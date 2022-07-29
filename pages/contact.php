<?php
require LCY_ROOT . 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(LCY_ROOT . 'twig');
$twig = new \Twig\Environment($loader, [
    'cache' => LCY_ROOT . 'cache',
    'auto_reload' => true, //根据文件更新时间，自动更新缓存
    'debug' => true,
]);
$twigFile = 'contact.twig';

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
    'action' => $action,
    'post' => $post,
];

// <=========== 业务代码结束

echo $twig->render($twigFile, $twigData);

$db->CloseConnection();
if ($onMemCache && $memCache) {
    $memCache->close();
}

exit();
