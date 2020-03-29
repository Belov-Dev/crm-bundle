<?php

namespace A2Global\CRMBundle\EntityField;

class IntegerField extends AbstractField
{
    public function getNameOriginal(): string
    {
        return 'Integer';
    }

    public function getNameFriendly(): string
    {
        return 'Number';
    }

    public function getMySQLFieldType(): string
    {
        return 'INT';
    }
}