<?php


namespace model;

use core\DBDriverInterface;
use core\exceptions\DataBaseException;
use core\exceptions\IncorrectDataException;
use core\exceptions\NotFoundException;
use core\exceptions\ValidatorException;
use core\Validator;

class Articles extends Base
{
    const EMPTY_TITLE = 'Article title must not be empty!';
    const TITLE_MIN_LEN = 8;
    const TITLE_MAX_LEN = 64;
    const TOO_SHORT_TITLE = 'Заголовок слишком короткий! Минимальная длина = ' . self::TITLE_MIN_LEN;
    const TOO_LONG_TITLE = 'Заголовок слишком длинный! Максимальная длина = ' . self::TITLE_MAX_LEN;

    const EMPTY_CONTENT = 'Your attention, please! Fill article content!';
    const CONTENT_MIN_LEN = 8;
    const TOO_SHORT_CONTENT = 'Контент слишком короткий! Минимальная длина = ' . self::CONTENT_MIN_LEN;

    const ARTICLE_ID_NOT_TRANSFERRED = 'Внимание! Не передано идентификатор статьи!';
    const ARTICLE_ID_INVALID = 'Внимание! Передано невалидный идентификатор статьи!';
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
            'required_message' => self::EMPTY_TITLE,

            'type' => Validator::TYPE_STRING,

            'min_length' => 8,
            'max_length' => 64,

            'min_length_message' => 'Title length must be at least %1$d characters!',
            'max_length_message' => 'Title length must be below %1$d characters. %2$d got!',
        ],
        'content' => [
            'required' => true,
            'required_message' => self::EMPTY_CONTENT,
            'type' => Validator::TYPE_STRING,
            'min_length' => 8
        ]
    ];

    private $joinTable = null;

    public function __construct(DBDriverInterface $db, Validator $validator)
    {
//        $validator->setSchema(self::SCHEMA);
        parent::__construct($db, $validator, 'articles', 'article_id');
        $this->validator->setSchema(self::SCHEMA);
        $this->joinTable = 'users';
    }

    /**
     * @return mixed
     */
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

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundException
     * @throws ValidatorException
     */
    public function getById($id)
    {
        $this->validator->validateByFields([
            $this->idAlias => $id
        ]);

        if (!$this->validator->success) {
            throw new NotFoundException();
        }

        $r = $this->db->read(
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
        if (!$r) {
            throw new NotFoundException();
        }
        return $r;
    }

    /**
     * @param string $title
     * @param string $content
     * @param $userId
     * @return mixed
     * @throws IncorrectDataException
     * @throws ValidatorException
     */
    public function insert(string $title, string $content, $userId)
    {
        $fields = [
            'title' => $title,
            'content' => $content,
            'id_user' => $userId
        ];
        $this->validator->validateByFields($fields);

        if (!$this->validator->success) {
            throw new IncorrectDataException(
                $this->validator->errors,
                'Invalid params given to insert method'
            );
        }

        return $this->db->create($this->tableName, $fields);
    }

    /**
     * @param $id
     * @param string $title
     * @param string $content
     * @return mixed
     * @throws IncorrectDataException
     * @throws ValidatorException
     * @throws DataBaseException
     */
    public function update($id, string $title, string $content)
    {
        $this->validator->validateByFields([
            'article_id' => $id,
            'title' => $title,
            'content' => $content
        ]);

        if (!$this->validator->success) {
            throw new IncorrectDataException(
                $this->validator->errors,
                'Invalid params given to update method'
            );
        }

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
    public static function getRepresentation(array $article)
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
    public static function getPreviewRepresentation($article)
    {
        $article['content'] = substr($article['content'], 0, self::CONTENT_PREVIEW_MAX_LENGTH) . '...';
        return self::getRepresentation($article);
    }
}