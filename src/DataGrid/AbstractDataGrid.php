<?php

namespace A2Global\CRMBundle\DataGrid;

use Twig\Environment;

abstract class AbstractDataGrid implements DataGridInterface
{
    const PER_PAGE = 10;

    const MAX_PAGES_IN_PAGINATOR = 5;

    protected $currentPage;

    protected $perPage;

    protected $pagesTotal;

    protected $queryString;

    protected $data = [];

    protected $fields = [];

    /** @var Environment */
    protected $twigEnvironment;

    /**
     * @required
     */
    public function setTwigEnvironment(Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
    }

    public function getRowActionsTemplateName(): ?string
    {
        return null;
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
        $queryString = $this->getQueryString();
        unset($queryString['page']);
        $queryString = http_build_query($queryString);

        if ($this->getCurrentPage() <= $maxPagesHalf) {
            $pagesFrom = 1;
            $pagesTo = min($maxPages, $this->getPagesTotal());
        } elseif ($this->getCurrentPage() > ($this->getPagesTotal() - $maxPagesHalf)) {
            $pagesFrom = max(1, $this->getPagesTotal() - $maxPages + 1);
            $pagesTo = $this->getPagesTotal();
        } else {
            $pagesFrom = $this->getCurrentPage() - $maxPagesHalf + 1;
            $pagesTo = $this->getCurrentPage() + $maxPagesHalf - 1;
        }

        return [
            'enabled' => $this->getPagesTotal() > 1,
            'currentPage' => $this->getCurrentPage(),
            'perPage' => $this->perPage,
            'totalPages' => $this->getPagesTotal(),
            'pagesFrom' => $pagesFrom,
            'pagesTo' => $pagesTo,
            'hasPreviousPage' => $this->getCurrentPage() > 1,
            'previousPage' => $this->getCurrentPage() - 1,
            'hasNextPage' => $this->getCurrentPage() < $this->getPagesTotal(),
            'nextPage' => $this->getCurrentPage() + 1,
            'showFirstPage' => $pagesFrom > 1,
            'showLastPage' => $this->getCurrentPage() + $maxPagesHalf <= $this->getPagesTotal(),
            'url' => '?' . $queryString,
        ];
    }

    protected function getPagesTotal()
    {
        return $this->pagesTotal;
    }

    protected function getCurrentPage()
    {
        return $this->currentPage;
    }

    protected function getPerPage()
    {
        return $this->perPage;
    }

    protected function getQueryString()
    {
        return $this->queryString;
    }
}