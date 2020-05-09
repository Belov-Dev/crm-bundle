<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Datasheet\DatasheetExtended;

interface DatasheetBuilderInterface
{
    public function supports(): bool;

    public function getItems(): array;

    public function getItemsTotal(): int;

    public function getFields(): array;

    public function hasFilters(): bool;
}