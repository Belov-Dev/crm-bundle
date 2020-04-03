<?php

namespace A2Global\CRMBundle\DataGrid;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ObjectDataSheet extends AbstractDataSheet
{
    protected $data = [];

    protected $fields = [];

    private $entityManager;

    private $entityFieldRegistry;

    /** @var Entity */
    private $entity;

    /** @var ServiceEntityRepository */
    private $entityRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityFieldRegistry $entityFieldRegistry
    )
    {
        $this->entityManager = $entityManager;
        $this->entityFieldRegistry = $entityFieldRegistry;
    }

    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function build($options)
    {
        $this->entityRepository = $this->entityManager
            ->getRepository('App:' . StringUtility::toPascalCase($this->entity->getName()));
        $this->currentPage = $options['page'] ?? 1;
        $this->perPage = $options['per_page'] ?? self::PER_PAGE;
        $this->queryString = [
            'page' => $this->currentPage,
            'per_page' => $this->perPage,
        ];

        $this->buildFields();
        $this->buildData();
        $this->calculatePagesTotal();

        return $this;
    }

    public function getPagination()
    {

    }

    public function getRowActionsTemplateName(): ?string
    {
        return '@A2CRM/datagrid/datagrid.actions.html.twig';
    }

    protected function buildFields()
    {
        $this->fields['id'] = [
            'title' => '#',
        ];

        /** @var EntityField $field */
        foreach ($this->entity->getFields() as $field) {
            $fieldNameCamelCase = StringUtility::toCamelCase($field->getName());
            $this->fields[$fieldNameCamelCase] = [
                'title' => $field->getName(),
            ];
        }
    }

    protected function buildData()
    {
        $offset = ($this->currentPage - 1) * $this->perPage;

        foreach ($this->entityRepository->findBy([], [], $this->perPage, $offset) as $object) {
            $item = ['id' => $object->getId()];

            foreach ($this->fields as $fieldNameCamelCase => $fieldName) {
                $getter = 'get' . $fieldNameCamelCase;
                $value = $object->{$getter}();

                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                } elseif ($value instanceof DateTimeInterface) {
                    $value = $value->format('j/m/Y');
                } elseif (is_object($value)) {
                    if (!method_exists($value, '__toString')) {
                        $value = StringUtility::normalize(StringUtility::getShortClassName($value)) . ' #' . $value->getId();
                    }
                }
                $item[$fieldNameCamelCase] = $value;
            }
            $this->data[] = $item;
        }
    }

    protected function calculatePagesTotal()
    {
        $totalItems = $this->entityRepository
            ->createQueryBuilder('s')
            ->select('count(s)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->pagesTotal = (int)ceil($totalItems / $this->perPage);
    }
}