<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use core\exceptions\AuthorizationException;
use core\exceptions\IncorrectDataException;
use model\Authorization;
use model\Users;
use core\Validator;

class Authentication extends Base
{
    public function indexAction()
    {
        $db = new DBDriver(DBConnector::getPdo());

        $mUsers = new Users($db, new Validator());
        $mAuth = new Authorization($mUsers);

        $msg = '';
        $errors = null;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userName = secure_data($_POST['name']);
            $password = secure_data($_POST['password']);
            $remember = isset($_POST['remember']);

            $errors = null;
            try {
                $mAuth->authorize($userName, $password, $remember);
                $redirect = $_SESSION['back_redirect'] ?? ROOT . 'dashboard/';
                unset($_SESSION['back_redirect']);

                redirect($redirect);
            } catch (IncorrectDataException $e) {
                $msg = $e->getMessage();
                $errors = $e->getErrors();
            } catch (AuthorizationException $e) {
                $msg = INVALID_LOGIN_OR_PASSWORD;
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
        $mUsers = new Users($db, new Validator());
        $msg = '';
        $errors = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userName = secure_data($_POST['name']);
            $password = secure_data($_POST['password']);
            $rePassword = secure_data($_POST['re_password']);

            try {
                $mUsers->register($userName, $password, $rePassword);
                redirect(ROOT . 'auth/?msg=' . urlencode($mUsers::REGISTRATION_SUCCESSFUL));
            } catch (IncorrectDataException $e) {
                $errors = $e->getErrors();
                $msg = $e->getMessage();
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