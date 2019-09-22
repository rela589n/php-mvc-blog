<?php

$content = get_template('dashboard/v_home.php', [
    'userName' => $_SESSION[$mAuth::SESSION_USER_NAME_KEY],
    'isAdmin' => $mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY])
]);
$title = DASHBOARD_PAGE_TITLE;
