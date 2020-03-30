<?php

namespace A2Global\CRMBundle\EntityField;

class IntegerField extends AbstractField
{
    public function getName(): string
    {
        return 'Integer';
    }

    public function getFriendlyName(): string
    {
        return 'Number';
    }

    public function getMySQLFieldType(): string
    {
        return 'INT';
    }
}