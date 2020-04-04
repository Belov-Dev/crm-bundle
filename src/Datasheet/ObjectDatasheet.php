<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ObjectDatasheet extends AbstractDatasheet
{
    protected $actionsTemplate = '@A2CRM/object/object.datasheet.actions.html.twig';

    protected $entityManager;

    /** @var Entity */
    protected $entity;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setEntity(Entity $entity): self
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
        $this->buildFields();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager
            ->getRepository('App:' . StringUtility::toPascalCase($this->entity->getName()))
            ->createQueryBuilder('o');

        if (!empty($filters)) {
            foreach ($filters as $field => $searchString) {
                // TODO MINOR this must be moved to datasheet provider
                if (!trim($searchString)) {
                    continue;
                }
                $queryBuilder
                    ->andWhere(sprintf('o.%s LIKE :%s', $field, $field))
                    ->setParameter($field, sprintf('%%%s%%', $searchString));
            }
        }
        $this->setItemsTotal((clone $queryBuilder)->select('count(o)')->getQuery()->getSingleScalarResult());
        $results = $queryBuilder->setFirstResult($startFrom)->setMaxResults($limit)->getQuery()->getResult();
        $items = [];

        foreach ($results as $object) {
            $item = ['id' => $object->getId()];

            foreach ($this->fields as $fieldName => $field) {
                $value = $object->{'get' . $fieldName}();
                $item[$fieldName] = $this->handleValue($value);
            }
            $items[] = $item;
        }

        $this->setItems($items);
    }

    protected function buildFields()
    {
        $this->fields['id'] = [
            'title' => '#',
        ];

        /** @var EntityField $field */
        foreach ($this->getEntity()->getFields() as $field) {
            if (!$field->getShowInDatasheet()) {
                continue;
            }
            $this->fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => $field->getName(),
                'hasFiltering' => $field->hasFiltering(),
            ];
        }
    }

    protected function handleValue($value)
    {
        if (is_bool($value)) {
            if ($value) {
                return '<span class="badge bg-light-blue">Yes</span>';
            } else {
                return '<span class="badge">No</span>';
            }
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('j/m/Y');
        }

        if (is_object($value)) {
            if (!method_exists($value, '__toString')) {
                return StringUtility::normalize(StringUtility::getShortClassName($value)) . ' #' . $value->getId();
            } else {
                return (string)$value;
            }
        }

        return $value;
    }
}