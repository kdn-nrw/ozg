<?php

namespace App\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use ReflectionClass;

/**
 * Class BaseEntity
 */
abstract class BaseEntity implements BaseEntityInterface, TimestampableEntityInterface
{
    use TimestampableEntityTrait;

    /**
     * @var null|int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $reflectionClass = new ReflectionClass($this);
        $classShortName = $reflectionClass->getShortName();

        return sprintf('%s(%d)', $classShortName, $this->getId());
    }
}