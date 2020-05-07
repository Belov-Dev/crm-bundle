<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\DatasheetExtended;

interface DatasheetAdapterInterface
{
    public function supports(DatasheetExtended $datasheet): bool;

    public function getItems(DatasheetExtended $datasheet): array;

    public function getItemsTotal(DatasheetExtended $datasheet): int;

    public function getFields(DatasheetExtended $datasheet): array;
}