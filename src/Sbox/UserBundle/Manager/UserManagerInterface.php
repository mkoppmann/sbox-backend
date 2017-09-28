<?php

namespace Sbox\UserBundle\Manager;

use Sbox\UserBundle\Entity\User;

interface UserManagerInterface
{
    /**
     * Finds exactly one user by the given criteria.
     *
     * @param array $criteria
     * @return User|null
     */
    public function findUserBy(array $criteria): ?User;

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $name
     * @param bool $isSuperAdmin
     * @return User
     */
    public function createUser(
        string $username,
        string $password,
        string $email,
        string $name,
        bool $isSuperAdmin
    ): User;

    /**
     * @return User[]
     */
    public function getUsers(): array;
}
