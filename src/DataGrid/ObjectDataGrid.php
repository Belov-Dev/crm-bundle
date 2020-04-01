<?php

namespace A2Global\CRMBundle\DataGrid;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ObjectDataGrid
{
    const PER_PAGE = 15;

    const MAX_PAGES_IN_PAGINATOR = 5;

    private $fields = [];

    private $data = [];

    private $currentPage = 1;

    private $perPage = self::PER_PAGE;

    private $pagesTotal = 0;

    private $queryString = [];

    /** @var Entity */
    private $entity;

    /** @var ServiceEntityRepository */
    private $entityRepository;

    private $entityManager;

    private $entityFieldRegistry;

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

    public function getData(): array
    {
        return $this->data;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getPagination()
    {
        $maxPages = self::MAX_PAGES_IN_PAGINATOR;
        $maxPagesHalf = (int)ceil($maxPages / 2);
        $queryString = $this->queryString;
        unset($queryString['page']);
        $queryString = http_build_query($queryString);

        if($this->currentPage <= $maxPagesHalf){
            $pagesFrom = 1;
            $pagesTo = min($maxPages, $this->pagesTotal);
        }elseif($this->currentPage > ($this->pagesTotal - $maxPagesHalf)){
            $pagesFrom = max(1, $this->pagesTotal - $maxPages + 1);
            $pagesTo = $this->pagesTotal;
        }else{
            $pagesFrom = $this->currentPage - $maxPagesHalf + 1;
            $pagesTo = $this->currentPage + $maxPagesHalf - 1;
        }

        return [
            'enabled' => $this->pagesTotal > 1,
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
            'totalPages' => $this->pagesTotal,
            'pagesFrom' => $pagesFrom,
            'pagesTo' => $pagesTo,
            'hasPreviousPage' => $this->currentPage > 1,
            'previousPage' => $this->currentPage - 1,
            'hasNextPage' => $this->currentPage < $this->pagesTotal,
            'nextPage' => $this->currentPage + 1,
            'showFirstPage' => $pagesFrom > 1,
            'showLastPage' => $this->currentPage + $maxPagesHalf <= $this->pagesTotal,
            'url' => '?'.$queryString,
        ];
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
                    $value = $value ? '+' : '-';
                } elseif ($value instanceof DateTimeInterface) {
                    $value = $value->format('H:i:s j/m/Y');
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