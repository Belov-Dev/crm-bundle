<?php
declare(strict_types=1);

namespace A2Global\CRMBundle\Twig;

use A2Global\CRMBundle\Utility\StringUtility;
use App\Cindy\Twig\AppRuntimeMenu;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension {

    public function getFilters() {
        return [
            new TwigFilter('toCamelCase', [$this, 'toCamelCase']),
            new TwigFilter('toSnakeCase', [$this, 'toSnakeCase']),
            new TwigFilter('toPascalCase', [$this, 'toPascalCase']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('crm_menu', [AppRuntimeFunctions::class, 'getMenu'], ['is_safe' => ['html']]),
        ];
    }

    public function toCamelCase($input) {
        return StringUtility::toCamelCase($input);
    }

    public function toSnakeCase($input) {
        return StringUtility::toSnakeCase($input);
    }

    public function toPascalCase($input) {
        return StringUtility::toPascalCase($input);
    }
}