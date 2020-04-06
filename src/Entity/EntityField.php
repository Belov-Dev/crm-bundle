<?php

namespace A2Global\CRMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="A2Global\CRMBundle\Repository\EntityFieldRepository")
 * @ORM\Table(name="crm_entity_fields")
 */
class EntityField
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="A2Global\CRMBundle\Entity\Entity"
     * )
     * @ORM\JoinColumn(
     *     name="entity_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private $entity;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasFiltering;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showInDatasheet;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fixtureType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fixtureOptions;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $configuration;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getConfiguration(): array
    {
        return json_decode($this->configuration, true) ?: [];
    }

    public function setConfiguration($configuration = []): self
    {
        $this->configuration = json_encode($configuration);

        return $this;
    }

    public function hasFiltering(): ?bool
    {
        return $this->hasFiltering;
    }

    public function setHasFiltering($hasFiltering): self
    {
        $this->hasFiltering = $hasFiltering;

        return true;
    }

    public function getShowInDatasheet(): ?bool
    {
        return $this->showInDatasheet;
    }

    public function setShowInDatasheet($showInDatasheet): self
    {
        $this->showInDatasheet = $showInDatasheet;

        return $this;
    }

    public function getFixtureType()
    {
        return $this->fixtureType;
    }

    public function setFixtureType($fixtureType): self
    {
        $this->fixtureType = $fixtureType;

        return $this;
    }

    public function getFixtureOptions()
    {
        return $this->fixtureOptions;
    }

    public function setFixtureOptions($fixtureOptions)
    {
        $this->fixtureOptions = $fixtureOptions;

        return $this;
    }
}
