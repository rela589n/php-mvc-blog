<?php


namespace core;


class FrontController
{
    private $reg;

    private function __construct()
    {
        $this->reg = Registry::getInstance();
    }

    public static function run()
    {
        $instance = new self();

        $instance->handleRequest();
    }

    public function handleRequest()
    {
        $request = $this->reg->getRequest();
        $controllerResolver = new ControllerResolver($request);

        try {
            $controller = $controllerResolver->getController();
            $action = $controllerResolver->getAction();

            $controller->$action();
            $controller->render();
        } catch (exceptions\NotFoundException $e) {
            $controller = ControllerResolver::PAGE_NOT_FOUND_CONTROLLER;
            $controller = new $controller;

            $controller->indexAction();
            $controller->render();
        }
    }
}