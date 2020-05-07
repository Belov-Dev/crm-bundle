<?php

namespace A2Global\CRMBundle\Datasheet;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class DatasheetProvider
{
    const MAX_PAGES_IN_PAGINATOR = 5;

    protected $twig;

    protected $requestStack;

    protected $datasheetBuilder;

    public function __construct(
        DatasheetBuilder $datasheetBuilder,
        Environment $twig,
        RequestStack $requestStack
    )
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->datasheetBuilder = $datasheetBuilder;
    }

    public function getTable(DatasheetExtended $datasheet)
    {
        $queryString = $this->requestStack->getMasterRequest()->query->all();
        $datasheet
            ->setPage(max(1, (int)($queryString['page'] ?? 0)) - 1)
            ->setItemsPerPage((int)($queryString['perPage'] ?? 15));
        $this->datasheetBuilder->build($datasheet);

        // Filter form url (reset filters, page. leaving per_page)
//        if ($this->datasheetBuilder->) {
//            unset($queryString['page']);
//            unset($queryString['filter']);
//            $filterFormUrl = http_build_query($queryString);
//        }

        if (!$datasheet->getItemsTotal()) {
            return $this->twig->render('@A2CRM/datasheet/datasheet.table.empty.html.twig', [
                'datasheet' => $datasheet,
            ]);
        }

        return $this->twig->render('@A2CRM/datasheet/datasheet.table.html.twig', [
            'datasheet' => $datasheet,
            'filterFormUrl' => $filterFormUrl ?? null,
        ]);
    }

    public function getPagination(DatasheetExtended $datasheet)
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