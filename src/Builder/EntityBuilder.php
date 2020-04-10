<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Component\Field\FieldInterface;
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
        $constants = [];
        $properties = [];
        $methods = [];

        /** @var FieldInterface $field */
        foreach ($this->getEntity()->getFields() as $field) {
            $properties = array_merge($properties, self::indentElements($field->getEntityClassProperty()), ['']);
            $methods = array_merge($methods, self::indentElements($field->getEntityClassMethods()), ['']);
            $constant = $field->getEntityClassConstant();

            if($constant){
                $constants = array_merge($constants, self::indentElements($constant), ['']);
            }
        }

        return $this
            ->addElements($this->getBaseElements())
            ->addElements($constants)
            ->addElements($properties)
            ->addElements($methods)
            ->addElements($this->getFinalElements());
    }

    public function getFileContent(): string
    {
        $this->build();
//        dd($this->elements);

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
            '<?php',
            '',
            'namespace App\Entity;',
            '',
            'use Doctrine\ORM\Mapping as ORM;',
            '',
            '/**',
            sprintf(' * @ORM\Entity(%s)', implode(',', $entityOptions)),
            ' * @ORM\Table(name="' . StringUtility::toMySQLTableName($this->getEntity()->getName()) . '")',
            ' */',
            sprintf('class %s', StringUtility::toPascalCase($this->getEntity()->getName())),
            '{',
        ];
    }

    protected function getFinalElements()
    {
        $elements = [
            'public function __toString()',
            '{',
            self::IDENT.'return sprintf(\'%s #%s\', \''.StringUtility::normalize($this->entity->getName()).'\', $this->getId());',
            '}',
        ];
        $elements = self::indentElements($elements);
        $elements[] = '}';

        return $elements;
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
            self::indentElements($property),
            self::indentElements($methods),
        ];
    }

    protected function buildParameters(array $parameters)
    {
        $parameters = array_map(function ($key, $value) {
            return $key . '=' . $value;
        }, array_keys($parameters), $parameters);

        return implode(', ', $parameters);
    }

    public static function indentElements(array $elements): array
    {
        return array_map(function ($value) {
            return self::IDENT . $value;
        }, $elements);
    }
}