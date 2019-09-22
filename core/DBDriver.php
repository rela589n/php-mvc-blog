<?php


namespace core;


use http\Exception\RuntimeException;
use PDO;
use PDOStatement;

class DBDriver implements DBDriverInterface
{
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param false|PDOStatement $statement
     * @return bool
     */
    protected static function checkErrors($statement)
    {
        if ($statement === false) {
            return false;
        }

        $errorInfo = $statement->errorInfo();
        if ($errorInfo[0] !== PDO::ERR_NONE) {
            exit(self::DB_FATAL_ERROR_MSG);
//        save to logs     exit($errorInfo[2]);
        }

        return true;
    }

    public function create(string $table, array $params)
    {
        $paramsKeys = array_keys($params);

        $keys = sprintf('(%s)', implode(', ', $paramsKeys));
        $values = sprintf('(:%s)', implode(', :', $paramsKeys));

        $sql = "INSERT INTO $table $keys VALUES $values;";
        $statement = $this->db->prepare($sql);

        $statement->execute($params);
        self::checkErrors($statement);

        return $this->db->lastInsertId();
    }

    public function read(string $sql, $fetch = self::FETCH_ALL, array $params = [])
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        self::checkErrors($statement);
        if ($fetch === self::FETCH_ONE) {
            return $statement->fetch(\PDO::FETCH_ASSOC);
        }
        if ($fetch === self::FETCH_ALL) {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }

        throw new RuntimeException(
            sprintf('Undefined fetch style in %s on line %s', __FILE__, __LINE__)
        );
    }

    private function mapParams(array $paramKeys)
    {
        $mapped = [];
        foreach ($paramKeys as $key) {
            $mapped [] = sprintf('%1$s = :%1$s', $key);
        }
        return $mapped;
    }

    public function update(string $table, array $setParams, string $where, array $whereParams)
    {
        if (empty(array_intersect_key($setParams, $whereParams))) {
            throw new RuntimeException(
                sprintf(
                    'The same keys in \$setParams and \$whereParams in %s:%s',
                    __FILE__,
                    __LINE__
                )
            );
        };

        $set = implode(', ', $this->mapParams(array_keys($setParams)));
        $sql = "UPDATE $table SET $set WHERE $where;";

        $statement = $this->db->prepare($sql);
        $statement->execute(array_merge($setParams, $whereParams));
        self::checkErrors($statement);

        return $statement->rowCount();
    }

    public function delete(string $table, string $where, array $whereParams)
    {
        $sql = "DELETE FROM $table WHERE $where;";
        $statement = $this->db->prepare($sql);
        $statement->execute($whereParams);
        self::checkErrors($statement);

        return $statement->rowCount();
    }
}