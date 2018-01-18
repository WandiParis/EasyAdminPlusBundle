<?php

namespace Wandi\EasyAdminPlusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Admin.
 *
 * @ORM\Table(name="wandi_easy_admin_plus_user")
 * @ORM\Entity(repositoryClass="Wandi\EasyAdminPlusBundle\Repository\UserRepository")
 * @UniqueEntity(fields={"username"})
 */
class User implements UserInterface
{
    const ROLE_EASY_ADMIN = 'ROLE_EASY_ADMIN';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", unique=true, length=64)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="json_array")
     */
    protected $roles;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->roles = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt()
    {
        return;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = static::ROLE_EASY_ADMIN;

        return array_values(array_unique($roles));
    }

    /**
     * @param $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @param $role
     *
     * @return $this
     */
    public function addRole($role)
    {
        if ($role === static::ROLE_EASY_ADMIN) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $boolean
     *
     * @return $this
     */
    public function setEnabled($boolean)
    {
        $this->enabled = (bool) $boolean;

        return $this;
    }
}
