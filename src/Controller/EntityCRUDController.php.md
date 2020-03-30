<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Form\EntityFieldTypeForm;
use A2Global\CRMBundle\Form\EntityTypeForm;
use A2Global\CRMBundle\Modifier\ProxyEntityModifier;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/manage/entity", name="a2crm_entity_") */
class EntityCRUDController extends AbstractController
{
    private $entityManager;

    private $schemaModifier;

    private $proxyEntityModifier;

    private $entityFieldRegistry;

    public function __construct(
        EntityManagerInterface $entityManager,
        SchemaModifier $schemaModifier,
        ProxyEntityModifier $proxyEntityModifier,
        EntityFieldRegistry $entityFieldRegistry
    )
    {
        $this->entityManager = $entityManager;
        $this->schemaModifier = $schemaModifier;
        $this->proxyEntityModifier = $proxyEntityModifier;
        $this->entityFieldRegistry = $entityFieldRegistry;
    }

    /** @Route("/list", name="list") */
    public function entityList()
    {
        return $this->render('@A2CRM/entity/entity.list.html.twig', [
            'entities' => $this->entityManager->getRepository('A2CRMBundle:Entity')->findAll(),
        ]);
    }

    /** @Route("/edit/{entity}", name="edit") */
    public function entityEdit(Request $request, Entity $entity = null)
    {
        $isCreating = is_null($entity);
        $form = $this->createForm(EntityTypeForm::class, $entity, [
            'action' => $this->generateUrl('a2crm_entity_edit', ['entity' => $isCreating ? null : $entity->getId()]),
            'csrf_protection' => false,
        ])->add('Submit', SubmitType::class);

        if ($request->getMethod() != Request::METHOD_POST) {
            return $this->render('@A2CRM/entity/entity.edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if (!$isCreating) {
            $entityNameBefore = $entity->getNameSnakeCasePlural();
        }
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $request->getSession()->getFlashBag()->add('warning', 'data invalid');

            return $this->redirectToRoute('a2crm_entity_edit', ['entity' => $isCreating ? null : $entity->getId()]);
        }

        /** @var Entity $entity */
        $entity = $form->getData();

        if ($isCreating) {
            $this->entityManager->persist($entity);
            $this->schemaModifier->createTable($entity->getNameSnakeCasePlural());
            $this->entityManager->flush();
        } else {
            $this->entityManager->flush();
            $this->schemaModifier->renameTable($entityNameBefore, $entity->getNameSnakeCasePlural());
        }
        $request->getSession()->getFlashBag()->add('success', 'Entity created');

        return $this->redirectToRoute('a2crm_entity_field_list', ['entity' => $isCreating ? null : $entity->getId()]);
    }

    /** @Route("/{entity}/field/list", name="field_list") */
    public function entityFieldList(Entity $entity)
    {
        return $this->render('@A2CRM/entity/entity_field.list.html.twig', [
            'entity' => $entity,
            'fields' => $this->entityManager->getRepository('A2CRMBundle:EntityField')
                ->findBy(['entityId' => $entity->getId()]),
        ]);
    }

    /** @Route("/{entity}/field/edit/{entityField}", name="field_edit") */
    public function entityFieldEdit(Request $request, Entity $entity, EntityField $entityField = null)
    {
        $isCreating = is_null($entityField);
        $url = $this->generateUrl('a2crm_entity_field_edit', [
            'entity' => $entity->getId(),
            'entityField' => $isCreating ? null : $entityField->getId(),
        ]);
        $form = $this->createForm(EntityFieldTypeForm::class, $entityField, [
            'action' => $url,
            'csrf_protection' => false,
        ])
            ->add('type', ChoiceType::class, ['choices' => $this->entityFieldRegistry->getFormFieldChoices()])
            ->add('Submit', SubmitType::class);

        if ($request->getMethod() != Request::METHOD_POST) {
            return $this->render('@A2CRM/entity/entity_field.edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if (!$isCreating) {
            $entityFieldNameBefore = $entityField->getName();
        }
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $request->getSession()->getFlashBag()->add('warning', 'data invalid');

            return $this->redirect($url);
        }
        /** @var EntityField $entityField */
        $entityField = $form->getData()->setEntityId($entity->getId());

        if ($isCreating) {
            $this->schemaModifier->addField($entity->getName(), $entityField->getName(), $entityField->getType());
            $this->entityManager->persist($entityField);
        } else {
            $this->schemaModifier->updateField($entity->getName(), $entityFieldNameBefore, $entityField->getName(), $entityField->getType());
        }
        $this->entityManager->flush();

        // Should goes after flush, to generate proxy class with actual data
        $this->proxyEntityModifier->update($entity);
        $request->getSession()->getFlashBag()->add('success', 'Field added');

        return $this->redirectToRoute('a2crm_entity_field_list', ['entity' => $entity->getId()]);
    }
}
