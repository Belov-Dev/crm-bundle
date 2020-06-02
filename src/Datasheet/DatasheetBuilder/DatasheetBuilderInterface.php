<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

interface DatasheetBuilderInterface
{
    public function supports(): bool;

    public function build($page, $itemsPerPage, $filters, $sorting);
}