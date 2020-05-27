<?php

namespace A2Global\CRMBundle\Datasheet;

class DatasheetExtended extends Datasheet
{
    const NEST_SEPARATOR = "___";

    protected $original;

    protected $fields = [];

    protected $items = [];

    protected $hasFilters = false;

    protected $filters = [];

    protected $itemsTotal = 0;

    protected $debug = [];

    protected $constructedFrom = '';

    protected $uniqueId;

    protected $debugMode = false;

    public function __construct($data)
    {
        parent::__construct(null);

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        $this->generateUniqueId();
    }

    protected function generateUniqueId()
    {
        $this->uniqueId = strtoupper(substr(md5($this->constructedFrom), 0, 4));
    }

    /** Base properties */

    public function getData()
    {
        return $this->data;
    }

    public function getFieldsToShow(): ?array
    {
        return $this->fieldsToShow;
    }

    public function getFieldsToRemove(): ?array
    {
        return $this->fieldsToRemove;
    }

    public function getFieldHandlers(): ?array
    {
        return $this->fieldHandlers;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function getItemsTotal()
    {
        return $this->itemsTotal;
    }

    public function getPage()
    {
        return $this->page;
    }

    /** Extended properties */

    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items): self
    {
        $this->items = $items;

        return $this;
    }

    public function setHasFilters($hasFilters): self
    {
        $this->hasFilters = $hasFilters;

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

    public function getFieldOptions()
    {
        return $this->fieldOptions;
    }

    public function getOriginal(): Datasheet
    {
        return $this->original;
    }

    public function setOriginal(Datasheet $original): self
    {
        $this->original = $original;

        return $this;
    }

    public function getDebug(): array
    {
        return $this->debug;
    }

    public function addDebug(string $debug): DatasheetExtended
    {
        $this->debug[] = $debug;

        return $this;
    }

    public function getConstructedFrom(): string
    {
        return $this->constructedFrom;
    }

    public function setConstructedFrom(string $constructedFrom): DatasheetExtended
    {
        $this->constructedFrom = $constructedFrom;

        return $this;
    }

    public function setDisableFilters($disableFilters)
    {
        $this->disableFilters = $disableFilters;
    }

    public function isFiltersDisabled()
    {
        return $this->disableFilters;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    /** Other */

    public function hasFilters()
    {
        return $this->disableFilters ? false : $this->hasFilters;
    }

    public function getTranslationPrefix()
    {
        return $this->translationPrefix;
    }

    public function getHasFilters()
    {
        return $this->hasFilters;
    }

    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    public function setDebugMode(bool $debugMode): DatasheetExtended
    {
        $this->debugMode = $debugMode;

        return $this;
    }
}