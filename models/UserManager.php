<?php

/** 
 * Manages user authentication
 */

class UserManager extends AbstractEntityManager 
{
    /**
     * Gets user by login
     */
    public function getUserByLogin(string $login) : ?User 
    {
        $sql = "SELECT * FROM user WHERE login = :login";
        $result = $this->db->query($sql, ['login' => $login]);
        $user = $result->fetch();
        if ($user) {
            return new User($user);
        }
        return null;
    }
}