<?php

if (!$mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY])) {
    $error404 = true;
}

$texts_page = $params[2] ?? 'all';

if (!in_array($texts_page, array_keys(TEXTS_TEMPLATES_MAP), true)) {
    $error404 = true;
}
else {
    include_once (sprintf("controller/dashboard/texts/%s", TEXTS_TEMPLATES_MAP[$texts_page] ));
}
