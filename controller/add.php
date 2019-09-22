<?php

use model\Articles;
use model\Authorization;
use model\Users;

$mUsers = new Users($db);
$mAuth = new Authorization($mUsers);
if (!$mAuth->isAuth()){
    $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];
    redirect(ROOT . 'login/?msg=' . urlencode(NOT_AUTHORIZED));
}

$mArticle = new Articles($db);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $articleTitle = secure_data($_POST['title']);
    $articleContent = secure_data($_POST['content']);

    if (!($mArticle::checkTitle($articleTitle) && $mArticle::checkContent($articleContent))) {
        $msg = $mArticle::$lastError;
    }
    else {
        if ($insertId = $mArticle->insert(
            $articleTitle,
            $articleContent,
            $_SESSION[$mAuth::SESSION_USER_ID_KEY])) {
            redirect(ROOT . "article/$insertId/");
        }
        else {
            $msg = ARTICLE_SAVE_ERROR;
        }
    }
}
else {
    $msg = '';
    $articleTitle = '';
    $articleContent = '';
}


$menu = get_template('v_main.php', [
    'isAuth' => $mAuth->isAuth()
]);

$sidebar = get_template('sidebar/v_sidebar_short.php');

$content = get_template('v_add.php', [
    'title' => $articleTitle,
    'content' => $articleContent,
    'message' => $msg
]);

$title = ADD_ARTICLE_TITLE;
