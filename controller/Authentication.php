<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use model\Authorization;
use model\Users;
use model\Validator;

class Authentication extends Base
{
    public function indexAction()
    {
        $db = new DBDriver( DBConnector::getPdo());

        $validator = new Validator();
        $mUsers = new Users($db, $validator);
        $mAuth = new Authorization($mUsers);
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userName = secure_data($_POST['name']);
            $password = secure_data($_POST['password']);
            $remember = isset($_POST['remember']);

            if (!$userName || !$password) {
                $msg = FILL_IN_ALL_FIELDS;
            } elseif (!$mAuth->authorize($userName, $password, $remember)) {
                $msg = INVALID_LOGIN_OR_PASSWORD;
            } else {
                if (isset($_SESSION['back_redirect'])) {
                    $redirect = $_SESSION['back_redirect'];
                    unset($_SESSION['back_redirect']);
                } else {
                    $redirect = ROOT . 'dashboard/';
                }
                redirect($redirect);
            }
        } else {
            $mAuth::deauthorize();
            $msg = sprintf('%s', urldecode($_GET['msg'] ?? ''));
            $userName = $password = '';
        }

        $this->menu = self::getTemplate('header_menu/v_login_menu.php');
        $this->title = TITLE_LOGIN;

        $this->content = self::getTemplate('v_login.php', [
            'userName' => $userName,
            'password' => $password,
            'message' => $msg
        ]);
    }

    public function registerAction()
    {
        $db = new DBDriver(DBConnector::getPdo());
        $validator = new Validator();
        $mUsers = new Users($db, $validator);
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userName = secure_data($_POST['name']);
            $password = secure_data($_POST['password']);
            $rePassword = secure_data($_POST['re_password']);


            if (!($userName && $password && $rePassword)) {
                $msg = FILL_IN_ALL_FIELDS;
            } elseif (!($mUsers->checkPasswords($password, $rePassword)
                && $mUsers->checkUserName($userName))) {
                $msg = $mUsers::$lastError;
            } else {
                $mUsers->insert($userName, $password);
                redirect(ROOT . 'login/?msg=' . urlencode($mUsers::REGISTRATION_SUCCESSFUL));
            }
        } else {
            $msg = '';
            $userName = $password = $rePassword = '';
        }

        $this->title = TITLE_REGISTER;
        $this->menu = self::getTemplate('header_menu/v_register_menu.php');

        $this->content = self::getTemplate('v_register.php', [
            'userName' => $userName,
            'first_password' => $password,
            'second_password' => $rePassword,
            'message' => $msg
        ]);
    }


}