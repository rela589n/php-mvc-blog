<?php

include_once 'config.php';

use core\FrontController;

spl_autoload_register(function ($classPath) {
    $classPath = str_replace('\\', '/', $classPath);
    require_once sprintf('%s/%s.php', __DIR__, $classPath);
});

session_start();

FrontController::run();
