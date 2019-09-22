<?php

namespace model;

use core\DBDriverInterface;
use PDOStatement;

abstract class Base
{
    protected $db;
    protected $tableName;
    protected $idAlias;

    public static $lastError = '';

    /**
     * BaseModel constructor.
     * @param $db
     * @param $tableName
     * @param $idAlias
     */
    public function __construct(DBDriverInterface $db, string $tableName, string $idAlias)
    {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->idAlias = $idAlias;
    }

    public function getById($id)
    {
        return $this->db->read(
            "SELECT * FROM {$this->tableName} WHERE {$this->idAlias} = :id",
            $this->db::FETCH_ONE,
            ['id' => $id]
        );
    }

    public function getAll()
    {
        return $this->db->read("SELECT * FROM {$this->tableName}");
    }

    public function deleteById($id)
    {
        return $this->db->delete(
            $this->tableName,
            "{$this->idAlias} = :id",
            ['id' => $id]
        );
    }


//    public function insert(array $params)
//    {
//        $this->db->create($this->tableName, $params);
//    }

}