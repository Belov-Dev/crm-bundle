<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Builder\FixtureBuilder;
use A2Global\CRMBundle\Datasheet\EntitiesDatasheet;
use A2Global\CRMBundle\Datasheet\EntityFieldsDatasheet;
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

/** @Route("/admin/crm/entity/", name="crm_admin_entity_") */
class EntityCRUDController extends AbstractController
{
    private $entityManager;

    private $schemaModifier;

    private $proxyEntityModifier;

    private $entityFieldRegistry;

    private $logger;

    private $fixtureBuilder;

    private $entitiesDatasheet;

    private $entityFieldsDatasheet;

    public function __construct(
        EntityManagerInterface $entityManager,
        SchemaModifier $schemaModifier,
        ProxyEntityModifier $proxyEntityModifier,
        EntityFieldRegistry $entityFieldRegistry,
        LoggerInterface $logger,
        FixtureBuilder $fixtureBuilder,
        EntitiesDatasheet $entitiesDatasheet,
        EntityFieldsDatasheet $entityFieldsDatasheet
    )
    {
        $this->entityManager = $entityManager;
        $this->schemaModifier = $schemaModifier;
        $this->proxyEntityModifier = $proxyEntityModifier;
        $this->entityFieldRegistry = $entityFieldRegistry;
        $this->logger = $logger;
        $this->fixtureBuilder = $fixtureBuilder;
        $this->entitiesDatasheet = $entitiesDatasheet;
        $this->entityFieldsDatasheet = $entityFieldsDatasheet;
    }

    /** @Route("list/{entityName?}/{fieldName?}", name="list") */
    public function list(Request $request, $entityName = null, $fieldName = null)
    {
        $data = [
            'datasheet' => [
                'entities' => $this->entitiesDatasheet,
            ],
            'entityFieldTypes' => $this->entityFieldRegistry->getFormFieldChoices(),
        ];

        if ($entityName) {
            $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($entityName);
            $data['datasheet']['entityFields'] = $this->entityFieldsDatasheet->setEntity($entity);
            $data['entity'] = $entity;
        }

        if ($fieldName) {
            $data['entityField'] = $this->entityManager
                ->getRepository('A2CRMBundle:EntityField')
                ->findByName($fieldName);
        }

        return $this->render('@A2CRM/entity/entity.list.html.twig', $data);
    }

    /** @Route("update/{entity?}", name="update") */
    public function entity(Request $request, Entity $entity = null)
    {
        if ($request->getMethod() != Request::METHOD_POST) {
            throw new \Exception('What are you doing here?');
        }
        $isCreating = is_null($entity);

        if ($isCreating) {
            $entity = new Entity();
        } else {
            $entityNameBefore = $entity->getName();
        }
        $entity->setName(StringUtility::normalize($request->request->get('entityForm')['name']));

        if ($isCreating) {
            $this->schemaModifier->createTable($entity->getName());
            $this->entityManager->persist($entity);
        } else {
            $this->schemaModifier->renameTable($entityNameBefore, $entity->getName());
        }
        $this->entityManager->flush();
        // Should goes after flush, to generate proxy class with actual data
        $this->proxyEntityModifier->update($entity);
//        $request->getSession()->getFlashBag()->add('success', 'Entity created');

        return $this->redirectToRoute('crm_admin_entity_list', ['entityName' => StringUtility::toSnakeCase($entity->getName())]);
    }

    /** @Route("field/{entity}/{field?}", name="field") */
    public function field(Request $request, Entity $entity, $field = null)
    {
        $field = $field ? $this->entityManager->getRepository('A2CRMBundle:EntityField')->find($field) : null;
        $isCreating = is_null($field);

        if ($isCreating) {
            $field = new EntityField();
        } else {
            $fieldBefore = clone $field;
        }
        $field
            ->setEntity($entity)
            ->setName(StringUtility::normalize($request->request->get('entityFieldForm')['name']))
            ->setType($request->request->get('entityFieldForm')['type'])
            ->setConfiguration($request->request->get('entityFieldForm')['configuration'] ?? []);

        if ($isCreating) {
            $mysqlQuery = $this->entityFieldRegistry->find($field->getType())->getMysqlCreateQuery($field);
            $this->entityManager->persist($field);
        } else {
            $mysqlQuery = $this->entityFieldRegistry->find($field->getType())->getMySQLUpdateQuery($fieldBefore, $field);
        }
        $this->entityManager->getConnection()->executeQuery($mysqlQuery);
        $this->entityManager->flush();
        // Should goes after flush, to generate proxy class with actual data
        $this->proxyEntityModifier->update($entity);
//        $request->getSession()->getFlashBag()->add('success', 'Field added');

        return $this->redirectToRoute('crm_admin_entity_list', ['entityName' => StringUtility::toSnakeCase($entity->getName())]);
    }

    /** @Route("field-configuration/{fieldType}/{entity}/{field?}", name="field_configuration") */
    public function entityFieldEditConfiguration(Request $request, $fieldType, Entity $entity = null, EntityField $field = null)
    {
        $hasConfiguration = false;
        /** @var EntityFieldInterface $fieldType */
        $fieldType = $this->entityFieldRegistry->find($fieldType);

        if ($fieldType instanceof EntityFieldConfigurableInterface) {
            $hasConfiguration = true;
        }

        return new JsonResponse([
            'hasConfiguration' => $hasConfiguration,
            'html' => $hasConfiguration ? $fieldType->getFormConfigurationControls($entity, $field) : '',
        ]);
    }

//    /** @Route("{entityName}/proxy/update", name="update_proxy") */
    public function updateProxy($entityName)
    {
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findByName($entityName);
        $this->proxyEntityModifier->update($entity);

        return $this->redirectToRoute('crm_entity_list');
    }

//    /** @Route("/fixtures/load", name="load_fixtures") */
    public function loadFixtures()
    {
        $this->fixtureBuilder->build();

        return $this->redirectToRoute('crm_entity_list');
    }
}
