<?php

namespace Application\Model;

class User
{

    public $id;
    public $username;
    public $password;
    public $realname;
    public $active;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->username = !empty($data['username']) ? $data['username'] : null;
        $this->password = !empty($data['password']) ? $data['password'] : null;
        $this->realname = !empty($data['realname']) ? $data['realname'] : null;
        $this->active = !empty($data['active']) ? $data['active'] : null;
    }

}