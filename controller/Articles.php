<?php


namespace controller;

use core\DBConnector;
use core\DBDriver;
use core\exceptions\DataBaseException;
use core\exceptions\IncorrectDataException;
use core\exceptions\NotFoundException;
use core\exceptions\ValidatorException;
use model\Authorization;
use model\Users;
use core\Validator;


class Articles extends Base
{
    public function indexAction()
    {
        $this->title = 'All articles';

        $db = new DBDriver(DBConnector::getPdo());

        $mArticles = new \model\Articles($db, new Validator());
        $mUsers = new Users($db, new Validator());
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

        $this->message = sprintf('"%s"', base64_decode($_GET['msg'] ?? ''));
    }

    /**
     * @param $id
     * @throws NotFoundException
     * @throws ValidatorException
     */
    public function singleAction($id)
    {
        $db = new DBDriver(DBConnector::getPdo());
        $mArticles = new \model\Articles($db, new Validator());

        $article = $mArticles->getById($id);
        $article = $mArticles::getRepresentation($article);

        $mAuth = new Authorization(new Users($db, new Validator()));

        $this->title = $article['title'];
        $this->content = self::getTemplate('articles/v_article.php', [
            'article' => $article,
            'isAuth' => $mAuth->isAuth(),
            'isOwner' => $mAuth->isAuth() &&
                ($mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY]) ||
                    $article['id_user'] == $_SESSION[$mAuth::SESSION_USER_ID_KEY])
        ]);

        $this->sidebar = self::getTemplate('sidebar/v_sidebar_short.php');
    }

    /**
     * @param $id
     * @throws NotFoundException
     * @throws ValidatorException
     * @throws \core\exceptions\RequestException
     */
    public function editAction($id)
    {
        $db = new DBDriver(DBConnector::getPdo());
        $mAuth = new Authorization(new Users($db, new Validator()));

        if (!$mAuth->isAuth()) {
            $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];
            $this->redirect( '/auth/?msg=' . base64_encode(NOT_AUTHORIZED));
        }

        $mArticles = new \model\Articles($db, new Validator());

        $msg = '';
        $errors = null;
        if ($this->request->isPost()) {
            $articleTitle = secure_data($this->request->post('title'));
            $articleContent = secure_data($this->request->post('content'));

            if (!isset($_SESSION['edit_id']) || $_SESSION['edit_id'] != $id) {
                $msg = PERMISSION_DENIED_ERROR;
            } else {
                try {
                    $mArticles->update($id, $articleTitle, $articleContent);
                    unset($_SESSION['edit_id']);
                    $this->redirect("/article/$id/");
                } catch (IncorrectDataException $e) {
                    $msg = $e->getMessage();
                    $errors = $e->getErrors();
                } catch (DataBaseException $e) {
                    $this->redirect( '?msg=' . base64_encode(ARTICLE_SAVE_ERROR));
                }
            }
        } else { // GET
            $article = $mArticles->getById($id);

            if (!$mAuth->isAdmin($_SESSION[$mAuth::SESSION_USER_NAME_KEY]) &&
                $article['id_user'] != $_SESSION[$mAuth::SESSION_USER_ID_KEY]) {
                $this->redirect( '?msg=' . base64_encode(EDIT_DENIED));
            }

            $_SESSION['edit_id'] = $id;
            $articleTitle = $article['title'];
            $articleContent = $article['content'];
        }

        $this->title = 'Edit - ' . $articleTitle;
        $this->content = self::getTemplate('v_edit.php', [
            'title' => $articleTitle,
            'content' => $articleContent,
            'message' => $msg,
            'errors' => $errors
        ]);
        $this->sidebar = self::getTemplate('sidebar/v_sidebar_short.php');
    }

    public function addAction($id)
    {
        $db = new DBDriver(DBConnector::getPdo());
        $mUsers = new Users($db, new Validator());
        $mAuth = new Authorization($mUsers);

        if (!$mAuth->isAuth()) {
            $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];

            $this->redirect('/auth/?msg=' . base64_encode(NOT_AUTHORIZED));
        }

        $mArticle = new \model\Articles($db, new Validator());
        $msg = '';
        $title = '';
        $content = '';
        $errors = null;

        if ($this->request->isPost()) {

            $title = secure_data($this->request->post('title'));
            $content = secure_data($this->request->post('content'));

            try {
                $insertId = $mArticle->insert($title, $content, $_SESSION[$mAuth::SESSION_USER_ID_KEY]);
                $this->redirect("/article/$insertId/");
            } catch (IncorrectDataException $e) {
                $msg = ARTICLE_SAVE_ERROR;
                $errors = $e->getErrors();
            }
        }

        $this->menu = self::getTemplate('header_menu/v_main.php', [
            'isAuth' => $mAuth->isAuth()
        ]);

        $this->sidebar = self::getTemplate('sidebar/v_sidebar_short.php');

        $this->content = self::getTemplate('v_add.php', [
            'title' => $title,
            'content' => $content,
            'message' => $msg,
            'errors' => $errors
        ]);

        $this->title = ADD_ARTICLE_TITLE;
    }
}