<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Component\Field\FieldInterface;
use A2Global\CRMBundle\Component\Field\IdField;
use A2Global\CRMBundle\Component\Field\RelationField;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;

class qbDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    protected $entityInfoProvider;

    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports(): bool
    {
        return $this->getDatasheet()->getData() instanceof QueryBuilder;
    }

    public function getItems(): array
    {
        $query = $this
            ->cloneQbFiltered()
            ->setFirstResult($this->datasheet->getPage() * $this->datasheet->getItemsPerPage())
            ->setMaxResults($this->datasheet->getItemsPerPage())
            ->getQuery()
            ->getSQL();
        echo $query;

        return $this
            ->cloneQbFiltered()
            ->setFirstResult($this->datasheet->getPage() * $this->datasheet->getItemsPerPage())
            ->setMaxResults($this->datasheet->getItemsPerPage())
            ->getQuery()
            ->getResult();
    }

    public function getItemsTotal(): int
    {
        return $this
            ->cloneQbFiltered()
            ->select(sprintf('count(%s)', $this->getQbMainAlias()))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFields(): array
    {
        /** @var From $firstFrom */
        $firstFrom = $this->datasheet->getQueryBuilder()->getDQLPart('from')[0];
        $entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));
        $fields = [];

        foreach ($entity->getFields() as $entityField) {
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

    protected function cloneQbFiltered(): QueryBuilder
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
}