<?php


namespace model;


use core\DBDriverInterface;

class Users extends Base
{
    const PASSWORDS_NOT_IDENTICAL = 'Внимание! Пароли не идентичны!';
    const PASSWORD_MIN_LENGTH = 8;
    const TOO_SHORT_PASSWORD = 'Пароль слишком короткий! Минимальная длина = ' . self::PASSWORD_MIN_LENGTH;
    const USER_NAME_MIN_LENGTH = 5;
    const TOO_SHORT_USER_NAME = 'Имя пользователя слишком короткое! Минимальная длина = ' . self::USER_NAME_MIN_LENGTH;

    const USER_ALREADY_EXISTS = 'Пользователь с таким логином уже существует!';
    const INVALID_USERNAME = 'Имя пользователя может содержать только латинские буквы, цифры, дефис и нижнее подчёркивание!';
    const VALID_USER_NAME_PATTERN = '/^[0-9a-z_\-]+$/i';
    const REGISTRATION_SUCCESSFUL = 'Регистрация успешна! Пожалуйста войдите.';

    const HASH_SALT = '@2|\4/1*&';

    private const SCHEMA = [
        'id_user' => [
            'type' => 'int',
        ],
        'user_name' => [
            'type' => 'string',
            'length' => [4, 64],
            'required' => true
        ],
        'password' => [
            'type' => 'string',
            'length' => [5, 50],
            'required' => true
        ],
        're_password' => [
            'equals_to' => 'password',
            'required' => true
        ]
    ];


    public function __construct(DBDriverInterface $db, Validator $validator)
    {
        $validator->setSchema(self::SCHEMA);
        parent::__construct($db, $validator, 'users', 'id_user');
    }

    public function insert(string $userName, string $password)
    {
        return $this->db->create($this->tableName, [
            'user_name' => $userName,
            'password' => self::hashSha512($password)
        ]);
    }

    public static function hashSha512(string $str)
    {
        return hash("sha512", $str . self::HASH_SALT);
    }

    public function exists(string $userName)
    {
        return boolval(
            $this->db->read(
                "select exists(select id_user from {$this->tableName} where user_name = :user) as user_exist",
                $this->db::FETCH_ONE,
                [
                    'user' => $userName
                ]
            )['user_exist']
        );

    }

    public function getByName(string $userName)
    {
        return $this->db->read(
            "select * from {$this->tableName} where user_name = :user",
            $this->db::FETCH_ONE,
            [
                'user' => $userName
            ]
        );
    }

    public function checkPasswords(string $password, string $re_password)
    {
        if (mb_strlen($password) < self::PASSWORD_MIN_LENGTH) {
            self::$lastError = self::TOO_SHORT_PASSWORD;
            return false;
        }
        if ($password !== $re_password) {
            self::$lastError = self::PASSWORDS_NOT_IDENTICAL;
            return false;
        }
        return true;
    }

    public function checkUserName(string $name)
    {
        if (strlen($name) < self::USER_NAME_MIN_LENGTH) {
            self::$lastError = self::TOO_SHORT_USER_NAME;
        }

        if (!preg_match(self::VALID_USER_NAME_PATTERN, $name)) {
            self::$lastError = self::INVALID_USERNAME;
            return false;
        }

        if (self::exists($name)) {
            self::$lastError = self::USER_ALREADY_EXISTS;
            return false;
        }

        return true;
    }
}