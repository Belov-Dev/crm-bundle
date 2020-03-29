<?php

namespace A2Global\CRMBundle\Utility;



use Doctrine\Common\Inflector\Inflector;

class StringUtility
{
    public static function variate($original)
    {
        $readable = ucfirst(trim(mb_strtolower(preg_replace('/\s{1,}/', ' ', preg_replace('/[^a-zA-Z\d\s]/',' ',$original)))));
        $snakeCase = mb_strtolower(str_replace(' ', '_', $readable));
        $camelCase = lcfirst(implode(array_map('ucfirst', explode(' ', $readable))));
        $pascalCase = ucfirst($camelCase);

        return [
            'readable' => $readable,
            'snakeCase' => $snakeCase,
            'snakeCasePlural' => self::pluralize($snakeCase),
            'camelCase' => $camelCase,
            'camelCasePlural' => self::pluralize($camelCase),
            'pascalCase' => $pascalCase,
        ];
    }

    public static function camelCaseToSnakeCase($string): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $string)), '_');
    }

    public static function snakeCaseToCamelCase($string): string
    {
        return implode(array_map('ucfirst', explode(' ', $string)));
    }

    public static function pluralize($string): string
    {
        return Inflector::pluralize($string);
    }

    public static function singularize($string): string
    {
        return Inflector::singularize($string);
    }
}