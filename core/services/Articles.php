<?php


namespace core\services;


use core\exceptions\ArticlesNotFoundException;
use core\exceptions\DataBaseException;
use core\exceptions\IncorrectDataException;
use core\exceptions\NotFoundException;
use core\exceptions\ValidatorException;
use core\Validator;
use model\Articles as ArticlesModel;

class Articles extends Base
{
    const ARTICLES_NOT_FOUND = 'На сайте пока ещё нет статей. Зайдите чуть-чуть-позже';
    const DEFAULT_ARTICLE_OWNER = 'Anonymous';
    const CONTENT_PREVIEW_MAX_LENGTH = 20;

    private const SCHEMA = [
        'article_id' => [
            'type' => Validator::TYPE_INT
        ],
        'id_user' => [
            'type' => Validator::TYPE_INT
        ],
        'title' => [
            'required' => true,
            'required_message' => 'Article title must not be empty!',

            'type' => Validator::TYPE_STRING,

            'min_length' => 8,
            'max_length' => 64,

            'min_length_message' => 'Title length must be at least %1$d characters!',
            'max_length_message' => 'Title length must be below %1$d characters. %2$d got!',
        ],
        'content' => [
            'required' => true,
            'required_message' => 'Your attention, please! Fill article content!',
            'type' => Validator::TYPE_STRING,
            'min_length' => 8
        ]
    ];

    private $mArticles;

    public function __construct(ArticlesModel $mArticles, Validator $validator)
    {
        $this->mArticles = $mArticles;
        parent::__construct($validator);
        $this->validator->setSchema(self::SCHEMA);
    }

    /**
     * @return array
     * @throws ArticlesNotFoundException
     */
    public function getAllPreviews(): array
    {
        $articles = $this->mArticles->getAll();
        if (empty($articles)) {
            throw new ArticlesNotFoundException(self::ARTICLES_NOT_FOUND);
        }

        $articles = array_map(function ($article) {
            return self::getPreviewRepresentation($article);
        }, $articles);

        return $articles;
    }


    /**
     * @param array $params
     * @return mixed
     * @throws IncorrectDataException
     * @throws ValidatorException
     */
    public function create(array $params)
    {
        $fields = [
            'title' => $params['title'],
            'content' => $params['content'],
            'id_user' => $params['id_user']
        ];
        $this->params = $fields;
        $this->validator->validateByFields($fields);

        if (!$this->validator->success()) {
            throw new IncorrectDataException(
                $this->validator->errors,
                'Invalid params given to insert method'
            );
        }

        return $this->mArticles->insert($this->validator->clear);
    }

    /**
     * @param array $params
     * @return mixed
     * @throws IncorrectDataException
     * @throws ValidatorException
     * @throws DataBaseException
     */
    public function edit(array $params)
    {
        $this->params = $params;
        $this->validator->validateByFields([
            'article_id' => $params['id'],
            'title' => $params['title'],
            'content' => $params['content']
        ]);

        if (!$this->validator->success()) {
            throw new IncorrectDataException(
                $this->validator->errors,
                'Invalid params given to update method'
            );
        }

        $clear = $this->validator->clear;
        return $this->mArticles->update(
            $clear['article_id'],
            $clear['title'],
            $clear['content']
        );
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundException
     * @throws ValidatorException
     */
    public function getOne($id)
    {
        $idAlias = $this->mArticles->getIdAlias();
        $this->validator->validateByFields([
            $idAlias => $id
        ]);

        if (!$this->validator->success()) {
            throw new NotFoundException();
        }
        $article = $this->mArticles->getById($this->validator->clear[$idAlias]);

        if (empty($article)) {
            throw new NotFoundException();
        }

        return $article;
    }

    /**
     * @param $id
     * @return array
     * @throws NotFoundException
     * @throws ValidatorException
     */
    public function getOneRepresentation($id)
    {
        return self::getRepresentation($this->getOne($id));
    }

    /**
     * @param string $date
     * @return false|string
     */
    public static function getArticleDate(string $date)
    {
        return date('F j, Y; H:i', strtotime($date));
    }

    /**
     * @param array $article
     * @return array
     */
    public static function getRepresentation(array $article): array
    {
        $article['dt'] = self::getArticleDate($article['dt']);
        if (!$article['user_name']) {
            $article['user_name'] = self::DEFAULT_ARTICLE_OWNER;
        }
        return $article;
    }

    /**
     * @param $article
     * @return array
     */
    public static function getPreviewRepresentation($article): array
    {
        $article['content'] = substr($article['content'], 0, self::CONTENT_PREVIEW_MAX_LENGTH) . '...';
        return self::getRepresentation($article);
    }
}