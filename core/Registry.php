<?php


namespace core;


class Registry
{
    private static $instance = null;
    private $request = null;

    private function __construct()
    {
    }

    public static function getInstance(): Registry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getRequest(): Request
    {
        if ($this->request === null) {
            $this->request = new Request();
        }

        return $this->request;
    }
}