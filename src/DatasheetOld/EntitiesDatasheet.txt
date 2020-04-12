<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Entity\EntityZ;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;

class EntitiesDatasheet extends ArrayDatasheet
{
    protected $actionTemplate = '@A2CRM/entity/entity.list.action.html.twig';

    protected $itemsPerPage = 30;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function build(int $startFrom = 0, int $limit = 0, $sort = [], $filters = [])
    {
        $items = [];

        /** @var EntityZ $entity */
        foreach($this->entityManager->getRepository('EntityZ')->findAll() as $entity){
            $item = [
                'title' => $entity->getName(),
                'name' => StringUtility::toCamelCase($entity->getName()),
            ];
            $items[] = $item;
        }

        $this->setItems($items);
    }

    public function getFields()
    {
        return [
            'title' => [
                'title' => 'Name',
            ]
        ];
    }
}