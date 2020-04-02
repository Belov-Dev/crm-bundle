<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class FixtureBuilder
{
    private $entityManager;

    private $fieldTypeRegistry;

    protected $processed = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityFieldRegistry $fieldTypeRegistry
    )
    {
        $this->entityManager = $entityManager;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    public function build()
    {
        $requiredFirst = [];
        $entities = $this->buildDependencyMap();

        foreach ($entities as $entity => $dependencies) {
            if ($dependencies) {
                foreach ($dependencies as $dependency) {
                    $requiredFirst[] = $dependency;
                }
            }
        }

        foreach ($requiredFirst as $entity) {
            $entity = $this->entityManager
                ->getRepository('A2CRMBundle:Entity')
                ->findByName(StringUtility::normalize($entity));
            $this->loadFixtures($entity);
        }

        foreach ($entities as $entityName => $dependencies) {
            if(array_key_exists($entityName, $this->processed)){
                continue;
            }
            $entity = $this->entityManager
                ->getRepository('A2CRMBundle:Entity')
                ->findByName(StringUtility::normalize($entityName));
            $this->loadFixtures($entity);
        }
    }

    protected function loadFixtures(Entity $entity)
    {
        $classname = 'App\\Entity\\' . StringUtility::toPascalCase($entity->getName());

        for ($i = 0; $i < 25; $i++) {
            $object = new $classname();

            /** @var EntityField $field */
            foreach ($entity->getFields() as $field) {
                $setter = 'set' . StringUtility::toPascalCase($field->getName());

                if ($field->getType() == 'relation') {

                    if (!isset($this->processed[StringUtility::toCamelCase($field->getName())])) {
                        throw new Exception('fixture dependency error');
                    }
                    $source = $this->processed[StringUtility::toCamelCase($field->getName())];

                    if (count($source) < 1) {
                        throw new Exception('fixture dependency error');
                    }
                    $object->{$setter}($source[array_rand($source)]);

                    continue;
                }
                $fieldType = $this->fieldTypeRegistry->find($field->getType());
                $object->{$setter}($fieldType->getFixtureValue($field));
            }
            $this->entityManager->persist($object);
            $this->entityManager->flush();
            $this->processed[StringUtility::toCamelCase($entity->getName())][] = $object;
        }
    }

    protected function buildDependencyMap()
    {
        $entities = [];

        foreach ($this->entityManager->getRepository('A2CRMBundle:Entity')->findAll() as $entity) {
            $entities[StringUtility::toCamelCase($entity->getName())] = [];

            /** @var EntityField $field */
            foreach ($entity->getFields() as $field) {
                if ($field->getType() != 'relation') {
                    continue;
                }
                $entities[StringUtility::toCamelCase($entity->getName())][] = StringUtility::toCamelCase($field->getName());
            }
        }

        return $entities;
    }
}