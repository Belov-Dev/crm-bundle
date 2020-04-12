<?php

namespace A2Global\CRMBundle\Datasheet;

class SampleDatasheet extends ArrayDatasheet
{
    protected $hasFilter = ['name', 'path'];

    protected $actionsTemplate = '@A2CRM/sample/datasheet.actions.html.twig';

    public function build(int $startFrom = 0, int $limit = 0, $sort = [], $filters = [])
    {
        $dir = __DIR__ . '/..{/*,/*/*,/*/*/*,/*/*/*/*,/*/*/*/*/*}';
        $i = 1;
        $items = [];

        foreach (glob($dir, GLOB_BRACE) as $file) {
            $item = [
                'id' => $i,
                'name' => basename($file),
                'size' => (int)filesize($file) . ' bytes',
                'updatedAt' => date('H:i j M, Y', filemtime($file)),
                'path' => realpath($file),
                'extension' => pathinfo($file)['extension'] ?? '',
            ];
            $i++;

            $items[] = $item;
        }

        $this->setItems($items);
    }
}