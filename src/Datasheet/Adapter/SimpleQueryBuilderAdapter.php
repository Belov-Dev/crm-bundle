<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\Query\Expr\From;

class SimpleQueryBuilderAdapter implements DatasheetAdapterInterface
{
    private $entityInfoProvider;

    public function __construct(EntityInfoProvider $entityInfoProvider)
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }

    public function supports(Datasheet $datasheet): bool
    {
        return count($datasheet->queryBuilder->getDQLPart('select')) < 2;
    }

    public function getFields(Datasheet $datasheet)
    {
        /** @var From $firstFrom */
        $firstFrom = $datasheet->queryBuilder->getDQLPart('from')[0];
        $entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));
        $fields = [];

        foreach($entity->getFields() as $field){
            $fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => $field->getName(),
            ];
        }

        return $fields;
    }
}