<?php


namespace core;


use controller\NotFound;
use core\exceptions\NotFoundException;

class ControllerResolver
{
    private const DEFAULT_CONTROLLER = 'article';
    public const PAGE_NOT_FOUND_CONTROLLER = NotFound::class;

    private const CONTROLLERS_MAP = [
        'article' => '\controller\Articles',
        //    'edit' => 'edit.php',
        //    'add' => 'add.php',
        //    'dashboard' => 'dashboard/index.php',
        'auth' => '\controller\Authentication',
        'dashboard' => '\\controller\\Dashboard',
        //    'login' => '\controller\Authentication',
        //    'register' => '\controller\Authentication'
    ];

    private $request;
    private $params = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->params = $request->getPathParts();
    }

    public function getController(): \controller\Base
    {
        $controller = $this->params[0] ?? self::DEFAULT_CONTROLLER;

        $controller = self::CONTROLLERS_MAP[$controller] ?? null;
        if ($controller === null) {
            throw new NotFoundException();
        }

        return new $controller;
    }

    public function getAction(): string
    {
        $params = $this->params;

        $id = null;
        if (isset($params[1]) && ctype_digit($params[1])) {
            $id = $params[1];

            $params[1] = 'single';
        }

        $action = $params[1] ?? 'index';
        if (!isset($id) && isset($params[2]) && ctype_digit($params[2])) {
            $id = $params[2];
        }

        if ($id) {
            $setMethod = 'set' . $this->request->server('REQUEST_METHOD');
            $this->request->$setMethod('id', $id);
        }

        $action .= 'Action';
        return $action;
    }
}