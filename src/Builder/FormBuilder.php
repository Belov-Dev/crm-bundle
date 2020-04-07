<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class FormBuilder
{
    protected $entityManager;

    private $entityFieldRegistry;

    private $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityFieldRegistry $entityFieldRegistry,
        RouterInterface $router
    )
    {
        $this->entityManager = $entityManager;
        $this->entityFieldRegistry = $entityFieldRegistry;
        $this->router = $router;
    }

    public function buildFor($objectName, $object = null, $returnUrl = null)
    {
        $entity = $this->entityManager->getRepository('EntityZ')->findByName($objectName);

        $url = $this->router->generate('crm_object_save', [
            'objectName' => $objectName,
            'objectId' => $object ? $object->getId() : '',
            'returnUrl' => $returnUrl,
        ]);
        $form = [
            'url' => $url,
            'fields' => [],
        ];

        /** @var EntityField $field */
        foreach ($entity->getFields() as $field) {
            $fieldNameCamelCase = StringUtility::toCamelCase($field->getName());
            $form['fields'][$fieldNameCamelCase] = [
                'title' => $field->getName(),
                'html' => $this->entityFieldRegistry->find($field->getType())
                    ->getFormControlHTML($field, $object ? $object->{'get' . $fieldNameCamelCase}() : ''),
            ];
        }

        return $form;
    }
}