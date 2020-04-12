<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Provider\EntityInfoProvider;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

interface FieldInterface
{
    public function getName(): string;

    public function setName($name): self;

    public function getType(): string;

    public function getEntityClassConstant(): array;

    public function getEntityClassProperty(): array;

    public function getEntityClassMethods(): array;

    public function setEntityManager(EntityManagerInterface $entityManager): self;

    public function setEntityInfoProvider(EntityInfoProvider $entityInfoProvider): self;

    public function setTwig(Environment $twig): self;

    public function getFormControl($value);
}