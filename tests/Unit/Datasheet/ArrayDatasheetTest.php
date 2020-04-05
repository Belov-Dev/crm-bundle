<?php

namespace A2Global\CRMBundle\Tests\Unit\Datasheet;

use A2Global\CRMBundle\Datasheet\ArrayDatasheet;
use A2Global\CRMBundle\Datasheet\SampleDatasheet;
use PHPUnit\Framework\TestCase;

class ArrayDatasheetTest extends TestCase
{
    const WORDS_LIST = ['AlphaOne', 'BravoOne', 'CharlieTwo'];

    /** @var ArrayDatasheet */
    protected $datasheet;

    public function testFiltering()
    {
        $datasheet = $this->getDatasheet();
        $this->assertInstanceOf(ArrayDatasheet::class, $datasheet);
        $this->assertNotEmpty($datasheet->getItems());
        $this->assertIsArray($datasheet->getItems());
        $this->assertEquals('AlphaOne', $datasheet->getItems()[0]['name']);
        $this->assertEquals('BravoOne', $datasheet->getItems()[1]['name']);
        $this->assertEquals('CharlieTwo', $datasheet->getItems()[2]['name']);
        $datasheet->applyFilters(['name' => 'One']);

        foreach ($datasheet->getItems() as $item) {
            $this->assertStringContainsString('One', $item['name']);
        }
    }

    public function testPagination()
    {
        $datasheet = $this->getDatasheet();

        $datasheet->buildItemsTotal();
        $this->assertCount(3, $datasheet->getItems());
        $this->assertEquals(3, $datasheet->getItemsTotal());
        $this->assertStringContainsString('Alpha', $datasheet->getItems()[0]['name']);

        $datasheet->applyPagination(2, 2);
        $datasheet->buildItemsTotal();
        $this->assertCount(1, $datasheet->getItems());
        $this->assertEquals(1, $datasheet->getItemsTotal());
        $this->assertStringContainsString('Charlie', $datasheet->getItems()[0]['name']);
    }

    protected function getDatasheet(): ArrayDatasheet
    {
        if (!$this->datasheet) {
            $this->initializeDatasheet();
        }
        $this->datasheet->setItems($this->getSampleItems());

        return $this->datasheet;
    }

    protected function initializeDatasheet()
    {
        $this->datasheet = new SampleDatasheet();
        $this->datasheet->setItems($this->getSampleItems());
    }

    protected function getSampleItems()
    {
        $items = [];

        for ($i = 0; $i < 3; $i++) {
            $item = [
                'id' => $i + 1,
                'name' => self::WORDS_LIST[$i],
                'units' => $i * 200,
            ];
            $items[] = $item;
        }

        return $items;
    }
}