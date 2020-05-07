<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Datasheet\DatasheetBuilder;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\Query\Expr\From;

class SimpleQueryBuilderAdapter implements DatasheetAdapterInterface
{
    use QueryBuilderAdapterTrait;

    private $entityInfoProvider;

    public function __construct(EntityInfoProvider $entityInfoProvider)
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }

    public function supports(Datasheet $datasheet): bool
    {
        return count($datasheet->queryBuilder->getDQLPart('select')) < 2;
    }

    public function buildItems(Datasheet $datasheet, $page = 0, $perPage = 15, $filters = []): array
    {
        $query = $this
            ->cloneQueryBuilder($datasheet->queryBuilder, true)
            ->setFirstResult($page * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getSQL();
//        echo $query;

        $items = $this->cloneQueryBuilder($datasheet->queryBuilder, true)
            ->setFirstResult($page * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return $items;
    }

    public function buildFields(Datasheet $datasheet): array
    {
        /** @var From $firstFrom */
        $firstFrom = $datasheet->queryBuilder->getDQLPart('from')[0];
        $entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));
        $fields = [];

        foreach ($entity->getFields() as $field) {
            $fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => StringUtility::normalize($field->getName()),
            ];
        }

        return $fields;
    }

    public function buildItemsTotal(Datasheet $datasheet): int
    {
        return $this->cloneQueryBuilder($datasheet->queryBuilder, true)
            ->select(sprintf('count(%s)', $this->getQueryBuilderMainAlias($datasheet->queryBuilder)))
            ->getQuery()
            ->getSingleScalarResult();
    }
}