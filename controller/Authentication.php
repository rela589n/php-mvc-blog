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
        if ($this->request->isPost()) {
            $userName = secure_data($this->request->post('name'));
            $password = secure_data( $this->request->post('password'));
            $remember = $this->request->post('remember');

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
                $msg = $e->getMessage();
            }
        } else if ($this->request->isGet()) {
            $mAuth::deauthorize();
            $msg = sprintf('%s', urldecode($_GET['msg'] ?? ''));
            $userName = $password = '';
        }
        else {
            throw new \Exception('Application can handle only GET and POST requests');
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

        if ($this->request->isPost()) {
            $userName = secure_data($this->request->post('name'));
            $password = secure_data($this->request->post('password'));
            $rePassword = secure_data($this->request->post('password_confirm'));

            try {
                $mUsers->register($userName, $password, $rePassword);
                redirect(ROOT . 'auth/?msg=' . urlencode($mUsers::REGISTRATION_SUCCESSFUL));
            } catch (IncorrectDataException $e) {
                $errors = $e->getErrors();
                $msg = $e->getMessage();
            }

        } else if ($this->request->isGet()) {
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