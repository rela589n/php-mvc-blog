<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use core\exceptions\AuthorizationException;
use core\exceptions\IncorrectDataException;
use model\User;
use core\Validator;
use core\services\User as UserService;

class Authentication extends Base
{
    public function indexAction()
    {
        $db = new DBDriver(DBConnector::getPdo());
        $userService = new UserService(new User($db), new Validator());

        $msg = $login = $password = '';
        $errors = null;
        if ($this->request->isPost()) {
            try {
                $userService->signIn($this->request->post());
                $redirect = $_SESSION['back_redirect'] ?? ROOT . '/dashboard/';

                unset($_SESSION['back_redirect']);
                $this->redirect($redirect, '');
            } catch (IncorrectDataException $e) {
                $msg = $e->getMessage();
                $errors = $e->getErrors();

            } catch (AuthorizationException $e) {
                $msg = $e->getMessage();
            }

            $params = $userService->getParams();
            $login = $params['user_name'];
            $password = $params['password'];

        } else if ($this->request->isGet()) {

            $userService->signOut();

            $msg = sprintf('%s', base64_decode($_GET['msg'] ?? ''));
        } else {
            throw new \Exception('Application can handle only GET and POST requests');
        }

        $this->menu = self::getTemplate('header_menu/v_login_menu.php');
        $this->title = TITLE_LOGIN;

        $this->content = self::getTemplate('v_login.php', [
            'userName' => $login,
            'password' => $password,
            'errors' => $errors,
            'message' => $msg
        ]);
    }

    public function registerAction()
    {
        $db = new DBDriver(DBConnector::getPdo());
        $userService = new UserService(new User($db), new Validator());

        $userName = $password = $rePassword = $msg = '';
        $errors = null;

        if ($this->request->isPost()) {
            try {
                $userService->signUp($this->request->post());
                $this->redirect('/auth/?msg=' . base64_encode(UserService::REGISTRATION_SUCCESSFUL));
            } catch (IncorrectDataException $e) {
                $errors = $e->getErrors();
                $msg = $e->getMessage();
            }
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