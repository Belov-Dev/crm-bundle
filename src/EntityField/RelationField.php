<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;
use Doctrine\ORM\EntityManagerInterface;

class RelationField extends AbstractField
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

    public function getMySQLFieldType(): string
    {
        return 'TINYINT(1)';
    }

    public function getExtendedFormControls(Entity $entity, $object = null)
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
        $pattern=<<<EOF
            <div class="form-group row">
                <label class="col-form-label col-sm-4 required" for="entity_field_type_form_type">Related entity</label>
                <div class="col-sm-8">
                    <select id="entity_field_type_form_type" name="entity_field_type_form[type]" class="form-control">
                        %s
                    </select>
                </div>
            </div>
EOF;
        return sprintf($pattern, implode(PHP_EOL, $elements));
    }
}