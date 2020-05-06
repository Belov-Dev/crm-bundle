<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\Datasheet;

interface DatasheetAdapterInterface
{
    public function supports(Datasheet $datasheet): bool;

    public function getFields(Datasheet $datasheet);
}