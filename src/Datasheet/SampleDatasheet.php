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

    public function getItems(int $startFrom = 0, int $limit = 0, $sort = [], $filters = [])
    {
        // Optional
        $this->filterItems($filters);

        // For array use: array_splice($startFrom, $limit);
        // for Doctrine: $entityManager->getRepository('App:ClassName')->findBy([], [], $limit, $startFrom);
        // for DQL: ->setFirstResult($startFrom)->setMaxResults($limit)
        // for raw mysql queries (not recommended) use: '...LIMIT $startFrom, $limit'

        return array_splice($this->items, $startFrom, $limit);
    }

    public function getItemsTotal()
    {
        // For array use: count($this->items);
        // for Doctrine: $entityManager->getRepository('App:ClassName')->findBy([], [], $limit, $startFrom);
        // for DQL: ->setFirstResult($startFrom)->setMaxResults($limit)
        // for raw mysql queries (not recommended) use: '...LIMIT $startFrom, $limit'

        return count($this->items);
    }

    // Optional
    public function getActionsTemplate()
    {
        return '@A2CRM/sample/datasheet.actions.html.twig';
    }

    /**
     * Optional
     *
     * If you want to explicitly define fields, to:
     *   - change title of the column
     *   - enable filtering
     *   - enable sorting
     * Only columns for the defined fields will be shown in the datasheet
     *
     * fields = [
     *    'fieldName' => [
     *        'title' => 'Username',
     *        'hasFiltering' => true,
     *        'hasSorting' => true,
     *    ],
     * ]
     *
     * Dont forget to properly react on $sort and $filters in getItems() method
     */
    public function getFields()
    {
        return [
            'id' => [
                'title' => 'ID',
                'hasSorting' => true,
                'hasFiltering' => true,
            ],
            'name' => [
                'title' => 'Name',
                'hasFiltering' => true,
            ],
            'size' => [
                'title' => 'Size',
            ],
            'updatedAt' => [
                'title' => 'Last updated at',
            ],
            'path' => [
                'title' => 'Path',
                'hasFiltering' => true,
            ],
            'extension' => [
                'title' => 'Extension',
                'hasFiltering' => true,
            ],
        ];
    }

    public function filterItems($filters = [])
    {
        foreach ($this->items as $key => $item) {
            foreach ($filters as $field => $searchString) {
                if (!trim($searchString)) {
                    continue;
                }

                if($field == 'id'){
                    if($item[$field] != $searchString){
                        unset($this->items[$key]);
                    }
                    continue;
                }

                if (!stristr($item[$field], $searchString)) {
                    unset($this->items[$key]);
                }
            }
        }
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