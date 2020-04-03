<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\DataSheet\DataSheetInterface;
use A2Global\CRMBundle\Utility\StringUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class DatasheetBuilder
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_PER_PAGE = 15;
    const MAX_PAGES_IN_PAGINATOR = 5;

    private $twig;

    private $requestStack;

    public function __construct(Environment $twig, RequestStack $requestStack)
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    public function getTable(DataSheetInterface $datasheet)
    {
        $hasPagination = method_exists($datasheet, 'getItemsTotal');
        $hasFields = method_exists($datasheet, 'getFields');

        if ($hasPagination) {
            $queryString = $this->requestStack->getMasterRequest()->query->all();
            $currentPage = $queryString['page'] ?? self::DEFAULT_PAGE;
            $perPage = $queryString['per_page'] ?? self::DEFAULT_PER_PAGE;
            $items = $datasheet->getItems(($currentPage - 1) * $perPage, $perPage);
        } else {
            $items = $datasheet->getItems();
        }

        if (!$hasFields) {
            $fields = [];

            foreach ($items[0] as $field => $value) {
                $fields[$field] = [
                    'title' => StringUtility::normalize($field),
                ];
            }
        }

        return $this->twig->render('@A2CRM/datasheet/datasheet.table.html.twig', [
            'fields' => $fields,
            'items' => $items,
        ]);
    }

    public function getPagination(DataSheetInterface $datasheet)
    {
        if (!method_exists($datasheet, 'getItemsTotal')) {
            return null;
        }
        $queryString = $this->requestStack->getMasterRequest()->query->all();
        $currentPage = $queryString['page'] ?? self::DEFAULT_PAGE;
        $perPage = $queryString['per_page'] ?? self::DEFAULT_PER_PAGE;
        $itemsTotal = $datasheet->getItemsTotal();
        $pagesTotal = (int)ceil($itemsTotal / $perPage);

        if (!$pagesTotal) {
            return null;
        }
        $maxPages = self::MAX_PAGES_IN_PAGINATOR;
        $maxPagesHalf = (int)ceil($maxPages / 2);

        // Creating query string pattern
        $queryString['per_page'] = $perPage;
        unset($queryString['page']);

        if ($currentPage <= $maxPagesHalf) {
            $pagesFrom = 1;
            $pagesTo = min($maxPages, $pagesTotal);
        } elseif ($currentPage > ($pagesTotal - $maxPagesHalf)) {
            $pagesFrom = max(1, $pagesTotal - $maxPages + 1);
            $pagesTo = $pagesTotal;
        } else {
            $pagesFrom = $currentPage - $maxPagesHalf + 1;
            $pagesTo = $currentPage + $maxPagesHalf - 1;
        }

        // TODO MINOR do not show ...5 when total 5 pages

        return $this->twig->render('@A2CRM/datasheet/datagrid.pagination.html.twig', [
            'pagination' => [
                'currentPage' => $currentPage,
                'perPage' => $perPage,
                'totalPages' => $pagesTotal,
                'pagesFrom' => $pagesFrom,
                'pagesTo' => $pagesTo,
                'hasPreviousPage' => $currentPage > 1,
                'previousPage' => $currentPage - 1,
                'hasNextPage' => $currentPage < $pagesTotal,
                'nextPage' => $currentPage + 1,
                'showFirstPage' => $pagesFrom > 1,
                'showLastPage' => $currentPage + $maxPagesHalf <= $pagesTotal,
                'queryString' => http_build_query($queryString),
            ],
        ]);
    }
}