<?php

namespace Wandi\EasyAdminPlusBundle\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * UserRepository.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository implements UserLoaderInterface
{
    /**
     * Find user by username.
     *
     * @param string $username
     *
     * @return null|object
     */
    public function loadUserByUsername($username)
    {
        return $this->findOneBy([
            'username' => $username,
            'enabled' => true,
        ]);
    }
}
