<?php

namespace A2Global\CRMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="A2Global\CRMBundle\Repository\EntityFieldRepository")
 * @ORM\Table(name="a2crm_entity_fields")
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
     * @ORM\Column(type="string", length=255)
     */
    private $configuration;

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
        return json_decode($this->configuration ?? '[]', true);
    }

    public function setConfiguration($configuration = []): self
    {
        $this->configuration = json_encode($configuration);

        return $this;
    }
}