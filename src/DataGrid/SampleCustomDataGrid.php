<?php

namespace A2Global\CRMBundle\DataGrid;

class SampleCustomDataGrid extends AbstractDataGrid implements DataGridInterface
{
    protected $data;

    protected $fields;

    public function build($options)
    {
        $this->currentPage = $options['page'] ?? 1;
        $this->perPage = $options['per_page'] ?? self::PER_PAGE;
        $this->queryString = [
            'page' => $this->currentPage,
            'per_page' => $this->perPage,
        ];
        $this->buildFields();
        $this->buildData();

        return $this;
    }

    protected function buildData()
    {
        $offset = ($this->getCurrentPage() - 1) * $this->getPerPage();
        $this->buildCompleteData();
        $this->data = array_splice($this->data, $offset, $this->getPerPage());
    }

    protected function buildCompleteData()
    {
        $data = [];
        $dir = __DIR__ . '/..{/*,/*/*,/*/*/*,/*/*/*/*,/*/*/*/*/*}';
        $i = 1;

        foreach (glob($dir, GLOB_BRACE) as $file) {
            $pi = pathinfo($file);
            $data[] = [
                'id' => $i,
                'name' => basename($file),
                'size' => (int)filesize($file) . ' bytes',
                'updatedAt' => date('H:i j M, Y', filemtime($file)),
                'path' => realpath($file),
                'extension' => $pi['extension'] ?? '',
            ];
            ++$i;
        }
        $this->data = $data;
        $this->pagesTotal = (int)ceil($i / $this->getPerPage());
    }

    protected function buildFields()
    {
        $this->fields = [
            'id' => ['title' => '#'],
            'name' => ['title' => 'Filename'],
            'size' => ['title' => 'File size'],
            'updatedAt' => ['title' => 'Modified at'],
            'path' => ['title' => 'Path'],
        ];
    }
}