<?php
include 'inc/conn.php';

// $do = isset($_GET['do']) ? $_GET['do'] : 'index'; //从url中取出do参数，如果没有提供do参数，就设置一个默认的'index'作为参数
// $file = 'pages/' . $do . '.php';
// if (!file_exists($file)) {
//     $file = 'pages/404.php';
// }
// include $file;

require 'vendor/autoload.php';

// 通过 FastRoute\simpleDispatcher() 方法定义路由，第一个参数必须是 FastRoute\RouteCollector实例
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /**
     * 通过 addRoute() 添加路由
     * $method 必须是大写，可以写成数组形式
     * $routePattern /开头, 可以用正则修饰
     * $handler
     */
    $r->addRoute('GET', '/', 'index');
    $r->addRoute('GET', '/other', '404');
    $r->addRoute('GET', '/contact', 'contact');
    $r->addRoute('GET', '/login', 'login');
    $r->addRoute('GET', '/serviceValidate', 'serviceValidate');
    $r->addRoute('POST', '/contact/submit', 'contact');
    $r->addRoute('GET', '/company', 'company');
    $r->addRoute('GET', '/detail/{id:\d+}', 'detail');
    // 分组
    $r->addGroup('/api', function (FastRoute\RouteCollector $r) {
        // {id} must be a number (\d+)
        $r->addRoute('POST', '/contact', 'api/contact');
        $r->addRoute('GET', '/other', 'api/other');
        $r->addRoute('POST', '/upload', 'api/upload');
        $r->addRoute('POST', '/qiniu', 'api/qiniu');
        $r->addRoute('GET', '/article/list', 'api/article-list');
        $r->addRoute('GET', '/article/lists', 'api/article-lists');
        $r->addRoute('GET', '/article/detail/{id:\d+}', 'api/article-detail');
        // The /{title} suffix is optional
        // $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
        // $r->addRoute('GET', '/users', 'get_all_users_handler');
    });
});

// 使用缓存
$dispatcher2 = FastRoute\cachedDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
    $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
    $r->addRoute('GET', '/user/{name}', 'handler2');
}, [
    'cacheFile' => __DIR__ . '/route.cache', /* required */
]);

// 获取请求和URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// 去除查询字符串(?foo=bar)和解码URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

$isApi = substr($uri, 0, 5) === '/api/';

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        if ($isApi) {
            $return = [];
            $return['code'] = 400;
            $return['data'] = null;
            $jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
            echo $jsonStr;
        } else {
            echo '... 404 Not Found';
        }
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        if ($isApi) {
            $return = [];
            $return['code'] = 400;
            $return['data'] = null;
            $jsonStr = json_encode($return, JSON_UNESCAPED_UNICODE);
            echo $jsonStr;
        } else {
            echo '... 405 Method Not Allowed';
        }
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $routeVars = $routeInfo[2];
        // var_dump('... call', $handler, 'with', $routeVars);

        include 'pages/' . $handler . '.php';

        break;
}
