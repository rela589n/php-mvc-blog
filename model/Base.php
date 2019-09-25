<?php

namespace model;

use core\DBDriverInterface;

abstract class Base
{
    protected $db;
    protected $tableName;
    protected $idAlias;
    protected $validator;

    public static $lastError = '';

    /**
     * BaseModel constructor.
     * @param DBDriverInterface $db
     * @param Validator $validator
     * @param string $tableName
     * @param string $idAlias
     */
    public function __construct(DBDriverInterface $db, Validator $validator, string $tableName, string $idAlias)
    {
        $this->db = $db;
        $this->validator = $validator;
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

    public function validate() {
        $this->validator->execute();
    }

//    public function insert(array $params)
//    {
//        $this->db->create($this->tableName, $params);
//    }

}