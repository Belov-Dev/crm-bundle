<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Datasheet\DatasheetBuilder\AbstractDatasheetBuilder;
use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Registry\DatasheetBuilderRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;
use Twig\Environment;

class DatasheetProvider
{
    const MAX_PAGES_IN_PAGINATOR = 5;

    protected $twig;

    protected $requestStack;

    protected $datasheetBuilderRegistry;

    public function __construct(
        Environment $twig,
        RequestStack $requestStack,
        DatasheetBuilderRegistry $datasheetBuilderRegistry
    )
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->datasheetBuilderRegistry = $datasheetBuilderRegistry;
    }

    public function getTable(Datasheet $datasheet)
    {
        $datasheet = $this->decorate($datasheet);
        $builder = $this->getBuilder($datasheet);

        $request = $this->requestStack->getMasterRequest();
        $queryString = $request->query->all();
        $filters = $queryString['datasheet_' . $datasheet->getUniqueId()] ?? [];
        $builder->build($request->get('page'), $request->get('perPage'), $filters);

//        if ($this->datasheetBuilder->) {
//            unset($queryString['page']);
//            unset($queryString['filter']);
//            $filterFormUrl = http_build_query($queryString);
//        }

        return $this->twig->render('@A2CRM/datasheet/datasheet.table.html.twig', [
            'datasheet' => $datasheet,
            'filterFormUrl' => $filterFormUrl ?? null,
        ]);
    }

    public function getPagination(Datasheet $datasheet)
    {
        /** @var DatasheetExtended $datasheet */
        $datasheet = $datasheet->extended;
        $currentPage = $datasheet->getPage() - 1;
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

    public function decorate(Datasheet $datasheet): DatasheetExtended
    {
        $datasheetExtended = new DatasheetExtended($datasheet());
        $datasheet->extended = $datasheetExtended;

        return $datasheetExtended;
    }

    protected function getBuilder(Datasheet $datasheet): AbstractDatasheetBuilder
    {
        foreach ($this->datasheetBuilderRegistry->get() as $builder) {
            if ($builder->setDatasheet($datasheet)->supports()) {
                return $builder;
            }
        }

        throw new DatasheetException('Failed to resolve builder for the datasheet');
    }
}