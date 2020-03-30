<?php

namespace A2Global\CRMBundle\EntityField;

class BooleanField extends AbstractField
{
    public function getName(): string
    {
        return 'Boolean';
    }

    public function getFriendlyName(): string
    {
        return 'Boolean: True/False';
    }

    public function getMySQLFieldType(): string
    {
        return 'TINYINT(1)';
    }
}