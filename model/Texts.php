<?php


namespace model;


use core\DBDriverInterface;
use core\Validator;

class Texts extends Base
{
    public function __construct(DBDriverInterface $db)
    {
        parent::__construct($db, 'dashboard_texts', "alias");
    }

    public function update(string $name, string $value, string $alias, string $oldAlias = null)
    {
        $oldAlias = $oldAlias ?? $alias;

        return $this->db->update(
            $this->tableName,
            [
                'name' => $name,
                'value' => $value,
                $this->idAlias => $alias
            ],
            "{$this->idAlias} = :whereAlias",
            [
                'whereAlias' => $oldAlias
            ]
        );
    }
}