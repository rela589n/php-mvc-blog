<?php


namespace core\dependencies;


use core\database\DBConnector;
use core\database\DBDriver;

use core\services\Articles as ArticleService;
use core\services\User as UserService;
use core\services\Texts as TextService;

use model\User as UserModel;
use model\Articles as ArticleModel;
use model\Texts as TextModel;

use core\Validator;


class ServiceBuilderBox implements RegisterBoxInterface
{

    public function register(DIContainer $container): void
    {
        $dbDriver = new DBDriver(DBConnector::getPdo());
        $container->set('articles-service', function () use ($dbDriver) {
            return new ArticleService(
                new ArticleModel($dbDriver),
                new Validator()
            );
        });

        $container->set('user-service', function () use ($dbDriver){
            return new UserService(
                new UserModel($dbDriver),
                new Validator()
            );
        });

        $container->set('texts-service', function () use ($dbDriver) {
            return new TextService(
                new TextModel($dbDriver),
                new Validator()
            );
        });
    }
}