<?php

namespace A2Global\CRMBundle\EventListener;

use A2Global\CRMBundle\Event\EntityFieldEvent;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class EntityFieldEventListener implements EventSubscriberInterface
{
    protected $entityInfoProvider;

    protected $twig;

    protected $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityInfoProvider $entityInfoProvider,
        Environment $twig
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            EntityFieldEvent::NAME => 'onEventFabricated',
        ];
    }

    public function onEventFabricated(EntityFieldEvent $event)
    {
        $event->getField()
            ->setTwig($this->twig)
            ->setEntityInfoProvider($this->entityInfoProvider)
            ->setEntityManager($this->entityManager);
    }
}