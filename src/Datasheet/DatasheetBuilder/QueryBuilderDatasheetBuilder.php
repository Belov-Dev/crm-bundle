<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Component\Field\FieldInterface;
use A2Global\CRMBundle\Component\Field\IdField;
use A2Global\CRMBundle\Component\Field\RelationField;
use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
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
        if($page){
            $this->getDatasheet()->setPage($page);
        }

        if($itemsPerPage){
            $this->getDatasheet()->setItemsPerPage($itemsPerPage);
        }
        $this->queryBuilder = clone $this->getDatasheet()->getData();
        $this->buildFields();

        $sql = (clone $this->getQueryBuilder())
            ->setFirstResult(($this->getDatasheet()->getPage() - 1) * $this->getDatasheet()->getItemsPerPage())
            ->setMaxResults($this->getDatasheet()->getItemsPerPage())
            ->getQuery()
            ->getSQL();

        $total = (clone $this->getQueryBuilder())
            ->resetDQLPart('select')
            ->addSelect(sprintf('COUNT(%s)', $this->getBaseAlias()))
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        $result = $this->getQueryBuilder()
            ->setFirstResult(($this->getDatasheet()->getPage() - 1) * $this->getDatasheet()->getItemsPerPage())
            ->setMaxResults($this->getDatasheet()->getItemsPerPage())
            ->getQuery()
            ->getArrayResult();

        $this->getDatasheet()->setItems($result);
        $this->getDatasheet()->setItemsTotal($total);

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

    public function getFilters($fieldName)
    {
        if ($this->getEntity()->getField($fieldName) instanceof RelationField) {
            $tableName = $this->entityInfoProvider
                ->getEntity($this->getEntity()->getField($fieldName)->getTargetEntity())
                ->getTableName();
            $fieldName = $this->datasheet->getFieldOptions($fieldName)['filterBy'];
        } else {
            $tableName = $this->getEntity()->getTableName();
            $fieldName = StringUtility::toSnakeCase($fieldName);
        }

        $results = $this->entityManager
            ->getConnection()
            ->fetchAll(sprintf('SELECT DISTINCT(%s) FROM %s ORDER BY %s', $fieldName, $tableName, $fieldName));

        return array_map(function ($result) {
            return reset($result);
        }, $results);
    }

    protected function buildFields()
    {
        // Fields was defined by ShowFields method

//        if ($this->getDatasheet()->getFieldsToShow()) {
//
//            foreach ($this->getDatasheet()->getFieldsToShow() as $field) {
//                $fields[StringUtility::toCamelCase($field)] = [
//                    'title' => StringUtility::normalize($field),
//                    'hasFilter' => false,
//                ];
//            }
//            return $fields;
//        }
        $selects = $this->getQueryBuilder()->getDQLPart('select');

        if (count($selects) == 1) {
            // Fields was not defined, no selects in query, so get all fields of base from
            $fields = [];

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
                    'title' => $fieldTitle,
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
                    'hasFilter' => false,
                ];
            }
            $this->getDatasheet()->setFields($fields);
            $this->getQueryBuilder()->resetDQLPart('select');

            foreach($newSelects as $newSelect){
                $this->getQueryBuilder()->addSelect($newSelect);
            }
        }

        foreach ($this->getEntity()->getFields() as $field) {
            $fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => StringUtility::normalize($field->getName()),
                'hasFilter' => false,
            ];
        }
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
        if (!$this->entity) {
            /** @var From $firstFrom */
            $firstFrom = $this->getQueryBuilder()->getDQLPart('from')[0];
            $this->entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));
        }

        return $this->entity;
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

    protected function getQueryBuilderFiltered(): QueryBuilder
    {

        $this->joined = [];
        $queryBuilder = $this->cloneQb();

        if (count($this->datasheet->getFilters()) < 1) {
            return $queryBuilder;
        }

        foreach ($this->datasheet->getFilters() as $fieldName => $value) {
            if (!trim($value)) {
                continue;
            }
            $field = $this->getEntity()->getField($fieldName);

            if ($field instanceof RelationField) {
                $fieldOptions = $this->datasheet->getFieldOptions($fieldName);
                $this
                    ->join($queryBuilder, $this->getQbMainAlias(), $fieldName)
                    ->andWhere(sprintf('%s.%s = :%sFilter', $fieldName, $fieldOptions['filterBy'], $fieldName))
                    ->setParameter($fieldName . 'Filter', $value);
                continue;
            }

            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%sFilter', $this->getQbMainAlias(), $fieldName, $fieldName))
                ->setParameter($fieldName . 'Filter', $value);
        }

        return $queryBuilder;
    }

    protected function hasFilter(FieldInterface $entityField)
    {
        if ($entityField instanceof IdField) {
            return false;
        }

        if ($entityField instanceof RelationField) {
            $fieldOptions = $this->datasheet->getFieldOptions(StringUtility::toCamelCase($entityField->getName()));

            if (!isset($fieldOptions['filterBy'])) {
                return false;
            }
        }

        return true;
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