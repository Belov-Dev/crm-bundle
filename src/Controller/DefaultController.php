<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Form\EntityTypeForm;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $entityManager;

    private $schemaModifier;

    public function __construct(EntityManagerInterface $entityManager, SchemaModifier $schemaModifier)
    {
        $this->entityManager = $entityManager;
        $this->schemaModifier = $schemaModifier;
    }

    /**
     * @Route("/manage", name="a2crm_homepage")
     */
    public function index()
    {
        return $this->render('@A2CRM/homepage.html.twig');
    }

    /**
     * @Route("/manage/entity/list", name="a2crm_entity_list")
     */
    public function entityList()
    {
        return $this->render('@A2CRM/entity/list.html.twig', [
            'entities' => $this->entityManager->getRepository('A2CRMBundle:Entity')->findAll(),
        ]);
    }

    /**
     * @Route("/manage/entity/{entity}/field/list", name="a2crm_entity_field_list")
     */
    public function entityFieldList(Entity $entity)
    {
        return $this->render('@A2CRM/entity/field.list.html.twig', [
            'entity' => $entity,
            'fields' => $this->entityManager->getRepository('A2CRMBundle:EntityField')
                ->findBy(['entityId' => $entity->getId()]),
        ]);
    }

    /**
     * @Route("/manage/entity/edit/{entity}", name="a2crm_entity_edit")
     */
    public function entityEdit(Request $request, Entity $entity = null)
    {
        $isCreating = is_null($entity);
        $form = $this->createForm(EntityTypeForm::class, null, [
            'action' => $this->generateUrl('a2crm_entity_edit'),
            'csrf_protection' => false,
        ])->add('Submit', SubmitType::class);

        if ($request->getMethod() != Request::METHOD_POST) {
            return $this->render('@A2CRM/entity/edit.html.twig', [
                'entities' => $this->entityManager->getRepository('A2CRMBundle:Entity')->findAll(),
                'form' => $form->createView(),
            ]);
        }
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $request->getSession()->getFlashBag()->add('warning', 'data invalid');

            return $this->redirectToRoute('a2crm_entity_edit');
        }
        /** @var Entity $entity */
        $entity = $form->getData();
        $this->schemaModifier->createTable($entity->getName());

        if ($isCreating) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
        $request->getSession()->getFlashBag()->add('success', 'Entity created');

        return $this->redirectToRoute('a2crm_entity_list');
    }

    /**
     * @Route("/manage/heartbeat", name="a2crm_hearbeat")
     */
    public function heartbeat()
    {
        return new Response(sprintf('Heartbeat: OK [%s]', (new DateTime())->format(DATE_RFC7231)));
    }
}
