<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Datasheet\DatasheetExtended;
use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    const NEST_SEPARATOR = "___";

    /** @var DatasheetExtended */
    protected $datasheet;

    /** @var EntityInfoProvider */
    protected $entityInfoProvider;

    public function setDatasheet(DatasheetExtended $datasheet): self
    {
        $this->datasheet = $datasheet;

        return $this;
    }

    public function build()
    {
        $this->datasheet
            ->setItems($this->getItems())
            ->setItemsTotal($this->getItemsTotal());

        $fields = $this->datasheet->getFieldsToShow() ?: $this->getFields();

        foreach ($this->datasheet->getFieldsToRemove() as $fieldToRemove) {
            if (isset($fields[$fieldToRemove])) {
                unset($fields[$fieldToRemove]);
            }
        }

        foreach ($fields as $fieldName => $fieldOptions) {
            if (!$fieldOptions['hasFilter']) {
                continue;
            }
            $fields[$fieldName]['filters'] = $this->getFilters($fieldName);
            $this->datasheet->setHasFilters(true);
        }
        $this->datasheet->setFields($fields);
        $this->updateItems();
    }

    protected function updateItems()
    {
        $items = [];

        foreach ($this->datasheet->getItems() as $itemOriginal) {
            $item = [];

            foreach ($this->datasheet->getFields() as $fieldName => $fieldOptions) {
//                if (!isset($itemOriginal[$fieldName])) {
//                    throw new DatasheetException(sprintf('Datasheet failed to get %s value from data', $fieldName));
//                }
//                $value = $itemOriginal[$fieldName];
                $value = is_object($itemOriginal) ? $this->getObjectValue($itemOriginal, $fieldName) : $itemOriginal[$fieldName];
                $value = $this->handleValue($value);

//                if (isset($this->datasheet->fieldHandlers[$fieldName])) {
//                    $callable = $this->datasheet->fieldHandlers[$fieldName];
//
//                    try {
//                        $value = $callable($itemOriginal);
//                    } catch (Throwable $e) {
//                        throw new DatasheetException(sprintf('Datasheet failed to process handler for field `%s` with `%s`', $fieldName, $e->getMessage()));
//                    }
//                }
                $item[$fieldName] = $value;
            }
            $items[] = $item;
        }
        $this->datasheet->setItems($items);
    }

    protected function getObjectValue($object, $path)
    {
        $path = explode(self::NEST_SEPARATOR, $path);
        $subObject = $object->{'get' . $path[0]}();

        if (count($path) == 1) {
            return $subObject;
        }
        array_shift($path);

        return $this->getObjectValue($subObject, implode(self::NEST_SEPARATOR, $path));
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

    protected $entity;

    protected $joined = [];

    protected function getQbMainAlias(): string
    {
        return $this->datasheet->getQueryBuilder()->getDQLPart('from')[0]->getAlias();
    }

    protected function cloneQb(): QueryBuilder
    {
        return clone $this->datasheet->getQueryBuilder();
    }

    protected function getEntity(): Entity
    {
        if (!$this->entity) {
            /** @var From $firstFrom */
            $firstFrom = $this->datasheet->getQueryBuilder()->getDQLPart('from')[0];
            $this->entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($firstFrom->getFrom()));
        }

        return $this->entity;
    }

    protected function join(QueryBuilder $queryBuilder, $entity, $relation): QueryBuilder
    {
        $join = sprintf('%s.%s', $entity, $relation);

        if (!in_array($join, $this->joined)) {
            $queryBuilder->join($join, $relation);
            $this->joined[] = $join;
        }

        return $queryBuilder;
    }

    /** Interface methods */

    public function supports(): bool
    {
        throw new DatasheetException('Datasheet builder method should be implemented: ' . __METHOD__);
    }

    public function getItems(): array
    {
        throw new DatasheetException('Datasheet builder method should be implemented: ' . __METHOD__);
    }

    public function getItemsTotal(): int
    {
        throw new DatasheetException('Datasheet builder method should be implemented: ' . __METHOD__);
    }

    public function getFields(): array
    {
        throw new DatasheetException('Datasheet builder method should be implemented: ' . __METHOD__);
    }
}