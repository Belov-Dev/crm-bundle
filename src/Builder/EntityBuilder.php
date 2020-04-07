<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Utility\StringUtility;

class EntityBuilder
{
    const IDENT = "\t";

    protected $entity;

    protected $elements = [];

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function build(): self
    {
        $properties = [];
        $methods = [];

        foreach ($this->getEntity()->getFields() as $field) {
            $properties = array_merge($properties, $this->indentElements($field->getEntityClassProperty()));
            $methods = array_merge($methods, $this->indentElements($field->getEntityClassMethods()));
        }

        return $this
            ->addElements($this->getBaseElements())
            ->addElements($properties)
            ->addElements($methods)
            ->addElements($this->getFinalElements());
    }

    public function getFileContent(): string
    {
        $this->build();

        return implode(PHP_EOL, $this->elements);
    }

    protected function addElements(array $elements): self
    {
        $this->elements = array_merge($this->elements, $elements);

        return $this;
    }

    protected function getBaseElements(): array
    {
        $entityOptions = [];
//        $repositoryClassName = sprintf('App\\Repository\\%sRepository', StringUtility::toPascalCase($entity->getName()));
//
//        if (class_exists($repositoryClassName)) {
//            $entityOptions[] = sprintf('repositoryClass="%s"', $repositoryClassName);
//        }

        return [
            '<?php' . PHP_EOL,
            'namespace App\Entity;' . PHP_EOL,
            'use Doctrine\ORM\Mapping as ORM;' . PHP_EOL,
            '/**',
            sprintf(' * @ORM\Entity(%s)', implode(',', $entityOptions)),
            ' * @ORM\Table(name="' . SchemaModifier::toTableName($this->getEntity()->getName()) . '")',
            ' */',
            sprintf('class %s', StringUtility::toPascalCase($this->getEntity()->getName())),
            '{',
        ];
    }

    protected function getFinalElements()
    {
        return [
            '}',
        ];
    }

    protected function getIdFieldElements(): array
    {
        $property = [
            '/**',
            ' * @ORM\Id()',
            ' * @ORM\GeneratedValue()',
            ' * @ORM\Column(type="integer")',
            ' */',
            'private $id;',
        ];

        $methods = [
            '',
            'public function getId(): ?int',
            '{',
            self::IDENT . 'return $this->id;',
            '}',
        ];

        return [
            $this->indentElements($property),
            $this->indentElements($methods),
        ];
    }

    protected function indentElements(array $elements): array
    {
        return array_map(function ($value) {
            return self::IDENT . $value;
        }, $elements);
    }

    protected function buildParameters(array $parameters)
    {
        $parameters = array_map(function ($key, $value) {
            return $key . '=' . $value;
        }, array_keys($parameters), $parameters);

        return implode(', ', $parameters);
    }
}