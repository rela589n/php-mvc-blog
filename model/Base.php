<?php

namespace model;

use core\DBDriverInterface;
use core\exceptions\IncorrectDataException;
use core\exceptions\ValidatorException;

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
        $this->validator->validateByFields([
            $this->idAlias => $id
        ]);

        if (!$this->validator->success) {
            throw new IncorrectDataException("Invalid param id passed!");
        }

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
        $this->validator->validateByFields([
            $this->idAlias => $id
        ]);

        if (!$this->validator->success) {
            throw new IncorrectDataException("Invalid param id passed!");
        }

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