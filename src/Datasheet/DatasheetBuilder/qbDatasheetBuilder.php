<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Component\Field\RelationField;
use A2Global\CRMBundle\Datasheet\DatasheetExtended;
use A2Global\CRMBundle\Datasheet\FilterableEntity;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;

class qbDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    protected $entityInfoProvider;

    protected $entityManager;

    public function __construct(
        EntityInfoProvider $entityInfoProvider,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
        $this->entityManager = $entityManager;
    }

    public function supports(): bool
    {
        return
            $this->datasheet->getQueryBuilder()
            &&
            count($this->datasheet->getQueryBuilder()->getDQLPart('select')) < 2;
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

        foreach ($entity->getFields() as $field) {
            $fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => StringUtility::normalize($field->getName()),
            ];
        }
        return $fields;
    }

    public function hasFilters(): bool
    {
        return false;
    }

    public function getFilters()
    {
        /** @var From $firstFrom */
        $firstFrom = $datasheet->getQueryBuilder()->getDQLPart('from')[0];
        $entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));

        $field = $entity->getField($fieldName);

        if ($field instanceof RelationField) {
            $targetEntityClass = 'App\\Entity\\' . $field->getTargetEntity();
            $targetEntityObject = new $targetEntityClass();

            if (!$targetEntityObject instanceof FilterableEntity) {
                return false;
            }
            $entity = $this->entityInfoProvider->getEntity($targetEntityObject);
            $fieldName = $targetEntityObject->getFilterField();
        }

        $results = $this->entityManager
            ->getConnection()
            ->fetchAll(
                sprintf(
                    'SELECT DISTINCT(%s) FROM %s ORDER BY %s',
                    StringUtility::toSnakeCase($fieldName),
                    $entity->getTableName(),
                    StringUtility::toSnakeCase($fieldName)
                )
            );

        return array_map(function ($result) {
            return reset($result);
        }, $results);
    }

    protected function cloneQbFiltered(): QueryBuilder
    {
        return $this->cloneQb();
        $queryBuilder = clone $this->datasheet->getQueryBuilder();

        if (!$applyFilters) {
            return $queryBuilder;
        }

        return $queryBuilder;
        foreach ($datasheet->getFilters() as $fieldName => $value) {
            if (!trim($value)) {
                continue;
            }

            if (strstr($fieldName, DatasheetExtended::NEST_SEPARATOR)) {
                $parentAlias = $this->getQueryBuilderMainAlias();
                $path = explode(DatasheetExtended::NEST_SEPARATOR, $fieldName);
                $targetFieldName = array_pop($path);

                foreach ($path as $relation) {
                    if (!$this->isAlreadyJoined($queryBuilder, $relation)) {
                        $queryBuilder->join(sprintf('%s.%s', $parentAlias, $relation), $relation);
                    }
                    $parentAlias = $relation;
                }
                $queryBuilder
                    ->andWhere(sprintf('%s.%s = :%sFilter', $parentAlias, $targetFieldName, $fieldName))
                    ->setParameter($fieldName . 'Filter', $value);
            } else {
                $queryBuilder
                    ->andWhere(sprintf(
                        '%s.%s = :%sFilter',
                        $this->getQueryBuilderMainAlias($datasheet->getQueryBuilder()),
                        $fieldName,
                        $fieldName
                    ))
                    ->setParameter($fieldName . 'Filter', $value);
            }
        }

        return $queryBuilder;
    }
}