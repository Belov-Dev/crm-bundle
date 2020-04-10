<?php

namespace A2Global\CRMBundle\Event;

use A2Global\CRMBundle\Component\Field\FieldInterface;

class EntityFieldEvent
{
    public const NAME = 'order.placed';

    protected $field;

    public function __construct(FieldInterface $field)
    {
        $this->field = $field;
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }
}