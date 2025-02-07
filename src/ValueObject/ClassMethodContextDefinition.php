<?php

declare (strict_types=1);
namespace Behastan\ValueObject;

final class ClassMethodContextDefinition
{
    /**
     * @readonly
     * @var string
     */
    private $filePath;
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @readonly
     * @var string
     */
    private $mask;
    /**
     * @readonly
     * @var int|null
     */
    private $methodLine;
    public function __construct(string $filePath, string $class, string $methodName, string $mask, ?int $methodLine = null)
    {
        $this->filePath = $filePath;
        $this->class = $class;
        $this->methodName = $methodName;
        $this->mask = $mask;
        $this->methodLine = $methodLine;
    }
    public function getFilePath(): string
    {
        return $this->filePath;
    }
    public function getClass(): string
    {
        return $this->class;
    }
    public function getMethodName(): string
    {
        return $this->methodName;
    }
    public function getMask(): string
    {
        return $this->mask;
    }
    public function getMethodLine(): ?int
    {
        return $this->methodLine;
    }
}
