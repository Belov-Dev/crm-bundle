<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;

class EntityFieldsDatasheet extends ArrayDatasheet
{
    protected $actionTemplate = '@A2CRM/entity/entity.fields.action.html.twig';

    protected $entityManager;

    protected $itemsPerPage = 50;

    /** @var Entity */
    protected $entity;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setEntity($entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function build(int $startFrom = 0, int $limit = 0, $sort = [], $filters = [])
    {
        $items = [];

        /** @var EntityField $field */
        foreach ($this->entity->getFields() as $field) {
            $item = [
                'id' => $field->getId(),
                'title' => $field->getName(),
                'type' => $field->getType(),
                'show' => $field->getShowInDatasheet(),
                'entityName' => StringUtility::toSnakeCase($this->entity->getName()),
            ];
            $items[] = $item;
        }

        $this->setItems($items);
    }

    public function getFields()
    {
        return [
            'title' => [
                'title' => 'Name',
            ],
            'type' => [
                'title' => 'Type',
            ],
            'show' => [
                'title' => 'Show',
            ],
        ];
    }
}