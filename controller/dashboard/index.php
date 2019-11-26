<?php

use model\Authorization;
use model\User;

$mUser = new User($db);
$mAuth = new Authorization($mUser);
if (!$mAuth->isAuth()) {
    $this->redirect('/auth/?msg=' . base64_encode(NOT_AUTHORIZED));
}

$dashboard_page = $params[1] ?? 'home';
if (!in_array($dashboard_page, array_keys(DASHBOARD_TEMPLATES_MAP), true)) {
    $error404 = true;
} else {
    include_once(__DIR__ .'/' . DASHBOARD_TEMPLATES_MAP[$dashboard_page]);
}
