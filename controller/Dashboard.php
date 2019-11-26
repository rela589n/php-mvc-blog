<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use core\exceptions\TextsNotFoundException;
use model\Texts;
use model\User;
use core\Validator;
use core\services\User as UserService;

class Dashboard extends Base
{
    public function indexAction()
    {
        $db = new DBDriver(DBConnector::getPdo());
        $userService = new UserService(new User($db), new Validator());

        if (!$userService->isAuth()) {
            $this->redirect('/auth/?msg=' . base64_encode(NOT_AUTHORIZED));
        }

        $this->content = self::getTemplate('dashboard/v_home.php', [
            'userName' => $_SESSION[UserService::SESSION_USER_NAME_KEY],
            'isAdmin' => $userService->isAdmin($_SESSION[UserService::SESSION_USER_NAME_KEY])
        ]);
        $this->title = DASHBOARD_PAGE_TITLE;
    }

    public function textsAction() {
        $db = new DBDriver(DBConnector::getPdo());
        $userService = new UserService(new User($db), new Validator());

        if (!$userService->isAuth()) {
            $this->redirect('/auth/?msg=' . base64_encode(NOT_AUTHORIZED));
        }

        $textsService = new \core\services\Texts(new Texts($db), new Validator());

        try {
            $this->content = self::getTemplate('dashboard/texts/v_texts.php', [
                'texts' => $textsService->getAllPreviews()
            ]);
        }
        catch (TextsNotFoundException $e) {
            $this->content = "<h3>{$e->getMessage()}</h3>";
        }

        $this->title = DASHBOARD_PAGE_TITLE;
    }
}