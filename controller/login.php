<?php

use model\Authorization;
use model\Users;

include_once('model/general.php');

$mUsers = new Users($db);
$mAuth = new Authorization($mUsers);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userName = secure_data($_POST['name']);
    $password = secure_data($_POST['password']);
    $remember = isset($_POST['remember']);

    if (!$userName || !$password) {
        $msg = FILL_IN_ALL_FIELDS;
    }
    elseif ($mAuth->authorize($userName, $password, $remember)){
        $msg = INVALID_LOGIN_OR_PASSWORD;
    }
    else {
        if (isset($_SESSION['back_redirect'])) {
            $redirect = $_SESSION['back_redirect'];
            unset($_SESSION['back_redirect']);
        }
        else {
            $redirect = ROOT . 'dashboard/';
        }
        redirect($redirect);
    }
}
else {
    $mAuth::deauthorize();
    $msg = sprintf('%s', urldecode($_GET['msg'] ?? ''));
    $userName = $password = '';
}

$title = TITLE_LOGIN;

$content = get_template('v_login.php', [
    'userName' => $userName,
    'password' => $password,
    'message' => $msg
]);


