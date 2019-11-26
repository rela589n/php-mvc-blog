<?php

include_once 'config.php';

use controller\NotFound;
use core\exceptions\NotFoundException;
use core\Request;

spl_autoload_register(function ($classPath) {
    $classPath = str_replace('\\', '/', $classPath);
    require_once sprintf('%s/%s.php', __DIR__, $classPath);
});

session_start();

$params = explode('/', $_GET['php_hru']);
if ($params[$last = count($params) - 1] == '') {
    unset($params[$last]);
    unset($last);
}

try {
    $controller = $params[0] ?? 'article';
    if (!in_array($controller, array_keys(CONTROLLERS_MAP), true)) {
        throw new NotFoundException("Page not found!");
    } else {
        $controller = CONTROLLERS_MAP[$controller];

        $id = null;
        if (isset($params[1]) && ctype_digit($params[1])) {
            $id = $params[1];
            $params[1] = 'single';
        }

        $action = $params[1] ?? 'index';

        if (!isset($id) && isset($params[2]) && ctype_digit($params[2])) {
            $id = $params[2];
        }
        $_GET['id'] = $id;
        $request = new Request();
        unset($_GET['id']);

        $action .= 'Action';

        $controller = new $controller($request);
        $controller->$action();
        $controller->render();
    }
} catch (NotFoundException $e) {
    $controller = new NotFound($request);
    $controller->indexAction($e->getMessage());
    $controller->render();
}