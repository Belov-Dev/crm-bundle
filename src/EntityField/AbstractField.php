<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Utility\StringUtility;

abstract class AbstractField implements EntityFieldInterface
{
    protected $names;

    public function getNameReadable(): string
    {
        return $this->getNames()['readable'];
    }

    public function getNameFriendly(): string
    {
        return $this->getNames()['readable'];
    }

    public function getNameSnakeCase(): string
    {
        return $this->getNames()['snakeCase'];
    }

    public function getNameSnakeCasePlural(): string
    {
        return $this->getNames()['snakeCasePlural'];
    }

    public function getNameCamelCase(): string
    {
        return $this->getNames()['camelCase'];
    }

    public function getNameCamelCasePlural(): string
    {
        return $this->getNames()['camelCasePlural'];
    }

    public function getNamePascalCase(): string
    {
        return $this->getNames()['pascalCase'];
    }

    protected function getNames()
    {
        if(is_null($this->names)){
            $this->names = StringUtility::variate($this->getNameOriginal());
        }

        return $this->names;
    }
}