<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

interface DatasheetBuilderInterface
{
    public function supports(): bool;

    public function getItems(): array;

    public function getItemsTotal(): int;

    public function getFields(): array;
}