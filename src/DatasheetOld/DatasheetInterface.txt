<?php

namespace A2Global\CRMBundle\Datasheet;

interface DatasheetInterface
{
    public function build(int $startFrom = 0, int $limit = 0, $sort = [], $filters = []);

    public function getFields();

    public function getItems();

    public function getItemsTotal();

    public function getActionsTemplate();

    public function getItemsPerPage();
}