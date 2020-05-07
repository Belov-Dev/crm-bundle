<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\DatasheetExtended;
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

    public function supports(DatasheetExtended $datasheet): bool
    {
        return count($datasheet->getQueryBuilder()->getDQLPart('select')) < 2;
    }

    public function getItems(DatasheetExtended $datasheet): array
    {
        $query = $this
            ->cloneQueryBuilder($datasheet, true)
            ->setFirstResult($datasheet->getPage() * $datasheet->getItemsPerPage())
            ->setMaxResults($datasheet->getItemsPerPage())
            ->getQuery()
            ->getSQL();
//        echo $query;

        return $this->cloneQueryBuilder($datasheet, true)
            ->setFirstResult($datasheet->getPage() * $datasheet->getItemsPerPage())
            ->setMaxResults($datasheet->getItemsPerPage())
            ->getQuery()
            ->getResult();
    }

    public function getItemsTotal(DatasheetExtended $datasheet): int
    {
        return $this->cloneQueryBuilder($datasheet, true)
            ->select(sprintf('count(%s)', $this->getQueryBuilderMainAlias($datasheet->getQueryBuilder())))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFields(DatasheetExtended $datasheet): array
    {
        /** @var From $firstFrom */
        $firstFrom = $datasheet->getQueryBuilder()->getDQLPart('from')[0];
        $entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));
        $fields = [];

        foreach ($entity->getFields() as $field) {
            $fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => StringUtility::normalize($field->getName()),
            ];
        }
        return $fields;
    }
}