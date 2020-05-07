<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\Datasheet;

interface DatasheetAdapterInterface
{
    public function supports(Datasheet $datasheet): bool;

    public function buildItems(Datasheet $datasheet, $page = 1, $perPage = 15, $filters = []): array;

    public function buildFields(Datasheet $datasheet): array;

    public function buildItemsTotal(Datasheet $datasheet): int;
}