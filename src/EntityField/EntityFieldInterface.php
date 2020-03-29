<?php

namespace A2Global\CRMBundle\EntityField;

interface EntityFieldInterface
{
    public function getNameOriginal(): string;

    public function getNameReadable(): string;

    public function getNameFriendly(): string;

    public function getNameSnakeCase(): string;

    public function getNameCamelCase(): string;

    public function getNameSnakeCasePlural(): string;

    public function getNamePascalCase(): string;

    public function getMySQLFieldType(): string;
}