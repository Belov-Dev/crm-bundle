<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;

class RelationField extends AbstractField implements FieldInterface, ConfigurableFieldInterface
{
    protected $targetEntity;

    /** @var EntityInfoProvider $entityInfoProvider */
    protected $entityInfoProvider;

    /**
     * @Required
     */
    public function setEntityManager(EntityInfoProvider $entityInfoProvider)
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }

    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    public function setTargetEntity($targetEntity): self
    {
        $this->targetEntity = $targetEntity;

        return $this;
    }

    public function getConfigurationsFormControls(Entity $entity = null): string
    {
        $entities = $this->entityInfoProvider->getEntityList();

        $entities = array_filter($entities, function ($targetEntity) use ($entity) {
            return StringUtility::toCamelCase($targetEntity) != StringUtility::toCamelCase($entity->getName());
        });

        return $this->render('@A2CRM/entity/field.relation.configuration.html.twig', [
            'relation' => $this->getTargetEntity(),
            'entities' => $entities,
        ]);
    }

    public function setConfigurationFromTheForm($configuration)
    {
        dd(1);
        $choices = array_map(function ($item) {
            return StringUtility::normalize($item);
        }, $configuration['choices']);

        $this->setChoices($choices);
    }

    public function getEntityClassConstant(): array
    {
        dd(123);
        $elements = [
            'const ' . StringUtility::toConstantName($this->name) . '_CHOICES = [',
        ];

        foreach ($this->choices as $choice) {
            $elements[] = self::INDENT . '\'' . $choice . '\',';
        }

        $elements[] = '];';

        return $elements;
    }

    public function getEntityClassProperty(): array
    {
        dd(123);

        return [
            '/**',
            ' * @ORM\Column(type="string", length=255, nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function getEntityClassMethods(): array
    {
        dd(123);
    }
}