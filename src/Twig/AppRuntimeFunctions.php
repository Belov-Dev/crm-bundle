<?php
declare(strict_types=1);

namespace A2Global\CRMBundle\Twig;

use A2Global\CRMBundle\Builder\DatasheetBuilder;
use A2Global\CRMBundle\DataSheet\DataSheetInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntimeFunctions implements RuntimeExtensionInterface
{
    private $entityManager;

    private $router;

    private $dataSheetBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        DatasheetBuilder $dataSheetBuilder
    )
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->dataSheetBuilder = $dataSheetBuilder;
    }

    // TODO PERFORMANCE extract this method to separate files

    public function getFormField($field)
    {
        return $field['html'];
    }

    public function getMenu()
    {
        $items = [];

        foreach ($this->entityManager->getRepository('A2CRMBundle:Menu')->findAll() as $menuItem) {
            if ($menuItem->getRoute()) {
                $items[] = sprintf(
                    '<li><a href="%s">%s</a></li>',
                    $menuItem->getRoute(),
                    $menuItem->getTitle()
                );
            } else {
                $items[] = sprintf('<li class="header">%s</li>', mb_strtoupper($menuItem->getTitle()));
            }
        }

        return implode(PHP_EOL, $items);
    }

    public function getDatasheet($datasheet)
    {
        if (!$datasheet instanceof DataSheetInterface) {
            throw new Exception(sprintf('Invalid class `%s`, please provide object of DataSheetInterface to build the datasheet', get_class($datasheet)));
        }

        return $this->dataSheetBuilder->getTable($datasheet);
    }

    public function getPagination($datasheet)
    {
        if (!$datasheet instanceof DataSheetInterface) {
            throw new Exception(sprintf('Invalid class `%s`, please provide object of DataSheetInterface to build the datasheet', get_class($datasheet)));
        }

        return $this->dataSheetBuilder->getPagination($datasheet);
    }
}