<?php

declare (strict_types=1);
namespace Behastan202502\PhpParser\Lexer\TokenEmulator;

use Behastan202502\PhpParser\PhpVersion;
use Behastan202502\PhpParser\Token;
/** @internal */
abstract class TokenEmulator
{
    abstract public function getPhpVersion(): PhpVersion;
    abstract public function isEmulationNeeded(string $code): bool;
    /**
     * @param Token[] $tokens Original tokens
     * @return Token[] Modified Tokens
     */
    abstract public function emulate(string $code, array $tokens): array;
    /**
     * @param Token[] $tokens Original tokens
     * @return Token[] Modified Tokens
     */
    abstract public function reverseEmulate(string $code, array $tokens): array;
    /** @param array{int, string, string}[] $patches */
    public function preprocessCode(string $code, array &$patches): string
    {
        return $code;
    }
}
