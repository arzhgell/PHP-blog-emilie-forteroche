<?php

/**
 * User Entity
 */ 
class User extends AbstractEntity 
{
    private string $login;
    private string $password;

    /**
     * Setter for the login.
     * @param string $login
     */
    public function setLogin(string $login) : void 
    {
        $this->login = $login;
    }

    /**
     * Getter for the login.
     * @return string
     */
    public function getLogin() : string 
    {
        return $this->login;
    }

    /**
     * Setter for the password.
     * @param string $password
     */
    public function setPassword(string $password) : void 
    {
        $this->password = $password;
    }

    /**
     * Getter for the password.
     * @return string
     */
    public function getPassword() : string 
    {
        return $this->password;
    }

    /**
     * Prepare the object for serialization
     * @return array
     */
    public function __sleep() 
    {
        return ['id', 'login', 'password'];
    }

    /**
     * Reconstruct the object after deserialization
     */
    public function __wakeup() 
    {
        // Nothing special to do here, as properties are already set
    }
}