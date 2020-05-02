<?php

namespace A2Global\CRMBundle\Datasheet;

use Doctrine\ORM\QueryBuilder;

trait DatasheetGettersSettersTrait
{
    protected $data = [];

    protected $page = 1;

    protected $itemsPerPage = 15;

    protected $itemsTotal = 0;

    /** @var QueryBuilder */
    protected $queryBuilder = null;

    protected $filters = [];

    protected $enableFiltering = false;

    protected $debug = [];

    protected $showDebug = false;

    protected $summaryRow = null;

    public function getItems()
    {
        return $this->items;
    }

    public function setQueryBuilder($queryBuilder): self
    {
        $this->setEnableFiltering(true);
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function getActionsTemplate()
    {
        return '';
    }

    public function getActionTemplate()
    {
        return '';
    }

    public function getItemsTotal(): int
    {
        return $this->itemsTotal;
    }

    public function setItemsTotal($itemsTotal): self
    {
        $this->itemsTotal = is_callable($itemsTotal) ? $itemsTotal() : $itemsTotal;

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function isEnableFiltering(): bool
    {
        return $this->enableFiltering;
    }

    public function setEnableFiltering(bool $enableFiltering): self
    {
        $this->enableFiltering = $enableFiltering;

        return $this;
    }

    public function getDebug(): array
    {
        return $this->debug;
    }

    public function setDebug(array $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function isShowDebug(): bool
    {
        return $this->showDebug;
    }

    public function setShowDebug(bool $showDebug): self
    {
        $this->showDebug = $showDebug;

        return $this;
    }

    public function getSummaryRow()
    {
        return $this->summaryRow;
    }

    public function addSummaryRow(array $summaryRow): self
    {
        $this->summaryRow = $summaryRow;

        return $this;
    }
}