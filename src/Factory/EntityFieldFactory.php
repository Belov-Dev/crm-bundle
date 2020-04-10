<?php

namespace A2Global\CRMBundle\Factory;

use A2Global\CRMBundle\Component\Field\FieldInterface;
use A2Global\CRMBundle\Event\EntityFieldEvent;
use A2Global\CRMBundle\Utility\StringUtility;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityFieldFactory
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function get($type): FieldInterface
    {
        /** @var FieldInterface $field */
        $classname = 'A2Global\\CRMBundle\\Component\\Field\\' . StringUtility::toPascalCase($type) . 'Field';
        $field = new $classname();

        $this->eventDispatcher->dispatch(new EntityFieldEvent($field), EntityFieldEvent::NAME);

        return $field;
    }
}