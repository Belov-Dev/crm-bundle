<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Builder\EntityBuilder;
use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Exception\NotImplementedYetException;
use A2Global\CRMBundle\FieldType\IDFieldType;
use A2Global\CRMBundle\Modifier\FileManager;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/crm/settings/", name="crm_settings_") */
class SettingsController extends AbstractController
{
    protected $entityInfoProvider;

    protected $entityBuilder;

    protected $fileManager;

    public function __construct(
        EntityInfoProvider $entityInfoProvider,
        EntityBuilder $entityBuilder,
        FileManager $fileManager
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
        $this->entityBuilder = $entityBuilder;
        $this->fileManager = $fileManager;
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
        $form = $this->createFormBuilder([])
            ->add('name', TextType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $entityName = StringUtility::normalize($formData['name']);

            if($isCreating){

                $entity = (new Entity($entityName))
                    ->addField(new IDFieldType());

                $this->fileManager->save(
                    FileManager::CLASS_TYPE_ENTITY,
                    StringUtility::toPascalCase($entityName),
                    $this->entityBuilder->setEntity($entity)->getFileContent()
                );

            }else{
                throw new NotImplementedYetException();
            }
            $request->getSession()->getFlashBag()->add('success', $isCreating ? 'Entity created' : 'Entity updated');

            return $this->redirectToRoute('crm_settings_entity_list', ['entityName' => $entityName]);
        }

        return $this->render('@A2CRM/settings/entity.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
