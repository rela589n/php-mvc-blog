<?php


namespace controller;

use core\exceptions\ArticlesNotFoundException;
use core\exceptions\DataBaseException;
use core\exceptions\IncorrectDataException;
use core\exceptions\NotFoundException;
use core\exceptions\RequestException;
use core\exceptions\ValidatorException;
use core\services\User as UserService;

class Articles extends Base
{
    public function indexAction()
    {
        $this->title = 'All articles';

        $articlesService = $this->container->fabricate('articles-service');
        $userService = $this->container->fabricate('user-service');

        try {
            $articles = $articlesService->getAllPreviews();

            $this->content = self::getTemplate('articles/v_articles.php', [
                'articles' => $articles,
                'isAdmin' => $userService->isAdmin($_SESSION[UserService::SESSION_USER_NAME_KEY] ?? ''),
                'userId' => $_SESSION[UserService::SESSION_USER_ID_KEY] ?? 0
            ]);

        } catch (ArticlesNotFoundException $e) {
            $this->content = "<h3>{$e->getMessage()}</h3>";
        }

        $this->message = sprintf('"%s"', base64_decode($_GET['msg'] ?? ''));
    }

    /**
     * @throws NotFoundException
     * @throws RequestException
     * @throws ValidatorException
     */
    public function singleAction()
    {
        $articlesService = $this->container->fabricate('articles-service');
        $userService = $this->container->fabricate('user-service');

        $article = $articlesService->getOneRepresentation($this->request->get('id'));

        $this->title = $article['title'];
        $this->content = self::getTemplate('articles/v_article.php', [
            'article' => $article,
            'isAuth' => $userService->isAuth(),
            'isOwner' => $userService->isAuth() &&
                ($userService->isAdmin($_SESSION[UserService::SESSION_USER_NAME_KEY]) ||
                    $article['id_user'] == $_SESSION[UserService::SESSION_USER_ID_KEY])
        ]);

        $this->sidebar = self::getTemplate('sidebar/v_sidebar_short.php');
    }

    /**
     * @throws NotFoundException
     * @throws RequestException
     * @throws ValidatorException
     */
    public function editAction()
    {
        $userService = $this->container->fabricate('user-service');

        if (!$userService->isAuth()) {
            $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];
            $this->redirect('/auth/?msg=' . base64_encode(NOT_AUTHORIZED));
        }

        $articlesService = $this->container->fabricate('articles-service');

        $msg = '';
        $errors = null;

        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            if (!isset($_SESSION['edit_id']) || $_SESSION['edit_id'] != $id) {
                $msg = PERMISSION_DENIED_ERROR;
            } else {
                try {
                    $articlesService->edit($this->request->post());

                    unset($_SESSION['edit_id']);
                    $this->redirect("/article/$id/");
                } catch (IncorrectDataException $e) {
                    $msg = $e->getMessage();
                    $errors = $e->getErrors();
                } catch (DataBaseException $e) {
                    $this->redirect('?msg=' . base64_encode(ARTICLE_SAVE_ERROR));
                }

                $params = $articlesService->getParams();
                $articleTitle = $params['title'];
                $articleContent = $params['content'];
            }
        } else { // GET
            $id = $this->request->get('id');
            $article = $articlesService->getOne($id);

            if (!$userService->isAdmin($_SESSION[UserService::SESSION_USER_NAME_KEY]) &&
                $article['id_user'] != $_SESSION[UserService::SESSION_USER_ID_KEY]) {
                $this->redirect('?msg=' . base64_encode(EDIT_DENIED));
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

    /**
     * @throws RequestException
     * @throws ValidatorException
     */
    public function addAction()
    {
        $userService = $this->container->fabricate('user-service');

        if (!$userService->isAuth()) {
            $_SESSION['back_redirect'] = $_SERVER["REQUEST_URI"];

            $this->redirect('/auth/?msg=' . base64_encode(NOT_AUTHORIZED));
        }

        $articlesService = $this->container->fabricate('articles-service');
        $msg = '';
        $title = '';
        $content = '';
        $errors = null;

        if ($this->request->isPost()) {
            try {
                $createParams = $this->request->post();
                $createParams['id_user'] = $_SESSION[UserService::SESSION_USER_ID_KEY];

                $insertId = $articlesService->create($createParams);
                $this->redirect("/article/$insertId/");
            } catch (IncorrectDataException $e) {
                $msg = ARTICLE_SAVE_ERROR;
                $errors = $e->getErrors();
            }

            $params = $articlesService->getParams();

            $title = $params['title'];
            $content = $params['content'];
        }

        $this->menu = self::getTemplate('header_menu/v_main.php', [
            'isAuth' => $userService->isAuth()
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