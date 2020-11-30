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

$post = $_POST;

$seo = [
    'title' => $row['c_title'],
    'keyword' => $row['c_title'],
    'desc' => $row['c_title'],
];

echo $twig->render('contact.twig', [
    'global' => $global,
    'seo' => $seo,
    'action' => $action,
    'post' => $post,
]);

$db->CloseConnection();
if ($onmemcache && $memcache) {
    $memcache->close();
}

exit();