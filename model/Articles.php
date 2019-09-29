<?php


namespace model;

use core\DBDriverInterface;

class Articles extends Base
{
    private const SCHEMA = [
        'article_id' => [
            'type' => Validator::TYPE_INT
        ],
        'title' => [
            'required' => true,
            'required_message' => 'Article name must be not empty!',

            'type' => Validator::TYPE_STRING,

            'length' => [8, 64],
            'length_message' => 'Title length must be between 8 and 64 characters!'
        ],
        'content' => [
            'required' => true,
            'type' => Validator::TYPE_STRING,
            'length' => [8, 65535],
        ]
    ];

    const EMPTY_TITLE = 'Внимание! Название статьи не может быть пустым!';
    const TITLE_MIN_LEN = 8;
    const TITLE_MAX_LEN = 64;
    const TOO_SHORT_TITLE = 'Заголовок слишком короткий! Минимальная длина = ' . self::TITLE_MIN_LEN;
    const TOO_LONG_TITLE = 'Заголовок слишком длинный! Максимальная длина = ' . self::TITLE_MAX_LEN;

    const EMPTY_CONTENT = 'Внимание! Заполните контент статьи!';
    const CONTENT_MIN_LEN = 8;
    const TOO_SHORT_CONTENT = 'Контент слишком короткий! Минимальная длина = ' . self::CONTENT_MIN_LEN;

    const ARTICLE_ID_NOT_TRANSFERRED = 'Внимание! Не передано идентификатор статьи!';
    const ARTICLE_ID_INVALID = 'Внимание! Передано невалидный идентификатор статьи!';
    const DEFAULT_ARTICLE_OWNER = 'Anonymous';
    const CONTENT_PREVIEW_MAX_LENGTH = 20;

    private $joinTable = null;
    public function __construct(DBDriverInterface $db, Validator $validator)
    {
//        $validator->setSchema(self::SCHEMA);
        parent::__construct($db, $validator, 'articles', 'article_id');
        $this->validator->setSchema(self::SCHEMA);
        $this->joinTable = 'users';
    }

    public function getAll()
    {
        return $this->db->read(
            sprintf(
                'SELECT %1$s.*, %2$s.* from %1$s left join %2$s on %1$s.id_user = %2$s.id_user order by dt desc;',
                $this->tableName,
                $this->joinTable
            )
        );

    }

    public function getById($id)
    {
        return $this->db->read(
            sprintf(
                'select %1$s.*, %2$s.user_name from %1$s left join %2$s on %1$s.id_user = %2$s.id_user where %3$s = :id',
                $this->tableName,
                $this->joinTable,
                $this->idAlias
            ),
            $this->db::FETCH_ONE,
            [
                'id' => $id
            ]
        );
    }

    public function insert(string $title, string $content, int $userId)
    {
        return $this->db->create($this->tableName, [
            'title' => $title,
            'content' => $content,
            'id_user' => $userId
        ]);
    }

    public function update($id, string $title, string $content)
    {
        return $this->db->update(
            $this->tableName,
            [
                'title' => $title,
                'content' => $content,
            ],
            "{$this->idAlias} = :id",
            [
                'id' => $id
            ]
        );
    }

    public static function getArticleDate(string $date)
    {
        return date('F j, Y; H:i', strtotime($date));
    }

    public static function getRepresentation(array $article)
    {
        $article['dt'] = self::getArticleDate($article['dt']);
        if (!$article['user_name']) {
            $article['user_name'] = self::DEFAULT_ARTICLE_OWNER;
        }
        return $article;
    }

    public static function getPreviewRepresentation($article)
    {
        $article['content'] = substr($article['content'], 0, self::CONTENT_PREVIEW_MAX_LENGTH) . '...';
        return self::getRepresentation($article);
    }
}