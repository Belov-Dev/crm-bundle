<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\Common\Annotations\Annotation\Required;
use Twig\Environment;

abstract class AbstractField implements FieldInterface
{
    const INDENT = "\t";

    protected $name;

    /** @var Environment */
    protected $twig;

    /**
     * @Required
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
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
}