<?php

namespace A2Global\CRMBundle\EntityField;

interface EntityFieldInterface
{
    public function getName(): string;

    public function getFriendlyName(): string;

    public function getMySQLFieldType(): string;
}