<?php

namespace A2Global\CRMBundle\Modifier;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Utility\StringUtility;

class EntityNamesModifier
{
    public function updateNames(Entity $entity): Entity
    {
        $names = StringUtility::variate($entity->getNameOriginal());

        return $entity
            ->setNameOriginal($names['readable'])
            ->setNameReadable($names['readable'])
            ->setNameReadablePlural(StringUtility::pluralize($names['readable']))
            ->setNameCamelCase($names['camelCase'])
            ->setNamePascalCase($names['pascalCase'])
            ->setNameSnakeCase($names['snakeCase'])
            ->setNameSnakeCasePlural($names['snakeCasePlural']);
    }
}