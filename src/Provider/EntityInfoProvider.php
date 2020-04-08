<?php

namespace A2Global\CRMBundle\Provider;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Component\Field\FieldInterface;
use A2Global\CRMBundle\Component\Field\IdField;
use A2Global\CRMBundle\Exception\NotImplementedYetException;
use A2Global\CRMBundle\Filesystem\FileManager;
use A2Global\CRMBundle\Utility\StringUtility;
use ReflectionClass;
use ReflectionProperty;

class EntityInfoProvider
{
    protected $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function getEntityList(): array
    {
        $directory = $this->fileManager->getPath(FileManager::CLASS_TYPE_ENTITY);

        return array_map(function ($item) {
            return StringUtility::normalize(basename(substr($item, 0, -4)));
        }, glob($directory . '/*.php'));
    }

    public function getEntity(string $entityName): Entity
    {
        $entity = new Entity(StringUtility::normalize($entityName));
        $class = 'App\\Entity\\' . StringUtility::toPascalCase($entityName);
        $reflection = new ReflectionClass($class);

        foreach ($reflection->getProperties() as $property) {
            $entity->addField($this->getFieldByProperty($property));
        }

        return $entity;
    }

    protected function parseAnnotations(string $comment): array
    {
        $items = [];

        foreach (explode(PHP_EOL, $comment) as $line) {
            if (!preg_match('/\@(.+)\\\(.+)\((.*)\)/iUs', $line, $result)) {
                continue;
            }
            $options = [];

            if (trim($result[3])) {
                foreach (explode(',', $result[3]) as $parameter) {
                    preg_match("/^([^\=]+)(\=(.+))?$/iUs", $parameter, $subresult);
                    $options[trim($subresult[1])] = isset($subresult[3]) ? trim($subresult[3], ' "\'') : null;
                }
            }
            $items[$result[1]][$result[2]] = $options;
        }

        return $items;
    }

    protected function getFieldByProperty(ReflectionProperty $property): FieldInterface
    {
        $annotations = $this->parseAnnotations($property->getDocComment());

        if (isset($annotations['ORM']['Id'])) {
            return new IdField();
        }

        if (isset($annotations['ORM']['Column']['type'])) {
            if (in_array($annotations['ORM']['Column']['type'], ['string'])) {
                $classname = 'A2Global\\CRMBundle\\Component\\Field\\' . StringUtility::toPascalCase($annotations['ORM']['Column']['type']) . 'Field';

                return (new $classname())->setName(StringUtility::normalize($property->getName()));
            }
        }

        throw new NotImplementedYetException();
    }
}