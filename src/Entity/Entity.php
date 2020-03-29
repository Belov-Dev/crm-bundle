<?php

namespace A2Global\CRMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="a2crm_entities")
 */
class Entity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameOriginal;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameReadable;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameReadablePlural;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameCamelCase;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameSnakeCase;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameSnakeCasePlural;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $namePascalCase;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameOriginal(): ?string
    {
        return $this->nameOriginal;
    }

    public function setNameOriginal(string $nameOriginal): self
    {
        $this->nameOriginal = $nameOriginal;

        return $this;
    }

    public function getNameReadable(): ?string
    {
        return $this->nameReadable;
    }

    public function setNameReadable(string $nameReadable): self
    {
        $this->nameReadable = $nameReadable;

        return $this;
    }

    public function getNameReadablePlural(): ?string
    {
        return $this->nameReadablePlural;
    }

    public function setNameReadablePlural(string $nameReadablePlural): self
    {
        $this->nameReadablePlural = $nameReadablePlural;

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

    public function getNameSnakeCase(): ?string
    {
        return $this->nameSnakeCase;
    }

    public function setNameSnakeCase(string $nameSnakeCase): self
    {
        $this->nameSnakeCase = $nameSnakeCase;

        return $this;
    }

    public function getNameSnakeCasePlural(): ?string
    {
        return $this->nameSnakeCasePlural;
    }

    public function setNameSnakeCasePlural(string $nameSnakeCasePlural): self
    {
        $this->nameSnakeCasePlural = $nameSnakeCasePlural;

        return $this;
    }

    public function getNamePascalCase(): ?string
    {
        return $this->namePascalCase;
    }

    public function setNamePascalCase(string $namePascalCase): self
    {
        $this->namePascalCase = $namePascalCase;

        return $this;
    }
}
