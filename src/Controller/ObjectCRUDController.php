<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\DataGrid\ObjectDataGrid;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/** @Route("/manage/object", name="a2crm_object_") */
class ObjectCRUDController extends AbstractController
{
    private $entityManager;

    private $entityFieldRegistry;

    private $objectDataGrid;

    private $twig;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityFieldRegistry $entityFieldRegistry,
        ObjectDataGrid $objectDataGrid,
        Environment $twig
    )
    {
        $this->entityManager = $entityManager;
        $this->entityFieldRegistry = $entityFieldRegistry;
        $this->objectDataGrid = $objectDataGrid;
        $this->twig = $twig;
    }

    /** @Route("/{objectName}/list", name="list") */
    public function objectList(Request $request, $objectName)
    {
        $entity = $this->entityManager
            ->getRepository('A2CRMBundle:Entity')
            ->findOneBy(['name' => StringUtility::normalize($objectName)]);

        $dataGrid = $this->objectDataGrid
            ->setEntity($entity)
            ->build($request->query->all());

        return $this->render('@A2CRM/object/object.list.html.twig', [
            'dataGrid' => $dataGrid,
            'entity' => $entity,
            'objectName' => $objectName,
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
