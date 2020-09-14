<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SuggestedScheduleRemark extends Enum
{
    const PENDING = 0;
    const APPROVED = 1;
    const DECLINED = 2;
}
