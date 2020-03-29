<?php

namespace A2Global\CRMBundle\EntityField;

class BooleanField extends AbstractField
{
    public function getNameOriginal(): string
    {
        return 'Boolean';
    }

    public function getNameFriendly(): string
    {
        return 'Boolean: True/False';
    }

    public function getMySQLFieldType(): string
    {
        return 'TINYINT(1)';
    }
}