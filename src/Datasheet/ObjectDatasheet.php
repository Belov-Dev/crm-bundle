<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class ObjectDatasheet implements DatasheetInterface
{
    protected $entityManager;

    /** @var Entity */
    protected $entity;

    protected $fields;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;
        $this->buildFields();

        return $this;
    }

    public function getItems(int $startFrom = 0, int $limit = 0)
    {
        $items = [];
        $objects = $this->entityManager
            ->getRepository('App:' . StringUtility::toPascalCase($this->entity->getName()))
            ->findBy([], [], $limit, $startFrom);

        foreach ($objects as $object) {
            $item = ['id' => $object->getId()];

            foreach ($this->fields as $fieldName => $field) {
                $value = $object->{'get' . $fieldName}();

                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                } elseif ($value instanceof DateTimeInterface) {
                    $value = $value->format('j/m/Y');
                } elseif (is_object($value)) {
                    if (!method_exists($value, '__toString')) {
                        $value = StringUtility::normalize(StringUtility::getShortClassName($value)) . ' #' . $value->getId();
                    }
                }
                $item[$fieldName] = $value;
            }
            $items[] = $item;
        }

        return $items;
    }

    public function getItemsTotal()
    {
        return $this->entityManager
            ->getRepository('App:' . StringUtility::toPascalCase($this->entity->getName()))
            ->createQueryBuilder('e')
            ->select('count(e)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function buildFields()
    {
        /** @var EntityField $field */
        foreach ($this->entity->getFields() as $field) {
            $this->fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => $field->getName(),
                'entity' => $field,
            ];
        }
    }
}