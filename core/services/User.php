<?php


namespace core\services;

use core\exceptions\AuthorizationException;
use core\exceptions\IncorrectDataException;
use core\exceptions\ValidatorException;
use core\Validator;
use model\User as MUser;

class User extends Base
{
    const REGISTRATION_SUCCESSFUL = 'Регистрация успешна! Пожалуйста войдите.';
    const USER_ALREADY_EXISTS = 'Пользователь с таким логином уже существует!';
    const WRONG_LOGIN_OR_PASSWORD = 'Неверное имя пользователя или пароль.';
    const HASH_SALT = '@2|\4/1*&';

    /// Authorization
    const SESSION_AUTHENTICATED = 'authenticated';
    const SESSION_USER_NAME_KEY = 'user_name';
    const SESSION_USER_ID_KEY = 'user_id';

    const COOKIE_REMEMBER_LOGIN_KEY = 'login';
    const COOKIE_REMEMBER_PASSWORD_KEY = 'password';
    const COOKIE_REMEMBER_TIME = 3600 * 24 * 7;

    private const ADMIN_LOGIN = 'admin';

    private const SCHEMA = [
        'id_user' => [
            'type' => 'int',
        ],
        'user_name' => [
            'type' => 'string',
            'min_length' => 4,
            'min_length_message' => 'Имя пользователя слишком короткое! Минимальная длина = %1$d',
            'max_length' => 64,
            'preg_match' => '/^[0-9a-z_\-]+$/i',
            'preg_match_message' => 'Имя пользователя может содержать только латинские буквы, цифры, дефис и нижнее подчёркивание!',
            'required' => true
        ],
        'password' => [
            'type' => 'string',
            'min_length' => 5,
            'min_length_message' => 'Пароль слишком короткий! Минимальная длина = %1$d. Длина сейчас: %2$d.',
            'max_length' => 50,
            'required' => true
        ],
        'password_confirm' => [
            'equals_to' => 'password',
            'equals_to_message' => 'Внимание! Пароли не идентичны!',
            'required' => true
        ]
    ];

    private $userModel;
    private static $isAuth = null;

    /**
     * User constructor.
     * @param MUser $userModel
     * @param Validator $validator
     */
    public function __construct(MUser $userModel, Validator $validator)
    {
        $this->userModel = $userModel;
        parent::__construct($validator);
        $this->validator->setSchema(self::SCHEMA);
    }

    public function signOut() : void
    {
        unset($_SESSION[self::SESSION_AUTHENTICATED]);
        unset($_SESSION[self::SESSION_USER_NAME_KEY]);
        unset($_SESSION[self::SESSION_USER_ID_KEY]);

        setcookie(self::COOKIE_REMEMBER_LOGIN_KEY, "", 0, ROOT);
        setcookie(self::COOKIE_REMEMBER_PASSWORD_KEY, "", 0, ROOT);
    }

    /**
     * @param array $params
     * @throws AuthorizationException
     * @throws IncorrectDataException
     * @throws ValidatorException
     */
    public function signIn(array $params)
    {
        $login = $params['name'];
        $password = $params['password'];
        $remember = !empty($params['remember']);

        $this->params = [
            'user_name' => $login,
            'password' => $password
        ];

        $this->validator->validateByFields($this->params);

        if (!$this->validator->success()) {
            throw new IncorrectDataException($this->validator->errors, self::WRONG_LOGIN_OR_PASSWORD);
        }

        $login = $this->validator->clear['user_name'];
        $password = $this->validator->clear['password'];

        $user = $this->userModel->getByName($login);
        $hashedPassword = self::hashPassword($password);

        $success = $user && $user['password'] === $hashedPassword;

        if (!$success) {
            throw new AuthorizationException(self::WRONG_LOGIN_OR_PASSWORD);
        }

        $_SESSION[self::SESSION_AUTHENTICATED] = true;
        $_SESSION[self::SESSION_USER_NAME_KEY] = $login;
        $_SESSION[self::SESSION_USER_ID_KEY] = $user['id_user'];

        if ($remember) {
            $rememberTime = self::COOKIE_REMEMBER_TIME + time();
            setcookie(self::COOKIE_REMEMBER_LOGIN_KEY, $login, $rememberTime, ROOT);
            setcookie(self::COOKIE_REMEMBER_PASSWORD_KEY, $hashedPassword, $rememberTime, ROOT);
        }

    }

    /**
     * @param array $params
     * @return mixed
     * @throws IncorrectDataException
     * @throws ValidatorException
     */
    public function signUp(array $params)
    {
        $this->validator->validateByFields([
            'user_name' => $params['name'],
            'password' => $params['password'],
            'password_confirm' => $params['password_confirm']
        ]);

        if ($this->validator->success()) {
            $userName = $this->validator->clear['user_name'];

            if ($this->userModel->exists($userName)) {
                $this->validator->appendErrors([
                    'user_name' => self::USER_ALREADY_EXISTS
                ]);
            }
        }

        if (!$this->validator->success()) {
            throw new IncorrectDataException(
                $this->validator->errors,
                'Insert failed. Invalid params given.');
        }

        $clearFields = $this->validator->clear;
        $clearFields['password'] = self::hashPassword($clearFields['password']);
        unset($clearFields['password_confirm']);

        return $this->userModel->insert($clearFields);
    }

    /**
     * @return bool|null
     */
    public function isAuth()
    {
        if (self::$isAuth === null) {
            self::$isAuth = false;

            if ($_SESSION[self::SESSION_AUTHENTICATED] ?? false) {
                self::$isAuth = true;
            } elseif ($userName = $_COOKIE[self::COOKIE_REMEMBER_LOGIN_KEY] ?? false) {
                $user = $this->userModel->getByName($userName);

                if ($user && $user['password'] === ($_COOKIE[self::COOKIE_REMEMBER_PASSWORD_KEY] ?? false)) {
                    $_SESSION[self::SESSION_AUTHENTICATED] = true;
                    $_SESSION[self::SESSION_USER_NAME_KEY] = $userName;
                    $_SESSION[self::SESSION_USER_ID_KEY] = $user['id_user'];

                    self::$isAuth = true;
                };
            }
        }
        return self::$isAuth;
    }

    /**
     * @param $userName
     * @return bool
     */
    public function isAdmin($userName)
    {
        return $userName === self::ADMIN_LOGIN && $this->isAuth();
    }


    /**
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password)
    {
        return hash("sha512", $password . self::HASH_SALT);
    }
}