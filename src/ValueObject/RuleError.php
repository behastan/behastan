<?php

declare(strict_types=1);

namespace Rector\Behastan\ValueObject;

final readonly class RuleError
{
    /**
     * @param string[] $lineFilePaths
     */
    public function __construct(
        private string $message,
        private array $lineFilePaths
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string[]
     */
    public function getLineFilePaths(): array
    {
        return $this->lineFilePaths;
    }
}
