<?php

namespace A2Global\CRMBundle\Utility;

class ArrayUtility
{
    public static function renameKey($array, $keyBefore, $keyAfter)
    {
        if (!array_key_exists($keyBefore, $array)) {
            return $array;
        }
        $keys = array_keys($array);
        $keys[array_search($keyBefore, $keys)] = $keyAfter;

        return array_combine($keys, $array);
    }
}