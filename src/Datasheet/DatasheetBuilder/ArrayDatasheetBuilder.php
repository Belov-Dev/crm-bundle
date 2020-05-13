<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Utility\StringUtility;

class ArrayDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    public function supports(): bool
    {
        return is_array($this->getDatasheet()->getData());
    }

    public function build($page = null, $itemsPerPage = null, $filters = [])
    {
        if (is_callable($this->getDatasheet()->getData())) {
            $callable = $this->getDatasheet()->getData();
            $this->getDatasheet()->setItems($callable());
        }else{
            $this->getDatasheet()->setItems($this->getDatasheet()->getData());
        }

        if($page){
            $this->getDatasheet()->setPage($page);
        }

        if($itemsPerPage){
            $this->getDatasheet()->setItemsPerPage($itemsPerPage);
        }
        $this->getDatasheet()->setItemsTotal(count($this->getDatasheet()->getItems()));

        if ($this->datasheet->getItems() && count($this->datasheet->getItems()) > 0) {
            foreach (array_keys($this->datasheet->getItems()[0]) as $fieldName) {
                $fields[StringUtility::toCamelCase($fieldName)] = [
                    'title' => StringUtility::normalize($fieldName),
                    'hasFilter' => false,
                ];
            }
        }
//        $fields = $this->getDatasheet()->getFieldsToShow() ?: $this->getFields();
//
//        foreach ($this->getDatasheet()->getFieldsToRemove() as $fieldToRemove) {
//            if (isset($fields[$fieldToRemove])) {
//                unset($fields[$fieldToRemove]);
//            }
//        }
//
//        foreach ($fields as $fieldName => $fieldOptions) {
//            if (!$fieldOptions['hasFilter']) {
//                continue;
//            }
//            $fields[$fieldName]['filters'] = $this->getFilters($fieldName);
//            $this->getDatasheet()->setHasFilters(true);
//        }
        $this->getDatasheet()->setFields($fields);

        parent::build($page, $itemsPerPage, $filters);
    }
}