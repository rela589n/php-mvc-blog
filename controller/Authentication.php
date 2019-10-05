<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use model\Authorization;
use model\Users;
use core\Validator;

class Authentication extends Base
{
    public function indexAction()
    {
        $db = new DBDriver( DBConnector::getPdo());

        $validator = new Validator();
        $mUsers = new Users($db, $validator);
        $mAuth = new Authorization($mUsers);
        $msg = '';
        $errors = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userName = secure_data($_POST['name']);
            $password = secure_data($_POST['password']);
            $remember = isset($_POST['remember']);

            $validator->validateByFields([
                'user_name' => $userName,
                'password' => $password
            ]);

            if (!$validator->success) {
                $errors = $validator->errors;
            }
            elseif (!$mAuth->authorize($userName, $password, $remember)) {
                $msg = INVALID_LOGIN_OR_PASSWORD;
            } else {
                $redirect= $_SESSION['back_redirect'] ?? ROOT . 'dashboard/';
                unset($_SESSION['back_redirect']);

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
            'errors' => $errors,
            'message' => $msg
        ]);
    }

    public function registerAction()
    {
        $db = new DBDriver(DBConnector::getPdo());
        $validator = new Validator();
        $mUsers = new Users($db, $validator);
        $msg = '';
        $errors = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userName = secure_data($_POST['name']);
            $password = secure_data($_POST['password']);
            $rePassword = secure_data($_POST['re_password']);

            $validator->validateByFields([
                'user_name' => $userName,
                'password' => $password,
                're_password' => $rePassword
            ]);

            if ($validator->success && $mUsers->exists($userName)) {
                $validator->appendErrors([
                    'user_name' => $mUsers::USER_ALREADY_EXISTS
                ]);
                $validator->success = false;
            }

            if (!$validator->success) {
                $errors = $validator->errors;
            }
            else {
                $mUsers->insert($userName, $password);
                redirect(ROOT . 'auth/?msg=' . urlencode($mUsers::REGISTRATION_SUCCESSFUL));
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
            'errors' => $errors,
            'message' => $msg
        ]);
    }


}