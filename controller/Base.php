<?php


namespace controller;


use core\database\DBConnector;
use core\database\DBDriver;
use core\dependencies\ServiceBuilderBox;
use core\exceptions\NotFoundException;
use core\Registry;
use core\Request;
use model\Texts;
use model\User;
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
    protected $container;

    private $registry;

    public function __construct()
    {
        $this->registry = Registry::getInstance();
        $this->request = $this->registry->getRequest();

        $this->container = $this->registry->getDIContainer();
        $this->container->register(new ServiceBuilderBox());
    }

    public function render()
    {
        if (!isset($this->menu)) {
            $this->menu = self::getTemplate('header_menu/v_main.php', [
                'isAuth' => $this->container->fabricate('user-service')->isAuth()
            ]);
        }

        if (!isset($this->sidebar)) {
            $this->sidebar = self::getTemplate('sidebar/v_sidebar.php');
        }

        if (!isset($this->footer)) {
            $textsService = $this->container->fabricate('texts-service');

            $this->footer = self::getTemplate('v_footer.php', [
                'title1' => $textsService->getText('footer_1'),
                'title2' => $textsService->getText('footer_2'),
                'title3' => $textsService->getText('footer_3'),
                'copyright' => sprintf($textsService->getText('copyright'), date('Y'))
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

    /**
     * @param $name
     * @param $arguments
     * @throws NotFoundException
     */
    public function __call($name, $arguments)
    {
        throw new NotFoundException();
    }
}