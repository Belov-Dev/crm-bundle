<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\EntityField\EntityFieldConfigurableInterface;
use A2Global\CRMBundle\EntityField\EntityFieldInterface;
use A2Global\CRMBundle\Form\EntityFieldTypeForm;
use A2Global\CRMBundle\Form\EntityTypeForm;
use A2Global\CRMBundle\Modifier\ProxyEntityModifier;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/entity/", name="crm_entity_") */
class EntityCRUDController extends AbstractController
{
    private $entityManager;

    private $schemaModifier;

    private $proxyEntityModifier;

    private $entityFieldRegistry;

    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        SchemaModifier $schemaModifier,
        ProxyEntityModifier $proxyEntityModifier,
        EntityFieldRegistry $entityFieldRegistry,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->schemaModifier = $schemaModifier;
        $this->proxyEntityModifier = $proxyEntityModifier;
        $this->entityFieldRegistry = $entityFieldRegistry;
        $this->logger = $logger;
    }

    /** @Route("list", name="list") */
    public function entityList()
    {
        return $this->render('@A2CRM/entity/entity.list.html.twig', [
            'entities' => $this->entityManager->getRepository('A2CRMBundle:Entity')->findAll(),
        ]);
    }

    /** @Route("edit/{entityName?}", name="edit") */
    public function entityEdit(Request $request, $entityName = null)
    {
        $isCreating = is_null($entityName);
        $entity = $isCreating ? null : $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($entityName);
        $url = $this->generateUrl('crm_entity_edit', ['entityName' => $entityName]);
        $form = $this->createForm(EntityTypeForm::class, $entity, [
            'action' => $url,
            'csrf_protection' => false,
        ])->add('Submit', SubmitType::class);

        if ($request->getMethod() != Request::METHOD_POST) {
            return $this->render('@A2CRM/entity/entity.edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if (!$isCreating) {
            $entityNameBefore = $entity->getName();
        }
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $request->getSession()->getFlashBag()->add('warning', 'data invalid');

            return $this->redirect($url);
        }

        /** @var Entity $entity */
        $entity = $form->getData();
        $entity->setName(StringUtility::normalize($entity->getName()));

        if ($isCreating) {
            $this->schemaModifier->createTable($entity->getName());
            $this->entityManager->persist($entity);
        } else {
            $this->schemaModifier->renameTable($entityNameBefore, $entity->getName());
        }
        $this->entityManager->flush();

        // Should goes after flush, to generate proxy class with actual data
        $this->proxyEntityModifier->update($entity);
        $request->getSession()->getFlashBag()->add('success', 'Entity created');

        return $this->redirectToRoute('crm_entity_list');
    }

    /** @Route("{entityName}/field/edit/{entityField?}", name="field_edit") */
    public function entityFieldEdit(Request $request, $entityName, EntityField $entityField = null)
    {
        $isCreating = is_null($entityField);

        if($isCreating){
            $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($entityName);
        }else{
            $entity = $entityField->getEntity();
        }
        $url = $this->generateUrl('crm_entity_field_edit', [
            'entityName' => $entityName,
            'entityField' => $entityField ? $entityField->getId() : '',
        ]);

        if($isCreating){
            $entityField = (new EntityField())->setType('string');
        }
        $form = $this->createForm(EntityFieldTypeForm::class, $entityField, [
            'action' => $url,
            'csrf_protection' => false,
        ])
            ->add('type', ChoiceType::class, ['choices' => $this->entityFieldRegistry->getFormFieldChoices()])
            ->add('submit', SubmitType::class);

        if ($request->getMethod() != Request::METHOD_POST) {

            return $this->render('@A2CRM/entity/entity_field.edit.html.twig', [
                'form' => $form->createView(),
                'entity' => $entity,
                'entityField' => $entityField,
                'entityName' => StringUtility::getVariations($entity->getName()),
            ]);
        }

        if (!$isCreating) {
            $entityFieldBefore = clone $entityField;
        }
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $request->getSession()->getFlashBag()->add('warning', 'data invalid');

            return $this->redirect($url);
        }
        /** @var EntityField $entityField */
        $entityField = $form->getData();
        $entityField
            ->setName(StringUtility::normalize($entityField->getName()))
            ->setEntity($entity)
            ->setConfiguration($request->request->get('configuration'));

        if ($isCreating) {
            $mysqlQuery = $this->entityFieldRegistry->find($entityField->getType())->getMysqlCreateQuery($entityField);
            $this->entityManager->persist($entityField);
        } else {
            $mysqlQuery = $this->entityFieldRegistry->find($entityField->getType())->getMySQLUpdateQuery($entityFieldBefore, $entityField);
        }
        $this->entityManager->getConnection()->executeQuery($mysqlQuery);
        $this->entityManager->flush();

        // Should goes after flush, to generate proxy class with actual data
        $this->proxyEntityModifier->update($entity);
        $request->getSession()->getFlashBag()->add('success', 'Field added');

        return $this->redirectToRoute('crm_entity_list');
    }

    /** @Route("{entityName}/field/edit-configuration/{fieldType}/{entityField?}", name="field_edit_extended") */
    public function entityFieldEditConfiguration(Request $request, $entityName, $fieldType, EntityField $entityField = null)
    {
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($entityName);
        $hasConfiguration = false;
        /** @var EntityFieldInterface $fieldType */
        $fieldType = $this->entityFieldRegistry->find($fieldType);

        if ($fieldType instanceof EntityFieldConfigurableInterface) {
            $hasConfiguration = true;
        }

        return new JsonResponse([
            'hasConfiguration' => $hasConfiguration,
            'html' => $hasConfiguration ? $fieldType->getFormConfigurationControls($entity, $entityField, $fieldType) : '',
        ]);
    }

    /** @Route("{entityName}/proxy/update", name="update_proxy") */
    public function updateProxy($entityName)
    {
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($entityName);
        $this->proxyEntityModifier->update($entity);

        return $this->redirectToRoute('crm_entity_list');
    }
}
