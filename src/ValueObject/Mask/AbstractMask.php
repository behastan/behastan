<?php

namespace Rector\Behastan\ValueObject\Mask;

use Rector\Behastan\Contract\MaskInterface;

abstract class AbstractMask implements MaskInterface
{
    public function __construct(
        public readonly string $mask,
        public readonly string $filePath,
        public readonly int $line,
        public readonly string $className,
        public readonly string $methodName,
    ) {
    }
}
