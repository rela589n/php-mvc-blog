<?php


namespace model;


use core\DBDriverInterface;
use core\exceptions\IncorrectDataException;
use core\exceptions\ValidatorException;
use core\Validator;

class Users extends Base
{
    const PASSWORD_MIN_LENGTH = 5;
    const TOO_SHORT_PASSWORD = 'Пароль слишком короткий! Минимальная длина = ' . self::PASSWORD_MIN_LENGTH;
    const USER_NAME_MIN_LENGTH = 5;
    const TOO_SHORT_USER_NAME = 'Имя пользователя слишком короткое! Минимальная длина = ' . self::USER_NAME_MIN_LENGTH;

    const USER_ALREADY_EXISTS = 'Пользователь с таким логином уже существует!';
    const REGISTRATION_SUCCESSFUL = 'Регистрация успешна! Пожалуйста войдите.';

    const HASH_SALT = '@2|\4/1*&';

    private const SCHEMA = [
        'id_user' => [
            'type' => 'int',
        ],
        'user_name' => [
            'type' => 'string',
            'min_length' => 4,
            'max_length' => 64,
            'preg_match' => '/^[0-9a-z_\-]+$/i',
            'preg_match_message' => 'Имя пользователя может содержать только латинские буквы, цифры, дефис и нижнее подчёркивание!',
            'required' => true
        ],
        'password' => [
            'type' => 'string',
            'min_length' => 5,
            'max_length' => 50,
            'required' => true
        ],
        'password_confirm' => [
            'equals_to' => 'password',
            'equals_to_message' => 'Внимание! Пароли не идентичны!',
            'required' => true
        ]
    ];


    public function __construct(DBDriverInterface $db, Validator $validator)
    {
        parent::__construct($db, $validator, 'users', 'id_user');
        $this->validator->setSchema(self::SCHEMA);
    }

    /**
     * @param string $userName
     * @param string $password
     * @param string $rePassword
     * @return mixed
     * @throws IncorrectDataException
     * @throws ValidatorException
     */
    public function register(string $userName, string $password, string $rePassword)
    {
        $validator = &$this->validator;
        $validator->validateByFields([
            'user_name' => $userName,
            'password' => $password,
            'password_confirm' => $rePassword
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
            'password' => self::hashPassword($password)
        ]);
    }

    /**
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password)
    {
        return hash("sha512", $password . self::HASH_SALT);
    }

    /**
     * @param string $userName
     * @return bool
     * @throws ValidatorException
     */
    public function exists(string $userName)
    {
        return $this->validator->validateByFields([
                'user_name' => $userName
            ])->success &&
            boolval(
                $this->db->read(
                    "select exists(select id_user from {$this->tableName} where user_name = :user) as user_exist",
                    $this->db::FETCH_ONE,
                    [
                        'user' => $userName
                    ]
                )['user_exist']
            );
    }

    /**
     * @param string $userName
     * @return mixed
     */
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