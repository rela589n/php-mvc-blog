<?php


namespace core;


interface DBDriverInterface
{
    const FETCH_ONE = 1;
    const FETCH_ALL = 2;
    const DB_FATAL_ERROR_MSG = 'Database error!';

    public function create(string $table, array $params);
    public function read(string $sql, $fetch = self::FETCH_ALL, array $params = []);
    public function update(string $table, array $setParams, string $where, array $whereParams);
    public function delete(string $table, string $where, array $whereParams);
}