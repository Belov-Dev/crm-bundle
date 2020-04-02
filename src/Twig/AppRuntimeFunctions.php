<?php
declare(strict_types=1);

namespace A2Global\CRMBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntimeFunctions implements RuntimeExtensionInterface
{
    private $entityManager;

    private $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router
    )
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function getFormField($formField)
    {
        return $formField;
    }

    public function getMenu()
    {
        $items = [];

        foreach ($this->entityManager->getRepository('A2CRMBundle:Menu')->findAll() as $menuItem) {
            if($menuItem->getRoute()) {
                $items[] = sprintf(
                    '<li><a href="%s">%s</a></li>',
                    $menuItem->getRoute(),
                    $menuItem->getTitle()
                );
            }else{
                $items[] = sprintf('<li class="header">%s</li>', $menuItem->getTitle());
            }
        }

        return implode(PHP_EOL, $items);
    }
}