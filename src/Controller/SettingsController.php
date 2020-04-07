<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Builder\EntityBuilder;
use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Exception\NotImplementedYetException;
use A2Global\CRMBundle\FieldType\IDFieldType;
use A2Global\CRMBundle\Modifier\FileManager;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Registry\EntityFieldTypeRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Choice;

/** @Route("/crm/settings/", name="crm_settings_") */
class SettingsController extends AbstractController
{
    protected $entityInfoProvider;

    protected $entityBuilder;

    protected $fileManager;

    protected $entityFieldRegistry;

    public function __construct(
        EntityInfoProvider $entityInfoProvider,
        EntityBuilder $entityBuilder,
        FileManager $fileManager,
        EntityFieldRegistry $entityFieldRegistry
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
        $this->entityBuilder = $entityBuilder;
        $this->fileManager = $fileManager;
        $this->entityFieldRegistry = $entityFieldRegistry;
    }

    /** @Route("", name="dashboard") */
    public function dashboard()
    {
        return $this->entityList();
    }

    /** @Route("entity/list", name="entity_list") */
    public function entityList()
    {
        return $this->render('@A2CRM/settings/entity.list.html.twig', [
            'entityList' => $this->entityInfoProvider->getEntityList(),
        ]);
    }

    /** @Route("entity/edit/{entityName?}", name="entity_edit") */
    public function entityEdit(Request $request, string $entityName = null)
    {
        $isCreating = is_null($entityName);
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $entityName = StringUtility::normalize($formData['name']);

            if ($isCreating) {

                $entity = (new Entity($entityName))
                    ->addField(new IDFieldType());
                $this->updateEntityFile($entity);

            } else {
                throw new NotImplementedYetException();
            }
            $request->getSession()->getFlashBag()->add('success', $isCreating ? 'Entity created' : 'Entity updated');

            return $this->redirectToRoute('crm_settings_entity_list', ['entityName' => $entityName]);
        }

        return $this->render('@A2CRM/settings/entity.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** @Route("entity/{entityName}/field/list", name="entity_field_list") */
    public function entityFieldList(string $entityName)
    {
        return $this->render('@A2CRM/settings/entity.field.list.html.twig', [
            'entity' => $this->entityInfoProvider->getEntity($entityName),
        ]);
    }

    /** @Route("entity/{entityName}/field/edit/{fieldName?}", name="entity_field_edit") */
    public function entityFieldEdit(Request $request, string $entityName, $fieldName = null)
    {
        $isCreating = is_null($fieldName);
        $entity = $this->entityInfoProvider->getEntity($entityName);
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => $this->entityFieldRegistry->getFormFieldChoices(),
            ])
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if ($isCreating) {
                $field = $this->entityFieldRegistry
                    ->find($formData['type'])
                    ->setName(StringUtility::normalize($formData['name']));
                $entity->addField($field);
                $this->updateEntityFile($entity);
            } else {
                throw new NotImplementedYetException();
            }
            $request->getSession()->getFlashBag()->add('success', $isCreating ? 'Entity field added' : 'Entity field updated');

            return $this->redirectToRoute('crm_settings_entity_field_list', ['entityName' => $entityName]);
        }

        return $this->render('@A2CRM/settings/entity.field.edit.html.twig', [
            'entity' => $this->entityInfoProvider->getEntity($entityName),
            'form' => $form->createView(),
        ]);
    }

    protected function updateEntityFile(Entity $entity)
    {
        $this->fileManager->save(
            FileManager::CLASS_TYPE_ENTITY,
            StringUtility::toPascalCase($entity->getName()),
            $this->entityBuilder->setEntity($entity)->getFileContent()
        );
    }
}
