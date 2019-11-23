<?php


namespace model;


use core\exceptions\AuthorizationException;
use core\exceptions\IncorrectDataException;

class Authorization
{
    const SESSION_AUTHENTICATED = 'authenticated';
    const SESSION_USER_NAME_KEY = 'user_name';
    const SESSION_USER_ID_KEY = 'user_id';

    const COOKIE_REMEMBER_LOGIN_KEY = 'login';
    const COOKIE_REMEMBER_PASSWORD_KEY = 'password';
    const REMEMBER_TIME = 3600 * 24 * 7;

    private const ADMIN_LOGIN = 'admin';
    private static $isAuth = null;
    private $userModel;

    public function __construct(Users $userModel)
    {
        $this->userModel = $userModel;
    }

    public function authorize(string $login, string $password, bool $remember = false, int $rememberTime = self::REMEMBER_TIME)
    {
        $this->userModel->validator->validateByFields([
            'user_name' => $login,
            'password' => $password
        ]);

        if (!$this->userModel->validator->success) {
            throw new IncorrectDataException($this->userModel->validator->errors);
        }

        $hashPassword = $this->userModel::hashPassword($password);
        $user = $this->userModel->getByName($login);

        $success = $user && $user['password'] === $hashPassword;
        if ($success) {
            $_SESSION[self::SESSION_AUTHENTICATED] = true;
            $_SESSION[self::SESSION_USER_NAME_KEY] = $login;
            $_SESSION[self::SESSION_USER_ID_KEY] = $user['id_user'];

            if ($remember) {
                $rememberTime += time();
                setcookie(self::COOKIE_REMEMBER_LOGIN_KEY, $login, $rememberTime, ROOT);
                setcookie(self::COOKIE_REMEMBER_PASSWORD_KEY, $hashPassword, $rememberTime, ROOT);
            }
        }

        if (!$success) {
            throw new AuthorizationException('Invalid login or password!');
        }
    }

    public static function deauthorize()
    {
        unset($_SESSION[self::SESSION_AUTHENTICATED]);
        unset($_SESSION[self::SESSION_USER_NAME_KEY]);
        unset($_SESSION[self::SESSION_USER_ID_KEY]);

        setcookie(self::COOKIE_REMEMBER_LOGIN_KEY, "", 0, ROOT);
        setcookie(self::COOKIE_REMEMBER_PASSWORD_KEY, "", 0, ROOT);
    }

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

    public function isAdmin($userName)
    {
        return $userName === self::ADMIN_LOGIN && $this->isAuth();
    }
}
