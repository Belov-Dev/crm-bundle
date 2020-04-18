<?php

namespace A2Global\CRMBundle\Builder;

use A2Global\CRMBundle\Datasheet\Datasheet;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class DatasheetBuilder
{
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
        // Get all query string params
        $queryString = $this->requestStack->getMasterRequest()->query->all();

        // Set pages
        $currentPage = isset($queryString['page']) && ((int) $queryString['page'] > 0) ? (((int) $queryString['page']) - 1) : 0;
        $datasheet->setPage($currentPage);

        // Set filters
        $filters = $queryString['filters'] ?? [];
        $datasheet->setFilters($filters);

        // Build datasheet
        $datasheet->build();
//        $hasActions = !empty($datasheet->getActionsTemplate());
//        $hasAction = !empty($datasheet->getActionTemplate());

        if (!count($datasheet->getItems())) {
            return $this->twig->render('@A2CRM/datasheet/datasheet.table.empty.html.twig');
        }

        return $this->twig->render('@A2CRM/datasheet/datasheet.table.html.twig', [
            'datasheet' => $datasheet,
            'filters' => $filters,
//            'hasActions' => false,//$hasActions,
//            'actionsTemplate' => null,//$hasActions ? $datasheet->getActionsTemplate() : null,
//            'hasAction' => false,//$hasAction,
//            'actionTemplate' => null,//$hasAction ? $datasheet->getActionTemplate() : null,
//            'hasFiltering' => false, $this->hasFiltering($datasheet->getFields()),
//            'filterFormUrl' => null, //$filterFormUrl,
//            'filterFormHiddenFields' => null,//$filterFormHiddenFields,
        ]);
    }

    public function getPagination(Datasheet $datasheet)
    {
        $currentPage = $datasheet->getPage();
        $itemsTotal = $datasheet->getItemsTotal();
        $pagesTotal = (int)ceil($itemsTotal / $datasheet->getItemsPerPage());

        if (!$pagesTotal) {
            return null;
        }
        $maxPages = self::MAX_PAGES_IN_PAGINATOR;
        $maxPagesHalf = (int)ceil($maxPages / 2);

        // Creating query string pattern
        $queryString = $this->requestStack->getMasterRequest()->query->all();
        $queryString['per_page'] = $datasheet->getItemsPerPage();
        unset($queryString['page']);
        unset($queryString['per_page']);

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
                'currentPage' => $currentPage + 1,
                'perPage' => $datasheet->getItemsPerPage(),
                'totalPages' => $pagesTotal,
                'pagesFrom' => $pagesFrom,
                'pagesTo' => $pagesTo,
                'hasPreviousPage' => $currentPage > 0,
                'previousPage' => $currentPage,
                'hasNextPage' => $currentPage < $pagesTotal - 1,
                'nextPage' => $currentPage + 2,
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