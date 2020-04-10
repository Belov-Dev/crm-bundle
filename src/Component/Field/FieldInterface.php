<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Provider\EntityInfoProvider;
use Twig\Environment;

interface FieldInterface
{
    public function getName(): string;

    public function setName($name): self;

    public function getType(): string;

    public function getEntityClassConstant(): array;

    public function getEntityClassProperty(): array;

    public function getEntityClassMethods(): array;

    public function setEntityInfoProvider(EntityInfoProvider $entityInfoProvider): self;

    public function setTwig(Environment $twig): self;
}