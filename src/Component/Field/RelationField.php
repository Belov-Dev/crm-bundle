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
        $this->setTargetEntity($configuration['targetEntity']);
    }

    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\ManyToOne(targetEntity="' . StringUtility::toPascalCase($this->getTargetEntity()) . '")',
            ' * @ORM\JoinColumn(name="' . StringUtility::toSnakeCase($this->getTargetEntity()) . '_id", referencedColumnName="id")',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function getFormControl($value = null): string
    {
        $optionsRepository = $this->entityManager->getRepository('App:' . StringUtility::toPascalCase($this->getTargetEntity()));
        $html = [sprintf(
            '<select class="form-control selectpicker" name="data[%s]" data-live-search="true">',
            StringUtility::toCamelCase($this->getName())
        )];
        $html[] = sprintf('<option></option>');

        foreach ($optionsRepository->findAll() as $item) {
            $isSelected = $value && ($value->getId() == $item->getId());
            $html[] = sprintf('<option value="%s" %s>%s', $item->getId(), ($isSelected ? 'selected' : ''), (string)$item);
        }
        $html[] = '</select>';

        return implode(PHP_EOL, $html);
    }

    public function setValueToObject($value, $object): FieldInterface
    {
        $value = $this->entityManager
            ->getRepository('App:' . StringUtility::toPascalCase($this->getTargetEntity()))
            ->find($value);

        return parent::setValueToObject($value, $object);
    }
}