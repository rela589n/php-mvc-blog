<?php


namespace model;


use core\DBDriverInterface;
use core\exceptions\IncorrectDataException;
use core\Validator;

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
            'preg_match' => '/^[0-9a-z_\-]+$/i',
//            'preg_match_message' => self::INVALID_USERNAME,
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
        parent::__construct($db, $validator, 'users', 'id_user');
        $this->validator->setSchema(self::SCHEMA);
    }

    public function register(string $userName, string $password, string $rePassword)
    {
        $validator = &$this->validator;
        $validator->validateByFields([
            'user_name' => $userName,
            'password' => $password,
            're_password' => $rePassword
        ]);

        if ($validator->success && $this->exists($userName)) {
            $validator->appendErrors([
                'user_name' => self::USER_ALREADY_EXISTS
            ]);
            $validator->success = false;
        }

        if (!$validator->success) {
            throw new IncorrectDataException(
                $this->validator->errors,
                'Insert failed. Invalid params given.'
            );
        }

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
        $this->validator->validateByFields([
            'user_name' => $userName
        ]);

        return $this->validator->success && boolval(
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
}