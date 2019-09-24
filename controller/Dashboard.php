<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use model\Authorization;
use model\Texts;
use model\Users;

class Dashboard extends Base
{
    public function indexAction()
    {
        $db = new DBDriver(DBConnector::getPdo());
        $mUsers = new Users($db);
        $mAuth = new Authorization($mUsers);

        if (!$mAuth->isAuth()) {
            redirect(ROOT . 'auth/?msg=' . urlencode(NOT_AUTHORIZED));
        }

        $this->content = self::getTemplate('dashboard/v_home.php', [
            'userName' => $_SESSION[$mAuth::SESSION_USER_NAME_KEY],
            'isAdmin' => $mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY])
        ]);
        $this->title = DASHBOARD_PAGE_TITLE;
    }

    public function textsAction() {
        $db = new DBDriver(DBConnector::getPdo());
        $mUsers = new Users($db);
        $mAuth = new Authorization($mUsers);

        if (!$mAuth->isAuth()) {
            redirect(ROOT . 'auth/?msg=' . urlencode(NOT_AUTHORIZED));
        }

        $mTexts = new Texts($db);
        $this->content = self::getTemplate('dashboard/texts/v_texts.php', [
            'texts' => $mTexts->getPreviews($mTexts->getAll())
        ]);

        $this->title = DASHBOARD_PAGE_TITLE;

    }

}