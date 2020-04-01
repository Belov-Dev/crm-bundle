<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

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
        $targetEntity = $this->getTargetEntity($object);
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

        return implode(';' . PHP_EOL, $query);
    }

    public function getMySQLUpdateQuery(EntityField $entityFieldBefore, EntityField $entityFieldAfter): string
    {
        throw new Exception('this feature is under construction');
    }

    public function getDoctrineClassPropertyCode(EntityField $object): array
    {
        $targetEntity = $this->getTargetEntity($object);

        return [
            '',
            '/**',
            ' * @ORM\ManyToOne(',
            ' *     targetEntity="' . StringUtility::toPascalCase($targetEntity->getName()) . '"',
            ' * )',
            ' * @ORM\JoinColumn(',
            ' *     name="' . StringUtility::toSnakeCase($targetEntity->getName()) . '_id",',
            ' *     referencedColumnName="id"',
            ' * )',
            ' */',
            'private $' . StringUtility::toCamelCase($object->getName()) . ';',
        ];
    }

    public function getDoctrineClassMethodsCode(EntityField $object): array
    {
        return [
            '',
            'public function get' . StringUtility::toPascalCase($object->getName()) . '()',
            '{',
            self::INDENT . 'return $this->' . StringUtility::toCamelCase($object->getName()) . ';',
            '}',
            '',
            'public function set' . StringUtility::toPascalCase($object->getName()) . '($value): self',
            '{',
            self::INDENT . '$this->' . StringUtility::toCamelCase($object->getName()) . ' = $value;',
            '',
            self::INDENT . 'return $this;',
            '}',
        ];
    }

    public function getFormControlHTML(EntityField $field, $value = null): string
    {
        $targetEntity = $this->getTargetEntity($field);
        $optionsRepository = $this->entityManager->getRepository('App:' . StringUtility::toPascalCase($targetEntity->getName()));

        $html = [];
        $html[] = sprintf('<select class="form-control" name="field[%s]">', StringUtility::toSnakeCase($field->getName()));

        foreach ($optionsRepository->findAll() as $item) {
            $isSelected = $value && ($value->getId() == $item->getId());
            $html[] = sprintf('<option value="%s" %s>%s', $item->getId(), ($isSelected ? 'selected' : ''), (string)$item);
        }
        $html[] = '</select>';

        return implode(PHP_EOL, $html);
    }

    public function setValueToObject($object, EntityField $field, $value)
    {
        $setter = 'set' . StringUtility::toPascalCase($field->getName());
        $value = $this->entityManager
            ->getRepository('App:' . StringUtility::toPascalCase($this->getTargetEntity($field)->getName()))
            ->find($value);

        return $object->{$setter}($value);
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

    protected function getTargetEntity(EntityField $field)
    {
        return $this->entityManager->getRepository('A2CRMBundle:Entity')->find($field->getConfiguration()['target_entity']);
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