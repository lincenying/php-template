<?php
include 'inc/conn.php';
include cyRoom_ROOT . 'inc/func.template.php';

$seo = [
    'title' => '上传',
    'keyword' => '上传',
    'desc' => '上传',
];

$Template = new Template($templateDir, $isRewrite);
include Template::showTemplate('upload');
$output = ob_get_contents();
ob_end_clean();
echo $output;
$db->CloseConnection();
if ($onmemcache && $memcache) {
    $memcache->close();
}

exit();
