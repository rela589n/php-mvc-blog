<?php


namespace model;

use core\database\DBDriverInterface;
use core\exceptions\DataBaseException;

class Articles extends Base
{
    private $joinTable = null;

    public function __construct(DBDriverInterface $db)
    {
        parent::__construct($db, 'articles', 'article_id');
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
     */
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

    /**
     * @param $id
     * @param string $title
     * @param string $content
     * @return mixed
     * @throws DataBaseException
     */
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
}