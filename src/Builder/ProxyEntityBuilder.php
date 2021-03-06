<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProxyEntityBuilder
 *
 * todo: isser
 *
 * @package A2Global\CRMBundle\Builder
 */
class ProxyEntityBuilder
{
    const IDENT = "\t";

    private $entityManager;

    private $entityFieldRegistry;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityFieldRegistry $entityFieldRegistry
    )
    {
        $this->entityManager = $entityManager;
        $this->entityFieldRegistry = $entityFieldRegistry;
    }

    public function buildForEntity(Entity $entity)
    {
        $elements = $this->getBaseElements($entity);
        $fieldElements = $this->getIdFieldElements();
        $properties = $fieldElements[0];
        $methods = $fieldElements[1];

        /** @var EntityField $field */
        foreach ($entity->getFields() as $field) {
            $properties = array_merge(
                $properties,
                $this->indentElements($this->entityFieldRegistry->find($field->getType())->getDoctrineClassPropertyCode($field))
            );
            $methods = array_merge(
                $methods,
                $this->indentElements($this->entityFieldRegistry->find($field->getType())->getDoctrineClassMethodsCode($field))
            );
        }
        $elements = array_merge($elements, $properties, $methods, $this->getFinalElements($entity));

        return implode(PHP_EOL, $elements);
    }

    protected function getBaseElements(Entity $entity): array
    {
        $entityOptions = [];
        $repositoryClassName = sprintf('App\\Repository\\%sRepository', StringUtility::toPascalCase($entity->getName()));

        if (class_exists($repositoryClassName)) {
            $entityOptions[] = sprintf('repositoryClass="%s"', $repositoryClassName);
        }

        return [
            '<?php' . PHP_EOL,
            'namespace App\Entity;' . PHP_EOL,
            'use Doctrine\ORM\Mapping as ORM;' . PHP_EOL,
            '/**',
            sprintf(' * @ORM\Entity(%s)', implode(',', $entityOptions)),
            ' * @ORM\Table(name="' . SchemaModifier::toTableName($entity->getName()) . '")',
            ' */',
            sprintf('class %s', StringUtility::toPascalCase($entity->getName())),
            '{',
        ];
    }

    protected function getFinalElements(Entity $entity)
    {
        $nameGetter = null;

        /** @var EntityField $field */
        foreach ($entity->getFields() as $field) {
            $fieldNameCamelCase = StringUtility::toCamelCase($field->getName());

            if (in_array($fieldNameCamelCase, ['name', 'title', 'username'])) {
                $nameGetter = $fieldNameCamelCase;
                break;
            }
        }

        if ($nameGetter) {
            $code = 'return sprintf(\'%s. %s\', $this->getId(), $this->get' . StringUtility::toPascalCase($nameGetter) . '());';
        } else {
            $code = 'return \'' . $entity->getName() . ' #\' . $this->getId();';
        }

        return [
            '',
            self::IDENT . 'public function __toString()',
            self::IDENT . '{',
            self::IDENT . self::IDENT . $code,
            self::IDENT . '}',
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
