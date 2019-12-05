<?php


namespace core;


class FrontController
{


    private function __construct()
    {
    }

    public static function run()
    {
        $instance = new self();

    }

    public function handleRequest()
    {

    }
}