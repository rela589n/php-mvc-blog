<?php

use model\Users;

include_once('model/general.php');

$msg = '';
$mUsers = new Users($db);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userName = secure_data($_POST['name']);
    $password = secure_data($_POST['password']);
    $re_password = secure_data($_POST['re_password']);


    if (!($userName && $password && $re_password)) {
        $msg = FILL_IN_ALL_FIELDS;
    } elseif (!($mUsers->checkPasswords($password, $re_password)
        && $mUsers->checkUserName($userName))) {
        $msg = $mUsers::$lastError;
    } else {
        $mUsers->insert($userName, $password);
        redirect(ROOT . 'login/?msg=' . urlencode($mUsers::REGISTRATION_SUCCESSFUL));
    }
} else {
    $msg = '';
    $userName = $password = $re_password = '';
}

$title = TITLE_REGISTER;

$content = get_template('v_register.php', [
    'userName' => $userName,
    'first_password' => $password,
    'second_password' => $re_password,
    'message' => $msg
]);


