<?php

namespace A2Global\CRMBundle\EventListener;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Modifier\EntityNamesModifier;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DoctrineEventListener
{
    private $entityNamesModifier;

    public function __construct(
        EntityNamesModifier $entityNamesModifier
    )
    {
        $this->entityNamesModifier = $entityNamesModifier;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        if($event->getEntity() instanceof Entity){
            $this->entityNamesModifier->updateNames($event->getEntity());
        }
    }
}