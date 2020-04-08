<?php

namespace A2Global\CRMBundle\Utility;

use Doctrine\Common\Inflector\Inflector;

class StringUtility
{
    static public function normalize($string): string
    {
        // remove everything, except letters, numbers and spaces
        $string = preg_replace('/[^a-zA-Z\d\s]/', ' ', $string);
        // 'SuperEXTRAString' -> 'Super EXTRA String'
        $string = preg_replace('/[A-Z]([A-Z](?![a-z]))*/', ' $0', $string);
        // remove multiply spaces and trim
        $string = trim(preg_replace('/\s{1,}/', ' ', $string));
        // lowercase everything, uppercase only first letter
        $string = ucfirst(mb_strtolower($string));

        return $string;
    }

    static public function toCamelCase($string): string
    {
        return lcfirst(implode(array_map('ucfirst', explode(' ', self::normalize($string)))));
    }

    static public function toSnakeCase($string): string
    {
        return mb_strtolower(implode('_', explode(' ', self::normalize($string))));
    }

    static public function toPascalCase($string): string
    {
        return ucfirst(self::toCamelCase($string));
    }

    static public function urlize($string): string
    {
        return mb_strtolower(implode('-', explode(' ', self::normalize($string))));
    }

    public static function pluralize($string): string
    {
        return Inflector::pluralize($string);
    }

    public static function singularize($string): string
    {
        return Inflector::singularize($string);
    }

    public static function getVariations($string): array
    {
        return [
            'readable' => self::normalize($string),
            'readablePlural' => self::pluralize(self::normalize($string)),
            'camelCase' => self::toCamelCase($string),
            'snakeCase' => self::toSnakeCase($string),
            'snakeCasePlural' => self::pluralize(self::toSnakeCase($string)),
            'pascalCase' => self::toPascalCase($string),
        ];
    }

    public static function getShortClassName($fullyQualifiedClassNameOrObject, string $trimRight = null): string
    {
        // using second best variant. not creating ReflectionClass, not eating more memory.
        // https://stackoverflow.com/a/41264231

        if (is_object($fullyQualifiedClassNameOrObject)) {
            $fullyQualifiedClassNameOrObject = get_class($fullyQualifiedClassNameOrObject);
        }
        $result = substr($fullyQualifiedClassNameOrObject, strrpos($fullyQualifiedClassNameOrObject, '\\') + 1);

        if ($trimRight) {
            $result = substr($result, 0, -1 * strlen($trimRight));
        }

        return $result;
    }

    public static function toMySQLTableName($string): string
    {
        return self::pluralize(self::toSnakeCase($string));
    }
}