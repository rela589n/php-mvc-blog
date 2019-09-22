<?php

/*******************************************************Database******************************************************/

//define('DB_NAME', 'simple_blog');
//define('DB_USER', 'root');
//define('DB_PASSWORD', '');

/********************************************************Errors*******************************************************/


define('ARTICLE_NOT_FOUND', 'Ошибка 404. Нет такой статьи!');
define('ARTICLE_SAVE_ERROR', 'Ошибка сохранения статьи');
define('FILL_IN_ALL_FIELDS', 'Заполните все поля!');

define('SUCCESSFULLY_SAVED', 'Успешно сохранено');

define('EDIT_DENIED', 'Внимание! Вам нельзя редактировать эту статью!');
define('PERMISSION_DENIED_ERROR', 'ERROR 403 - PERMISSION DENIED!!!');


// Authorization errors
define('INVALID_LOGIN_OR_PASSWORD', 'Неверный логин или пароль!');
define('NOT_AUTHORIZED', 'Для просмотра ресурса необходима авторизация!');


/******************************************************Views*********************************************************/


define('TITLE_404', 'Ошибка 404');
define('ERROR_404', 'Ошибка 404. Попробуйте начать с главной');
define('TITLE_LOGIN', 'Авторизация');
define('TITLE_REGISTER',  'Регистрация');
define('ADD_ARTICLE_TITLE', 'Добавить статью');
define('DASHBOARD_PAGE_TITLE', 'Личный кабинет');
define('ARTICLES_NOT_FOUND', 'На сайте пока ещё нет статей. Зайдите чуть-чуть-позже');
define('ROOT', '/php2/oop-blog/');

define('PAGE_NOT_FOUND', 'Ошибка 404 - страница не найдена!');

/**************************************************One entry point****************************************************/
/*

define('CONTROLLERS_MAP', [
    'article' => 'article.php',
    'edit' => 'edit.php',
    'add' => 'add.php',
    'dashboard' => 'dashboard/index.php',
    'home' => 'home.php',
    'login' => 'login.php',
    'register' => 'register.php'
]);
 */


define('CONTROLLERS_MAP', [
    'article' => '\controller\Articles',
//    'edit' => 'edit.php',
//    'add' => 'add.php',
//    'dashboard' => 'dashboard/index.php',
    'auth' => '\controller\Authentication',
//    'login' => '\controller\Authentication',
//    'register' => '\controller\Authentication'
]);

define('DASHBOARD_TEMPLATES_MAP', [
    'home' => 'home.php',
    'texts' => 'texts/index.php'
]);

define('TEXTS_TEMPLATES_MAP', [
    'all' => 'all_texts.php',
    'edit' => 'edit.php'
]) ;


/*************************************************Common functions****************************************************/

/**
 * @param string $path
 */
function redirect(string $path)
{
    header('Location: ' . $path);
    exit();
}

function secure_data(string $data)
{
    return htmlspecialchars(trim($data));
}
