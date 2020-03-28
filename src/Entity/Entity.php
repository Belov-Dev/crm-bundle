<?php

namespace A2Global\CRMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="A2Global\CRMBundle\Repository\EntityRepository")
 * @ORM\Table(name="a2crm_entities")
 */
class Entity
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameCamelCase;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameUnderScore;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNameCamelCase(): ?string
    {
        return $this->nameCamelCase;
    }

    public function setNameCamelCase(string $nameCamelCase): self
    {
        $this->nameCamelCase = $nameCamelCase;

        return $this;
    }

    public function getNameUnderScore(): ?string
    {
        return $this->nameUnderScore;
    }

    public function setNameUnderScore(string $nameUnderScore): self
    {
        $this->nameUnderScore = $nameUnderScore;

        return $this;
    }
}
