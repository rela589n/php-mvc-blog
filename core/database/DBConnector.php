<?php

namespace core\database;

use PDO;
use PDOException;

abstract class DBConnector
{
    private static $pdo = null;

    public static function getPdo()
    {
        $db = &self::$pdo;
        if ($db === null) {
            try {
                $dsn = sprintf('%s:host=%s;dbname=%s', 'mysql', 'localhost', 'simple_blog');
                $db = new PDO($dsn, 'root', '');
                $db->exec('set names utf8;');
            } catch (PDOException $e) {
                exit("Database fatal error!");
            }
        }
        return $db;
    }
}
