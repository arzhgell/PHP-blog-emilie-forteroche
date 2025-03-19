<?php

class User extends AbstractEntity 
{
    private string $login;
    private string $password;

    public function setLogin(string $login) : void 
    {
        $this->login = $login;
    }

    public function getLogin() : string 
    {
        return $this->login;
    }

    public function setPassword(string $password) : void 
    {
        $this->password = $password;
    }

    public function getPassword() : string 
    {
        return $this->password;
    }

    public function __sleep() 
    {
        return ['id', 'login', 'password'];
    }

}