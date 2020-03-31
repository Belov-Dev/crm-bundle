<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Utility\StringUtility;
use Cassandra\Schema;
use Doctrine\ORM\EntityManagerInterface;

class RelationField extends AbstractField implements EntityFieldConfigurableInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getName(): string
    {
        return 'Relation';
    }

    public function getFriendlyName(): string
    {
        return 'Other entity';
    }

    public function getMySQLCreateQuery(EntityField $object): string
    {
        $targetEntity = $this->entityManager->getRepository('A2CRMBundle:Entity')->find($object->getConfiguration()['target_entity']);
        $tableName = SchemaModifier::toTableName($object->getEntity()->getName());
        $fieldName = sprintf('%s_id', StringUtility::toSnakeCase($targetEntity->getName()));
        $uniqueKey = SchemaModifier::generateKey([$tableName, $fieldName]);

        $query = [];
        $query[] = sprintf(
            'ALTER TABLE %s ADD %s INT DEFAULT NULL',
            $tableName, $fieldName
        );
        $query[] = sprintf(
            'ALTER TABLE %s ADD CONSTRAINT FK_%s FOREIGN KEY (%s) REFERENCES %s (id)',
            $tableName, $uniqueKey, $fieldName, SchemaModifier::toTableName($targetEntity->getName())
        );
        $query[] = sprintf(
            'CREATE INDEX IDX_%s ON %s (%s)',
            $uniqueKey, $tableName, $fieldName
        );

        return implode(';'.PHP_EOL, $query);
    }

    public function getFormConfigurationControls(Entity $entity, $object = null)
    {
        $elements = [];

        foreach ($this->entityManager->getRepository('A2CRMBundle:Entity')->findAll() as $relatedEntity) {
            if ($entity->getId() == $relatedEntity->getId()) {
                continue;
            }
            $elements[] = sprintf('<option value="%s">%s</option>', $relatedEntity->getId(), $relatedEntity->getName());
        }

        return $this->getExtendedFormHTML($elements);
    }

    protected function getExtendedFormHTML($elements)
    {
        $pattern = <<<EOF
            <div class="form-group row">
                <label class="col-form-label col-sm-4 required">Related entity</label>
                <div class="col-sm-8">
                    <select id="entity_field_type_form_type" name="configuration[target_entity]" class="form-control">
                        %s
                    </select>
                </div>
            </div>
EOF;
        return sprintf($pattern, implode(PHP_EOL, $elements));
    }
}