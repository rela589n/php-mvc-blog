<?php

include_once 'model/general.php';


use core\DBConnector;
use model\Authorization;
use model\Texts;
use model\Users;

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

    $controller = new $controller();
    $controller->$action($id);

    $controller->render();
}

/*

if ($error404) {
    $content = get_template('404.php', [
        'message' => $error404['message'] ?? PAGE_NOT_FOUND
    ]);
    $title = $error404['title'] ?? TITLE_404;
}


if (!isset($menu)) {
    $mUsers = new Users($db);
    $mAuth = new Authentication($mUsers);
    $menu = get_template('v_main.php', [
        'isAuth' => $mAuth->isAuth()
    ]);
}

if (!isset($sidebar)) {
    $sidebar = get_template('sidebar/v_sidebar.php');
}

if (!isset($footer)) {
    $mTexts = new Texts($db);
    $footer = get_template('v_footer.php', [
        'copyright' => sprintf($mTexts->getOne('copyright'), date('Y')),
        'title1' => $mTexts->getOne('footer_1'),
        'title2' => $mTexts->getOne('footer_2'),
        'title3' => $mTexts->getOne('footer_3'),
    ]);
}
print_template('v_main.php', [
    'title' => $title ?? '',
    'menu' => $menu,
    'sidebar' => $sidebar,
    'content' => $content ?? '',
    'footer' => $footer,
    'message' => $message ?? ''
]);*/

/*


/article/
/article/10/
/article/edit/10
/article/add/





 */