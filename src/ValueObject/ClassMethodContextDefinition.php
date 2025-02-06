<?php

declare(strict_types=1);

namespace Behastan\ValueObject;

final readonly class ClassMethodContextDefinition
{
    public function __construct(
        private string $filePath,
        private string $class,
        private string $methodName,
        private string $mask,
        private ?int $methodLine = null
    ) {
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
