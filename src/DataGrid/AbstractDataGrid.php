<?php

namespace A2Global\CRMBundle\DataGrid;

use A2Global\CRMBundle\Utility\StringUtility;
use Twig\Environment;

abstract class AbstractDataGrid implements DataGridInterface
{
    /** @var Environment */
    protected $twigEnvironment;

    /**
     * @required
     */
    public function setTwigEnvironment(Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
    }

    public function getRowActionsTemplateName(string $objectName): ?string
    {
        $templateName = sprintf('crm/datagrid.%s.actions.html.twig', StringUtility::toSnakeCase($objectName));

        if ($this->twigEnvironment->getLoader()->exists($templateName)) {
            return $templateName;
        }

        return null;
    }
}