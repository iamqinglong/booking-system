<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
class CustomEnum extends Enum
{
    public static function toArrayDescription()
    {
        $arr = [];

        foreach (static::getValues() as $v) {
            $arr[] = ['name' => static::getDescription($v), 'value' => $v];
        }
        return $arr;
    }

}
