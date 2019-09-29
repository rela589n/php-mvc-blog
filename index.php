<?php

include_once 'model/general.php';

use core\DBConnector;

spl_autoload_register(function ($classPath) {
    $classPath = str_replace('\\', '/', $classPath);
    require_once sprintf('%s/%s.php', __DIR__, $classPath);
});

session_start();

$db = DBConnector::getPdo();


$params = explode('/', $_GET['php_hru']);
if ($params[$last = count($params) - 1] == '') {
    unset($params[$last]);
    unset($last);
}

$error404 = null;
$controller = $params[0] ?? 'article';
if (!in_array($controller, array_keys(CONTROLLERS_MAP), true)) {
    $error404 = [
        'message' => PAGE_NOT_FOUND,
        'title' => TITLE_404
    ];
} else {
//    include_once(sprintf("controller/%s", CONTROLLERS_MAP[$controller]));
    $controller = CONTROLLERS_MAP[$controller];

    $id = null;
    if (isset($params[1]) && ctype_digit($params[1])) {
        $id = $params[1];
        $params[1] = 'single';
    }

    $action = isset($params[1]) && preg_match("/^[a-z]+$/i", $params[1]) ? $params[1] : 'index';
    if (!isset($id) && isset($params[2]) && ctype_digit($params[2])) {
        $id = $params[2];
    }

    $action .= 'Action';


//    try {
        $controller = new $controller();
        $controller->$action($id);
        $controller->render();

//    } catch (Exception $e) {
//        var_dump($e->getMessage(), $e->getTrace());
//    }
}