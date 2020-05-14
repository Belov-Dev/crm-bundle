<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Component\Field\FieldInterface;
use A2Global\CRMBundle\Component\Field\IdField;
use A2Global\CRMBundle\Component\Field\RelationField;
use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\ArrayUtility;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    protected $entityInfoProvider;

    protected $entityManager;

    protected $entity;

    protected $joined = [];

    protected $queryBuilder;

    public function __construct(EntityManagerInterface $entityManager, EntityInfoProvider $entityInfoProvider)
    {
        $this->entityManager = $entityManager;
        $this->entityInfoProvider = $entityInfoProvider;
    }

    public function supports(): bool
    {
        return $this->getDatasheet()->getData() instanceof QueryBuilder;
    }

    public function build($page = null, $itemsPerPage = null, $filters = [])
    {
        if ($page) {
            $this->getDatasheet()->setPage($page);
        }

        if ($itemsPerPage) {
            $this->getDatasheet()->setItemsPerPage($itemsPerPage);
        }
        $this->getDatasheet()->setFilters($filters);
        $this->queryBuilder = clone $this->getDatasheet()->getData();
        $this->initEntity();
        $this->buildFields();

        foreach ($filters as $filterField => $filterValue) {
            if (!trim($filterValue)) {
                continue;
            }
            $this->getQueryBuilder()
                ->andWhere(sprintf('%s.%s = :filter%s', $this->getBaseAlias(), $filterField, $filterField))
                ->setParameter('filter' . $filterField, $filterValue);
        }

        $sql = (clone $this->getQueryBuilder())
            ->setFirstResult(($this->getDatasheet()->getPage() - 1) * $this->getDatasheet()->getItemsPerPage())
            ->setMaxResults($this->getDatasheet()->getItemsPerPage())
            ->getQuery()
            ->getSQL();
        $this->getDatasheet()->addDebug($sql);

        $total = (clone $this->getQueryBuilder())
            ->resetDQLPart('select')
            ->addSelect(sprintf('COUNT(%s)', $this->getBaseAlias()))
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        $items = $this->getQueryBuilder()
            ->setFirstResult(($this->getDatasheet()->getPage() - 1) * $this->getDatasheet()->getItemsPerPage())
            ->setMaxResults($this->getDatasheet()->getItemsPerPage())
            ->getQuery()
            ->getArrayResult();

        $replaceNestedFields = [];

        foreach ($this->getDatasheet()->getFields() as $fieldName => $field) {
            if (strpos($fieldName, '.')) {
                $replaceNestedFields[$fieldName] = str_replace('.', self::NEST_SEPARATOR, $fieldName);
            }
        }
        if (count($replaceNestedFields) > 0) {
            $replaceNestedFields = array_flip($replaceNestedFields);

            for ($i = 0; $i < count($items); $i++) {
                foreach ($replaceNestedFields as $from => $to) {
                    $items[$i] = ArrayUtility::renameKey($items[$i], $from, $to);
                }
            }
        }

        $this->getDatasheet()
            ->setItems($items)
            ->setItemsTotal($total)
            ->setHasFilters(true);

        parent::build($page, $itemsPerPage, $filters);
    }

    public function getFields(): array
    {
        $fields = [];

        foreach ($this->getEntity()->getFields() as $entityField) {
            $fieldName = StringUtility::toCamelCase($entityField->getName());
            $fields[$fieldName] = [
                'title' => StringUtility::normalize($entityField->getName()),
                'hasFilter' => $this->hasFilter($entityField),
            ];
        }

        return $fields;
    }

    protected function buildFields()
    {
        $selects = $this->getQueryBuilder()->getDQLPart('select');

        if (count($selects) == 1) {
            $this->getQueryBuilder()->resetDQLPart('select');

            // Fields was not defined, no selects in query, so get all fields of base from
            foreach ($this->getEntity()->getFields() as $field) {
                if ($field instanceof RelationField) {
                    $this->join($this->getQueryBuilder(), $field->getName());
                    $targetEntity = $this->entityInfoProvider->getEntity($field->getTargetEntity());
                    $titleField = $this->getTitleField($targetEntity);
                    $this->getQueryBuilder()->addSelect(sprintf(
                        '%s.%s AS %s%s%s',
                        StringUtility::toCamelCase($field->getName()),
                        $titleField,
                        StringUtility::toCamelCase($field->getName()),
                        self::NEST_SEPARATOR,
                        StringUtility::toCamelCase($titleField)
                    ));
                    $fieldName = sprintf('%s.%s', StringUtility::toCamelCase($field->getName()), $titleField);
                    $fieldTitle = StringUtility::normalize(sprintf('%s %s', $field->getName(), $titleField));
                } else {
                    $this->getQueryBuilder()->addSelect(
                        sprintf('%s.%s', $this->getBaseAlias(), StringUtility::toCamelCase($field->getName()))
                    );
                    $fieldName = StringUtility::toCamelCase($field->getName());
                    $fieldTitle = StringUtility::normalize($field->getName());
                }
                $fields[$fieldName] = [
                    'title' => $this->getDatasheet()->getFieldOptions()[$fieldName]['title'] ?? $fieldTitle,
                    'hasFilter' => false,
                ];
            }
            $this->getDatasheet()->setFields($fields);
        } else {
            // Fields was defined by multiple ->addSelect()
            array_shift($selects);
            $newSelects = [];
            $fields = [];

            /** @var Select $select */
            foreach ($selects as $select) {
                $firstPart = $select->getParts()[0];
                preg_match("/^([^\.]+)\.([^\s]+)($|\sAS\s(.+)$)/iUs", $firstPart, $result);
                $select = [
                    'alias' => $result[1],
                    'field' => $result[2],
                    'as' => $result[4] ?? null,
                ];

                if ($select['alias'] == $this->getBaseAlias()) {
                    $fieldName = $select['field'];
                    $newSelects[] = $firstPart;
                } else {
                    foreach ($this->getQueryBuilder()->getDQLPart('join') as $join) {
                        /** @var Join $firstJoin */
                        $firstJoin = reset($join);

                        if ($select['alias'] == $firstJoin->getAlias()) {
                            $tmp = explode('.', $firstJoin->getJoin());
                            $fieldName = sprintf('%s.%s', $tmp[1], $select['field']);
                            $sqbFieldName = sprintf('%s%s%s', $tmp[1], self::NEST_SEPARATOR, $select['field']);
                        }
                    }
                    $newSelects[] = sprintf('%s.%s AS %s', $select['alias'], $select['field'], $sqbFieldName);
                }
                $fields[$fieldName] = [
                    'title' => StringUtility::normalize($fieldName),
                ];
            }
            $this->getDatasheet()->setFields($fields);
            $this->getQueryBuilder()->resetDQLPart('select');

            foreach ($newSelects as $newSelect) {
                $this->getQueryBuilder()->addSelect($newSelect);
            }
        }

        if ($this->getDatasheet()->getFieldsToShow()) {
            $fields = [];

            foreach ($this->getDatasheet()->getFieldsToShow() as $fieldToShow) {
                $fields[$fieldToShow] = [
                    'title' => StringUtility::normalize($fieldToShow),
                ];
            }
        }

        $this->getDatasheet()->setFields($this->addFilterChoices($fields));
    }

    protected function addFilterChoices($fields)
    {
        foreach ($fields as $fieldName => $field) {
            $fields[$fieldName]['hasFilter'] = false;

            if ($fieldName == 'id') {
                continue;
            }

            if (strpos($fieldName, '.')) {
                continue;
            }
            $choices = $this->entityManager->getConnection()->fetchAll(sprintf(
                'SELECT DISTINCT(%s) FROM (%s) ORDER BY %s',
                StringUtility::toSnakeCase($fieldName),
                $this->getEntity()->getTableName(),
                StringUtility::toSnakeCase($fieldName)
            ));
            $choices = array_map(function ($item) {
                return reset($item);
            }, $choices);
            $fields[$fieldName]['filterChoices'] = $choices;
            $fields[$fieldName]['hasFilter'] = true;
        }

        return $fields;
    }

    protected function getBaseAlias(): string
    {
        return $this->getQueryBuilder()->getDQLPart('from')[0]->getAlias();
    }

    protected function getBaseFrom(): string
    {
        return $this->getQueryBuilder()->getDQLPart('from')[0]->getFrom();
    }

    protected function cloneQb(): QueryBuilder
    {
        return clone $this->datasheet->getQueryBuilder();
    }

    protected function getEntity(): Entity
    {
        return $this->entity;
    }

    protected function initEntity()
    {
        /** @var From $firstFrom */
        $firstFrom = $this->getQueryBuilder()->getDQLPart('from')[0];
        $this->entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));
    }

    protected function join(QueryBuilder $queryBuilder, $field)
    {
        // check if was already joined
        $joins = $queryBuilder->getDQLPart('join');
        $queryBuilder->join(
            sprintf('%s.%s', $this->getBaseAlias(), StringUtility::toCamelCase($field)),
            StringUtility::toCamelCase($field)
        );
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    protected function updateQueryBuilderWithSelects(QueryBuilder $queryBuilder)
    {
        $queryBuilder->resetDQLPart('select');

        foreach ($this->getEntity()->getFields() as $field) {
            if ($field instanceof RelationField) {
                $this->join($queryBuilder, $field->getName());
                $targetEntity = $this->entityInfoProvider->getEntity($field->getTargetEntity());
                $titleField = $this->getTitleField($targetEntity);
                $queryBuilder->addSelect(sprintf(
                    '%s.%s AS %s%s',
                    StringUtility::toCamelCase($field->getName()),
                    $titleField,
                    StringUtility::toCamelCase($field->getName()),
                    StringUtility::toPascalCase($titleField)
                ));
            } else {
                $queryBuilder->addSelect(sprintf('%s.%s', $this->getBaseAlias(), StringUtility::toCamelCase($field->getName())));
            }
        }
    }

    protected function getTitleField(Entity $entity)
    {
        foreach (['name', 'title', 'firstName', 'id'] as $fieldName) {
            foreach ($entity->getFields() as $field) {
                if (StringUtility::toCamelCase($field->getName()) == $fieldName) {
                    return $fieldName;
                }
            }
        }

        throw new DatasheetException('Description field not found in entity: ' . $entity->getName());
    }
}