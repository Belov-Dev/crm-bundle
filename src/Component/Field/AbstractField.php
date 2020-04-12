<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

abstract class AbstractField implements FieldInterface
{
    const INDENT = "\t";

    protected $name;

    /** @var Environment */
    protected $twig;

    /** @var EntityInfoProvider */
    protected $entityInfoProvider;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function setTwig(Environment $twig): FieldInterface
    {
        $this->twig = $twig;

        return $this;
    }

    public function setEntityInfoProvider(EntityInfoProvider $entityInfoProvider): FieldInterface
    {
        $this->entityInfoProvider = $entityInfoProvider;

        return $this;
    }

    public function setEntityManager(EntityManagerInterface $entityManager): FieldInterface
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    public function render($template, $data = []): string
    {
        return $this->twig->render($template, $data);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): FieldInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return strtolower(StringUtility::getShortClassName($this, 'Field'));
    }

    public function getEntityClassConstant(): array
    {
        return [];
    }

    public function getEntityClassProperty(): array
    {
        return [];
    }

    public function getEntityClassMethods(): array
    {
        return [
            'public function get' . StringUtility::toPascalCase($this->getName()) . '()',
            '{',
            self::INDENT . 'return $this->' . StringUtility::toCamelCase($this->getName()) . ';',
            '}',
            '',
            'public function set' . StringUtility::toPascalCase($this->getName()) . '($value): self',
            '{',
            self::INDENT . '$this->' . StringUtility::toCamelCase($this->getName()) . ' = $value;',
            '',
            self::INDENT . 'return $this;',
            '}',
        ];
    }

    public function getFormControl($value = null)
    {
        return '<input type="text" class="form-control" value="' . $value . '">';
    }
}