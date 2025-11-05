<?php

declare(strict_types=1);

namespace Rector\Behastan\Analyzer;

final class MaskAnalyzer
{
    /**
     * @var string
     */
    private const MASK_REGEX = '#(\:[\W\w]+)#';

    public static function isRegex(string $rawMask): bool
    {
        if (str_starts_with($rawMask, '/')) {
            return true;
        }

        return str_ends_with($rawMask, '#');
    }

    public static function isValueMask(string $rawMask): bool
    {
        preg_match(self::MASK_REGEX, $rawMask, $match);

        return $match !== [];
    }
}
