<?php

namespace Behastan\ValueObject\Mask;

use Behastan\Contract\MaskInterface;
abstract class AbstractMask implements MaskInterface
{
    /**
     * @readonly
     * @var string
     */
    public $mask;
    /**
     * @readonly
     * @var string
     */
    public $filePath;
    /**
     * @readonly
     * @var string
     */
    public $className;
    /**
     * @readonly
     * @var string
     */
    public $methodName;
    public function __construct(string $mask, string $filePath, string $className, string $methodName)
    {
        $this->mask = $mask;
        $this->filePath = $filePath;
        $this->className = $className;
        $this->methodName = $methodName;
    }
}
