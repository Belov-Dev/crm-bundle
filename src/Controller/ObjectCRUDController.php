<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/manage/object", name="a2crm_object_") */
class ObjectCRUDController extends AbstractController
{
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

    /** @Route("/{objectName}/list", name="list") */
    public function objectList($objectName)
    {
        $data = [];
        $fields = [];
        $objectName = StringUtility::getVariations($objectName);
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findOneBy(['name' => $objectName['readable']]);

        /** @var EntityField $field */
        foreach ($entity->getFields() as $field) {
            $fields[StringUtility::toCamelCase($field->getName())] = $field->getName();
        }
        $repository = $this->entityManager->getRepository('App:' . $objectName['pascalCase']);

        foreach ($repository->findAll() as $object) {
            $item = ['id' => $object->getId()];

            foreach ($fields as $fieldNameCamelCase => $fieldName) {
                $getter = 'get' . $fieldNameCamelCase;
                $value = $object->{$getter}();

                if (is_bool($value)) {
                    $value = $value ? '+' : '-';
                } elseif ($value instanceof DateTimeInterface) {
                    $value = $value->format('H:i:s j/m/Y');
                }elseif(is_object($value)) {
                    if (!method_exists($value, '__toString')) {
                        $value = StringUtility::normalize(StringUtility::getShortClassName($value)) . ' #' . $value->getId();
                    }
                }
                $item[$fieldNameCamelCase] = $value;
            }
            $data[] = $item;
        }

        return $this->render('@A2CRM/object/object.list.html.twig', [
            'entity' => $entity,
            'name' => $objectName,
            'fields' => $fields,
            'data' => $data,
        ]);
    }

    /** @Route("/{objectName}/edit/{objectId?}", name="edit") */
    public function objectEdit(Request $request, $objectName, $objectId = null)
    {
        $isCreating = is_null($objectId);
        $objectNameReadable = StringUtility::normalize($objectName);
        $objectPascalCase = StringUtility::toPascalCase($objectName);
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findOneBy(['name' => $objectNameReadable]);

        if ($isCreating) {
            $classname = 'App\\Entity\\' . StringUtility::toPascalCase($entity->getName());
            $object = new $classname;
        } else {
            $object = $this->entityManager->getRepository('App:' . $objectPascalCase)->find($objectId);
        }

        if ($request->getMethod() == Request::METHOD_POST) {
            $formData = $request->request->get('field');

            /** @var EntityField $field */
            foreach ($entity->getFields() as $field) {
                $fieldNameSnakeCase = StringUtility::toSnakeCase($field->getName());

                if (isset($formData[$fieldNameSnakeCase])) {
                    $this->entityFieldRegistry
                        ->find($field->getType())
                        ->setValueToObject($object, $field, $formData[$fieldNameSnakeCase]);
                }
            }

            if ($isCreating) {
                $this->entityManager->persist($object);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('a2crm_object_list', ['objectName' => $objectName]);
        }
        $url = $this->generateUrl('a2crm_object_edit', ['objectName' => $objectName, 'objectId' => $objectId]);

        $form = [
            'url' => $url,
            'elements' => [],
        ];

        /** @var EntityField $field */
        foreach ($entity->getFields() as $field) {
            $fieldNameCamelCase = StringUtility::toCamelCase($field->getName());
            $form['elements'][$field->getName()] = $this->entityFieldRegistry
                ->find($field->getType())
                ->getFormControlHTML($field, $object->{'get'.$fieldNameCamelCase}());
        }

        return $this->render('@A2CRM/object/object.edit.html.twig', [
            'entity' => $entity,
            'object' => $object,
            'form' => $form,
        ]);
    }
}
