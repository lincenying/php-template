<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL & ~E_NOTICE);

PHP_VERSION > '5.1' && date_default_timezone_set('Asia/Shanghai');
session_cache_limiter('private, must-revalidate');
@ini_set('session.auto_start', 0); //自动启动关闭
@ini_set('session.bug_compat_warn', 0);
@ini_set('session.bug_compat_42', 0);

define('cyRoom_ROOT', substr(dirname(__FILE__), 0, -3));

require cyRoom_ROOT . 'inc/config.php';
require cyRoom_ROOT . 'inc/db.class.php';
require cyRoom_ROOT . 'inc/function.php';
$db = new Db();

if ($onmemcache) {
    $memcache = new Memcache();
    $memcache->connect('127.0.0.1', 11211) or die('Could not connect');
}

$global = [];
$global['cachetime'] = 900; //缓存时间
$global['clientip'] = GetIP();
$global['clientagent'] = $_SERVER['HTTP_USER_AGENT'];
$global['script'] = str_replace(['/', '.php', 'php'], '', $_SERVER['SCRIPT_NAME']);
$global['ajaxfile'] = ['ajax'];
?>
