<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Component\Datasheet\FieldType\DataSheetFieldTypeInterface;
use A2Global\CRMBundle\Component\Datasheet\FieldType\TypeString;
use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Datasheet\DatasheetExtended;
use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Registry\DatasheetFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

abstract class AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    const NEST_SEPARATOR = "___";

    /** @var DatasheetExtended */
    protected $datasheet;

    /** @var EntityInfoProvider */
    protected $entityInfoProvider;

    /** @var ParameterBagInterface */
    protected $parameterBag;

    /** @var DatasheetFieldRegistry */
    protected $datasheetFieldRegistry;

    public function setDatasheet(Datasheet $datasheet): self
    {
        $this->datasheet = $datasheet;

        return $this;
    }

    public function getDatasheet(): DatasheetExtended
    {
        return $this->datasheet;
    }

    public function build($page = null, $itemsPerPage = null, $filters = [], $sorting = [])
    {
        $this->updateItems();
        $this->getDatasheet()->setDebugMode($this->parameterBag->get('kernel.environment') == 'dev');
    }

    protected function updateItems()
    {
        $rowNumber = 0;
        $items = [];

        foreach ($this->getDatasheet()->getItems() as $itemOriginal) {
            ++$rowNumber;
            $item = [];

            foreach ($this->getDatasheet()->getFields() as $fieldName => $fieldOptions) {
                $value = $itemOriginal[$fieldName];

                if (isset($this->getDatasheet()->getFieldHandlers()[$fieldName])) {
                    $callable = $this->getDatasheet()->getFieldHandlers()[$fieldName];

                    try {
                        $value = $callable($itemOriginal);
                    } catch (Throwable $e) {
                        throw new DatasheetException(sprintf('Datasheet failed to process handler for field `%s` with `%s`', $fieldName, $e->getMessage()));
                    }
                    $value = sprintf('<td id="ds_%s_%s">%s</td>', $rowNumber, StringUtility::toSnakeCase($fieldName), $value);
                } else {
                    $value = $this->handleValue($value, $fieldOptions, $rowNumber, $fieldName);
                }
                $item[$fieldName] = $value;
            }
            $items[] = $item;
        }
        $this->getDatasheet()->setItems($items);
    }

    protected function handleValue($value, $fieldOptions, $rowNumber, $fieldName)
    {
        $handler = $this->getFieldHandler($value, $fieldOptions);
        $item = $handler->get($value, $fieldOptions);

        if (!is_array($item)) {
            $item = [
                'value' => $item,
            ];
        }

        return sprintf(
            '<td%s id="ds_%s_%s">%s</td>',
            isset($item['class']) ? sprintf(' class="%s"', $item['class']) : '',
            $rowNumber,
            StringUtility::toSnakeCase($fieldName),
            $item['value']
        );
    }

    protected function getFieldHandler($value, $fieldOptions): DataSheetFieldTypeInterface
    {
        foreach ($this->datasheetFieldRegistry->findAll() as $fieldHandler) {
            if (isset($fieldOptions['type'])) {
                if ($fieldOptions['type'] == get_class($fieldHandler)) {
                    return $fieldHandler;
                }
            } else {
                if ($fieldHandler->supports($value, $fieldOptions)) {
                    return $fieldHandler;
                }
            }

            if ($fieldHandler instanceof TypeString) {
                $default = $fieldHandler;
            }
        }

        return $default;
    }

    /** DI */

    /** @Required */
    public function setEntityInfoProvider(EntityInfoProvider $entityInfoProvider)
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }

    /** @Required */
    public function setParameterBag(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /** @Required */
    public function setDatasheetFieldRegistry(DatasheetFieldRegistry $datasheetFieldRegistry)
    {
        $this->datasheetFieldRegistry = $datasheetFieldRegistry;
    }
}