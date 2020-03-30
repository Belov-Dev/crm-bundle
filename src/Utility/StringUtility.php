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

    public static function pluralize($string): string
    {
        return Inflector::pluralize($string);
    }

    public static function singularize($string): string
    {
        return Inflector::singularize($string);
    }
}