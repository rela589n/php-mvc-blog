<?php


namespace model;


use core\database\DBDriverInterface;

class User extends Base
{
    public function __construct(DBDriverInterface $db)
    {
        parent::__construct($db, 'users', 'id_user');
    }

    /**
     * @param string $userName
     * @return bool
     */
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

//    /**
//     * Accept array consisting of 'user_name' field <br>
//     * and 'password' - HASHED PASSWORD
//     * @param array $params
//     * @return mixed
//     */
//    public function insert(array $params)
//    {
//        return parent::insert([
//            'user_name' => $params['user_name'],
//            'password' => $params['password']
//        ]);
//    }
}