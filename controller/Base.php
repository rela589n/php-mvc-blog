<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;

use core\exceptions\NotFoundException;
use core\Request;
use model\Authorization;
use model\Texts;
use model\Users;
use core\Validator;

abstract class Base
{

    protected $title = '';
    protected $menu;
    protected $sidebar;
    protected $content;
    protected $footer;
    protected $message;
    protected $mainTemplate = 'v_main.php';
    protected $request;


    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function render()
    {
        if (!isset($this->menu)) {
            $mUsers = new Users(
                new DBDriver(
                    DBConnector::getPdo()
                ),
                new Validator());
            $mAuth = new Authorization($mUsers);

            $this->menu = self::getTemplate('header_menu/v_main.php', [
                'isAuth' => $mAuth->isAuth()
            ]);
        }
        if (!isset($this->sidebar)) {
            $this->sidebar = self::getTemplate('sidebar/v_sidebar.php');
        }

        if (!isset($this->footer)) {
            $mTexts = new Texts( new DBDriver( DBConnector::getPdo()), new Validator());
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

    /**
     * @param string $path
     * @param string $root
     */
    protected function redirect(string $path, string $root = ROOT)
    {
        header('Location: ' . $root . $path);
        exit();
    }

    public function __call($name, $arguments)
    {
        throw new NotFoundException();
    }
}