<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL & ~E_NOTICE);

PHP_VERSION > '5.1' && date_default_timezone_set('Asia/Shanghai');
session_cache_limiter('private, must-revalidate');
@ini_set('session.auto_start', 0); //自动启动关闭
@ini_set('session.bug_compat_warn', 0);
@ini_set('session.bug_compat_42', 0);

define('LCY_ROOT', substr(dirname(__FILE__), 0, -3));

require LCY_ROOT . 'inc/config.php';
require LCY_ROOT . 'inc/db.class.php';
require LCY_ROOT . 'inc/function.php';
$db = new Db();

if ($onMemCache) {
    $memCache = new Memcache();
    $memCache->connect('127.0.0.1', 11211) or die('MemCache Could not connect');
}

$global = [];
$global['cacheTime'] = 900; //缓存时间
$global['clientIp'] = GetIP();
$global['clientAgent'] = $_SERVER['HTTP_USER_AGENT'];
$global['script'] = str_replace(['/', '.php', 'php'], '', $_SERVER['SCRIPT_NAME']);
$global['ajaxFile'] = ['ajax'];
