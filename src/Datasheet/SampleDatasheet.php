<?php

namespace A2Global\CRMBundle\Datasheet;

class SampleDatasheet implements DatasheetInterface
{
    // this is for sample purposes only
    protected $items = [];

    // this is for sample purposes only
    public function __construct()
    {
        $this->buildSampleItems();
    }

    public function getItems(int $startFrom = 0, int $limit = 0)
    {
        // For array use: array_splice($startFrom, $limit);
        // For DQL queries use: ->setFirstResult($startFrom)->setMaxResults($limit)
        // For raw mysql queries (not recommended), use: '...LIMIT $startFrom, $limit'

        return array_splice($this->items, $startFrom, $limit);
    }

    public function getItemsTotal()
    {
        return count($this->items);
    }

    // this is for sample purposes only
    protected function buildSampleItems()
    {
        // Initial settings
        $dir = __DIR__ . '/..{/*,/*/*,/*/*/*,/*/*/*/*,/*/*/*/*/*}';
        $i = 1;

        // Iterating through the files in project
        foreach (glob($dir, GLOB_BRACE) as $file) {

            // Creating single item with any data
            // Items should be with keys — those will be used as table column titles
            $item = [
                'id' => $i,
                'name' => basename($file),
                'size' => (int)filesize($file) . ' bytes',
                'updatedAt' => date('H:i j M, Y', filemtime($file)),
                'path' => realpath($file),
                'extension' => pathinfo($file)['extension'] ?? '',
            ];
            $i++;

            // Adding item to the Items
            $this->items[] = $item;
        }
    }
}