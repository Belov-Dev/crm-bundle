<?php

namespace A2Global\CRMBundle\FieldType;

interface FieldTypeInterface
{
    public function getName(): string;

    public function getEntityClassProperty(): array;

    public function getEntityClassMethods(): array;

    public function getType(): string;
}