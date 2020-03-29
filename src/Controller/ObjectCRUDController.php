<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/manage/object", name="a2crm_object_") */
class ObjectCRUDController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** @Route("/list", name="entity_list") */
    public function entityList()
    {
        return $this->render('@A2CRM/object/entity.list.html.twig', [
            'entities' => $this->entityManager->getRepository('A2CRMBundle:Entity')->findAll(),
        ]);
    }

    /** @Route("/{objectName}/list", name="list") */
    public function objectList($objectName)
    {
        $data = [];
        $entity = $this->entityManager->getRepository('A2CRMBundle:Entity')->findOneBy(['name' => $objectName]);
        $fields = $this->entityManager->getRepository('A2CRMBundle:EntityField')->findBy(['entityId' => $entity->getId()]);
        $repository = $this->entityManager->getRepository('App:'.StringUtility::underScoreToCamelCase($objectName));

        foreach($repository->findAll() as $object){
            $item = [];

            foreach($fields as $field){
                $item[$field->getName()] = (string) $object->{'get'.$field->getName()}();
            }
            $data[] = $item;
        }

        return $this->render('@A2CRM/object/object.list.html.twig', [
            'fields' => $fields,
            'data' => $data,
        ]);
    }
}
