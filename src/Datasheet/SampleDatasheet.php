<?php

namespace A2Global\CRMBundle\Datasheet;

class SampleDatasheet implements DatasheetInterface
{
    protected $items = [];

    public function __construct()
    {
        /*
         * Building big array of sample items
         * This will fill $this->items[] variable on the top of this file
         */
        $this->buildSampleItems();
    }

    public function getItems(int $startFrom = 0, int $limit = 0)
    {
        // In case if no pagination is needed:
//         return $this->items;

        /*
         * Return $limit items starting from the $startFrom
         *
         * For array use: array_splice($startFrom, $limit);
         * For DQL queries use: ->setFirstResult($startFrom)->setMaxResults($limit)
         * For raw mysql queries (not recommended), use: '...LIMIT $startFrom, $limit'
         */
        return array_splice($this->items, $startFrom, $limit);
    }

    // Optional method, if you want to enable automatic pagination
    public function getItemsTotal()
    {
        return count($this->items);
    }

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