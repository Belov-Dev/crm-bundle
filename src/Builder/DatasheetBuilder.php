<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Datasheet\DatasheetInterface;
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

    public function getTable(Datasheet $datasheet)
    {
        $queryString = $this->requestStack->getMasterRequest()->query->all();
        $currentPage = $queryString['page'] ?? self::DEFAULT_PAGE;
        $perPage = $queryString['per_page'] ?? $datasheet->getItemsPerPage();
        $startFrom = ($currentPage - 1) * $perPage;
        $filters = $queryString['filters'] ?? [];
        unset($queryString['filters']);
        $filterFormUrl = http_build_query($queryString);
        $filterFormHiddenFields = $queryString;
        unset($filterFormHiddenFields['page']);
        $datasheet->build();


//        $datasheet->build($startFrom, $perPage, null, $filters);

        if (!count($datasheet->getItems())) {
            return $this->twig->render('@A2CRM/datasheet/datasheet.table.empty.html.twig');
        }

//        if ($datasheet instanceof ArrayDatasheet) {
//            $datasheet->buildFields();
//            $datasheet->applyFilters($filters);
//            $datasheet->buildItemsTotal();
//            $datasheet->applyPagination($startFrom, $perPage);
//        }
        $hasActions = !empty($datasheet->getActionsTemplate());
        $hasAction = !empty($datasheet->getActionTemplate());

        return $this->twig->render('@A2CRM/datasheet/datasheet.table.html.twig', [
            'datasheet' => $datasheet,
            'fields' => $datasheet->getFields(),
            'items' => $datasheet->getItems(),
            'hasActions' => $hasActions,
            'actionsTemplate' => $hasActions ? $datasheet->getActionsTemplate() : null,
            'hasAction' => $hasAction,
            'actionTemplate' => $hasAction ? $datasheet->getActionTemplate() : null,
            'hasFiltering' => $this->hasFiltering($datasheet->getFields()),
            'filterFormUrl' => $filterFormUrl,
            'filterFormHiddenFields' => $filterFormHiddenFields,
            'filters' => $filters,
        ]);
    }

    public function getPagination(DataSheetInterface $datasheet)
    {
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

        return $this->twig->render('@A2CRM/datasheet/datasheet.pagination.html.twig', [
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

    protected function hasFiltering($fields): bool
    {
        foreach ($fields as $field) {
            if (isset($field['hasFiltering']) && $field['hasFiltering']) {
                return true;
            }
        }

        return false;
    }
}