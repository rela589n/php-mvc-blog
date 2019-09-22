<?php

use model\Articles;
use model\Authorization;
use model\Users;

$mUsers = new Users($db);
$mAuth = new Authorization($mUsers);
if (!$mAuth->isAuth()) {
    $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];
    redirect(ROOT . 'login/?msg=' . urlencode(NOT_AUTHORIZED));
}

$id = $params[1] ?? null;
$mArticles = new Articles($db);

if (!$mArticles::checkId($id)) {
    redirect(ROOT . '?msg=' . urlencode($mArticles::$lastError));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $articleTitle = secure_data($_POST['title']);
    $articleContent = secure_data($_POST['content']);

    if (!isset($_SESSION['edit_id']) || $_SESSION['edit_id'] != $id) {
        $msg = PERMISSION_DENIED_ERROR;
    } elseif (!($mArticles::checkTitle($articleTitle) && $mArticles::checkContent($articleContent))) {
        $msg = $mArticles::$lastError;
    } else {
        unset($_SESSION['edit_id']);

        if ($id = $mArticles->update( $id, $articleTitle, $articleContent)) {
            redirect(ROOT . "article/$id/");
        } else {
            redirect(ROOT . '?msg=' . urlencode(ARTICLE_SAVE_ERROR));
        }
    }
} else {
    $article = $mArticles->getById($id);

    if (!$mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY]) &&
        $article['id_user'] != $_SESSION[$mAuth::SESSION_USER_ID_KEY]) {
        redirect(ROOT . '?msg=' . urlencode(EDIT_DENIED));
    }

    if (!$article) {
        $error404 = [
            'message' => ARTICLE_NOT_FOUND,
            'title' => TITLE_404
        ];
    } else {
        $_SESSION['edit_id'] = $id;

        $articleTitle = $article['title'];
        $articleContent = $article['content'];
        $msg = '';
    }

}
$sidebar = get_template('sidebar/v_sidebar_short.php');

if (!$error404){
    $content = get_template('v_edit.php', [
        'title' => $articleTitle,
        'content' => $articleContent,
        'message' => $msg
    ]);
    $title = 'Edit - ' . $articleTitle;
}
