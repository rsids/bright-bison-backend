<?php
namespace Bright\utils;

/**
 * Created by PhpStorm.
 * User: ids
 * Date: 11-1-17
 * Time: 10:06
 */
class ArrayUtils
{
    public static function IsAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}