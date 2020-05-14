<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Datasheet\DatasheetExtended;
use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\Common\Annotations\Annotation\Required;
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

    public function setDatasheet(Datasheet $datasheet): self
    {
        $this->datasheet = $datasheet;

        return $this;
    }

    public function getDatasheet(): DatasheetExtended
    {
        return $this->datasheet;
    }

    public function build($page = null, $itemsPerPage = null, $filters = [])
    {
        $this->updateItems();
        $this->getDatasheet()->setDebugMode($this->parameterBag->get('kernel.environment') == 'dev');

    }

    protected function updateItems()
    {
        $items = [];

        foreach ($this->getDatasheet()->getItems() as $itemOriginal) {
            $item = [];

            foreach ($this->getDatasheet()->getFields() as $fieldName => $fieldOptions) {
                $value = $itemOriginal[$fieldName];
                $value = $this->handleValue($value);

                if (isset($this->getDatasheet()->getFieldHandlers()[$fieldName])) {
                    $callable = $this->getDatasheet()->getFieldHandlers()[$fieldName];

                    try {
                        $value = $callable($itemOriginal);
                    } catch (Throwable $e) {
                        throw new DatasheetException(sprintf('Datasheet failed to process handler for field `%s` with `%s`', $fieldName, $e->getMessage()));
                    }
                }
                $item[$fieldName] = $value;
            }
            $items[] = $item;
        }
        $this->getDatasheet()->setItems($items);
    }

    protected function handleValue($value)
    {
        if (is_bool($value)) {
            if ($value) {
                return '<span class="badge bg-light-blue">Yes</span>';
            } else {
                return '<span class="badge">No</span>';
            }
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d-m-Y');
        }

        if (is_object($value)) {
            if (!method_exists($value, '__toString')) {
                return StringUtility::normalize(StringUtility::getShortClassName($value)) . ' #' . $value->getId();
            } else {
                return (string)$value;
            }
        }

        return $value;
    }

    /** QB builders common part */

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
}