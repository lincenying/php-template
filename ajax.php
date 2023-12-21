<?php
include 'inc/conn.php';

$action = isset($action) ? $action : 'other';
$action = str_replace("/", "-", $action);

$file = 'pages/api/' . $action . '.php';

if (!file_exists($file)) {
    $file = 'pages/api/other.php';
}

include $file;
