<?php

namespace A2Global\CRMBundle\Component\Form;

use A2Global\CRMBundle\Utility\StringUtility;

class Form
{
    protected $fields = [];

    protected $url;

    public function getFields(): array
    {
        return $this->fields;
    }

    public function addField(string $title, string $field): Form
    {
        $this->fields[StringUtility::toCamelCase($title)] = [
            'title' => $title,
            'html' => $field,
        ];

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}