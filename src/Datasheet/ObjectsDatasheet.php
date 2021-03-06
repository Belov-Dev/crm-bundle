<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Entity\Entity;
use Doctrine\ORM\EntityManagerInterface;

class ObjectsDatasheet extends ArrayDatasheet
{
    protected $actionTemplate = '@A2CRM/object/objects.list.action.html.twig';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function build(int $startFrom = 0, int $limit = 0, $sort = [], $filters = [])
    {
        $items = [];

        /** @var Entity $entity */
        foreach($this->entityManager->getRepository('A2CRMBundle:Entity')->findAll() as $entity){
            $item = [
                'title' => $entity->getName(),
            ];
            $items[] = $item;
        }

        $this->setItems($items);
    }
}