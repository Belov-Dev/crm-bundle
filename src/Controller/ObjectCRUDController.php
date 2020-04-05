<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Builder\FormBuilder;
use A2Global\CRMBundle\Datasheet\ObjectDatasheet;
use A2Global\CRMBundle\Datasheet\ObjectsDatasheet;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/** @Route("/admin/object", name="crm_object_") */
class ObjectCRUDController extends AbstractController
{
    private $entityManager;

    private $entityFieldRegistry;

    private $objectDatasheet;

    private $twig;

    private $logger;

    private $objectsDatasheet;

    private $formBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityFieldRegistry $entityFieldRegistry,
        ObjectDatasheet $objectDatasheet,
        Environment $twig,
        LoggerInterface $logger,
        ObjectsDatasheet $objectsDatasheet,
        FormBuilder $formBuilder
    )
    {
        $this->entityManager = $entityManager;
        $this->entityFieldRegistry = $entityFieldRegistry;
        $this->objectDatasheet = $objectDatasheet;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->objectsDatasheet = $objectsDatasheet;
        $this->formBuilder = $formBuilder;
    }

    /** @Route("/list/{objectName?}", name="list") */
    public function objectList(Request $request, $objectName = null)
    {
        if (!$objectName) {
            return $this->render('@A2CRM/object/objects.list.html.twig', [
                'datasheet' => $this->objectsDatasheet
            ]);
        }
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($objectName);

        return $this->render('@A2CRM/object/object.list.html.twig', [
            'datasheet' => $this->objectDatasheet->setEntity($entity),
            'objectName' => $objectName,
        ]);
    }

    /** @Route("/edit/{objectName}/{objectId?}", name="edit") */
    public function objectEdit(Request $request, $objectName, $objectId = null)
    {
        $isCreating = is_null($objectId);
        $object = $isCreating ? null : $object = $this->entityManager
            ->getRepository('App:' . StringUtility::toPascalCase($objectName))
            ->find($objectId);
        $entity = $this->entityManager
            ->getRepository('A2CRMBundle:Entity')
            ->findByName($objectName);
        $form = $this->formBuilder->buildFor(
            $objectName,
            $object,
            $this->generateUrl('crm_object_list', ['objectName' => $objectName])
        );

        return $this->render('@A2CRM/object/object.edit.html.twig', [
            'form' => $form,
            'entity' => $entity,
            'object' => $object,
            'isCreating' => $isCreating,
            'isEditing' => !$isCreating,
        ]);
    }

    /** @Route("/save/{objectName}/{objectId?}", name="save") */
    public function objectUpdate(Request $request, $objectName, $objectId = null)
    {
        $isCreating = is_null($objectId);
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($objectName);

        if ($isCreating) {
            $classname = 'App\\Entity\\' . StringUtility::toPascalCase($entity->getName());
            $object = new $classname;
        } else {
            $object = $this->entityManager
                ->getRepository('App:' . StringUtility::toPascalCase($objectName))
                ->find($objectId);
        }
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
        $returnUrl
            = $request->query->get('returnUrl') . '?objectId=' . $object->getId()
            ?: $this->generateUrl('crm_object_list', ['objectName' => $objectName]);

        return $this->redirect($returnUrl);
    }
}
