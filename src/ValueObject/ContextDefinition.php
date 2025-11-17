<?php

declare(strict_types=1);

namespace Rector\Behastan\ValueObject;

final class ContextDefinition
{
    private int $usageCount = 0;

    public function __construct(
        private readonly string $filePath,
        private readonly string $class,
        private readonly string $methodName,
        private readonly string $mask,
        private readonly int $methodLine
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

    public function getMethodLine(): int
    {
        return $this->methodLine;
    }

    /**
     * @param string[] $featureInstructions
     */
    public function recordUsage(array $featureInstructions): void
    {
        $usageCount = 0;
        foreach ($featureInstructions as $featureInstruction) {
            if ($this->mask === $featureInstruction) {
                ++$usageCount;
            }
        }

        $this->usageCount = $usageCount;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }
}
