<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use model\Authorization;
use model\Texts;
use model\Users;

abstract class Base
{

    protected $title;
    protected $menu;
    protected $sidebar;
    protected $content;
    protected $footer;
    protected $message;
    protected $error404 = null;

    protected $mainTemplate = 'v_main.php';
    protected $error404Template = '404.php';

    public function render()
    {
        if (isset($this->error404)) {
            $this->content = self::getTemplate($this->error404Template, [
                'message' => $this->error404['message'] ?? ERROR_404
            ]);
            $this->title = $this->error404['title'] ?? TITLE_404;
        }
        if (!isset($this->menu)) {
            $mUsers = new Users(new DBDriver(DBConnector::getPdo()));
            $mAuth = new Authorization($mUsers);
            $this->menu = self::getTemplate('header_menu/v_main.php', [
                'isAuth' => $mAuth->isAuth()
            ]);
        }
        if (!isset($this->sidebar)) {
            $this->sidebar = self::getTemplate('sidebar/v_sidebar.php');
        }

        if (!isset($this->footer)) {

            $mTexts = new Texts( new DBDriver( DBConnector::getPdo()));
            $this->footer = self::getTemplate('v_footer.php', [
                'title1' => $mTexts->getOne('footer_1'),
                'title2' => $mTexts->getOne('footer_2'),
                'title3' => $mTexts->getOne('footer_3'),
                'copyright' => sprintf($mTexts->getOne('copyright'), date('Y'))
            ]);
        }

        self::printTemplate($this->mainTemplate, [
            'title' => $this->title,
            'menu' => $this->menu,
            'sidebar' => $this->sidebar,
            'content' => $this->content,
            'footer' => $this->footer,
            'message' => $this->message
        ]);
    }

    protected static function printTemplate(string $template, array $vars = [])
    {
        extract($vars);
        include __DIR__ . "/../view/$template";
    }

    protected static function getTemplate(string $template, array $vars = [])
    {
        ob_start();
        self::printTemplate($template, $vars);
        return ob_get_clean();
    }
}