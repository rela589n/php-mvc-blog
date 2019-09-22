<?php

use model\Authorization;
use model\Users;

$mUsers = new Users($db);
$mAuth = new Authorization($mUsers);
if (!$mAuth->isAuth()) {
    redirect(ROOT . 'login/?msg=' . urlencode(NOT_AUTHORIZED));
}

$dashboard_page = $params[1] ?? 'home';
if (!in_array($dashboard_page, array_keys(DASHBOARD_TEMPLATES_MAP), true)) {
    $error404 = true;
} else {
    include_once(__DIR__ .'/' . DASHBOARD_TEMPLATES_MAP[$dashboard_page]);
}
