<?php

namespace A2Global\CRMBundle\Component\Field;

interface FieldInterface
{
    public function getName(): string;

    public function setName($name): self;

    public function getType(): string;

    public function getEntityClassProperty(): array;

    public function getEntityClassMethods(): array;
}