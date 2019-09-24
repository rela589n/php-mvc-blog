<?php


namespace controller;


use core\DBConnector;
use core\DBDriver;
use model\Authorization;
use model\Users;

class Articles extends Base
{
    public function indexAction()
    {
        $this->title = 'All articles';

        $db = new DBDriver(DBConnector::getPdo());
        $mArticles = new \model\Articles($db);
        $mUsers = new Users($db);
        $mAuth = new Authorization($mUsers);

        $articles = $mArticles->getAll();
        if (empty($articles)) {
            $this->content = '<h3>' . ARTICLES_NOT_FOUND . '</h3>';
        } else {
            $articles = array_map(function (array $article) use ($mArticles) {
                return $mArticles::getPreviewRepresentation($article);
            }, $articles);

            $this->content = self::getTemplate('articles/v_articles.php', [
                'articles' => $articles,
                'isAdmin' => $mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY] ?? ''),
                'userId' => $_SESSION[$mAuth::SESSION_USER_ID_KEY] ?? 0
            ]);
        }

        $this->message = sprintf('"%s"', urldecode($_GET['msg'] ?? ''));
    }

    public function singleAction($id)
    {
        $db = new DBDriver(DBConnector::getPdo());
        $mArticles = new \model\Articles($db);

        if (!$mArticles::checkId($id)) {
            redirect(ROOT . '?msg=' . urlencode($mArticles::$lastError));
        }
        $mUsers = new Users($db);
        $mAuth = new Authorization($mUsers);

        $this->sidebar = self::getTemplate( 'sidebar/v_sidebar_short.php');

        $article = $mArticles->getById($id);
        if (!$article) {
            $this->error404 = [
                'message' => ARTICLE_NOT_FOUND,
                'title' => TITLE_404
            ];
        } else {
            $article = $mArticles::getRepresentation($article);
            $this->content = self::getTemplate('articles/v_article.php', [
                'article' => $article,
                'isAuth' => $mAuth->isAuth(),
                'isOwner' => $mAuth->isAuth() &&
                    ($mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY]) ||
                        $article['id_user'] == $_SESSION[$mAuth::SESSION_USER_ID_KEY])
            ]);
            $this->title = $article['title'];
        }
    }

    public function editAction($id) {
        $db = new DBDriver(DBConnector::getPdo());

        $mUsers = new Users($db);
        $mAuth = new Authorization($mUsers);
        if (!$mAuth->isAuth()) {
            $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];
            redirect(ROOT . 'login/?msg=' . urlencode(NOT_AUTHORIZED));
        }

        $mArticles = new \model\Articles($db);
        if (!$mArticles::checkId($id)) {
            redirect(ROOT . '?msg=' . urlencode($mArticles::$lastError));
        }

        $msg = '';
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
                $this->error404 = [
                    'message' => ARTICLE_NOT_FOUND,
                    'title' => TITLE_404
                ];
            } else {
                $_SESSION['edit_id'] = $id;
                $articleTitle = $article['title'];
                $articleContent = $article['content'];
            }
        }
        $this->sidebar = self::getTemplate('sidebar/v_sidebar_short.php');

        if (!$this->error404){
            $this->content = self::getTemplate('v_edit.php', [
                'title' => $articleTitle,
                'content' => $articleContent,
                'message' => $msg
            ]);
            $this->title = 'Edit - ' . $articleTitle;
        }

    }

    public function addAction($id) {
        $db = new DBDriver(DBConnector::getPdo());

        $mUsers = new Users($db);
        $mAuth = new Authorization($mUsers);
        if (!$mAuth->isAuth()){
            $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];
            redirect(ROOT . 'login/?msg=' . urlencode(NOT_AUTHORIZED));
        }

        $mArticle = new \model\Articles($db);
        $msg = '';
        $articleTitle = '';
        $articleContent = '';

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

        $this->menu = self::getTemplate('header_menu/v_main.php', [
            'isAuth' => $mAuth->isAuth()
        ]);

        $this->sidebar = self::getTemplate('sidebar/v_sidebar_short.php');

        $this->content = self::getTemplate('v_add.php', [
            'title' => $articleTitle,
            'content' => $articleContent,
            'message' => $msg
        ]);

        $this->title = ADD_ARTICLE_TITLE;

    }


}