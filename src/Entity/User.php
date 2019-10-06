<?php

namespace App\Entity;

use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * This file has been generated by the SonataEasyExtendsBundle.
 *
 * @link https://sonata-project.org/easy-extends
 *
 * References:
 * @link http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @ORM\Entity()
 * @ORM\Table(name="mb_user_user")
 */
class User extends BaseUser
{
    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Hook on pre-persist operations.
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        parent::prePersist();
        // Update FOS user roles (array is generated in getter function)
        $this->setRoles($this->getRoles());
    }

    /**
     * Hook on pre-update operations.
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        parent::preUpdate();
        // Update FOS user roles (array is generated in getter function)
        $this->setRoles($this->getRoles());
    }
}