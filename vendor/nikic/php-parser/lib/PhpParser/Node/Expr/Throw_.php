<?php

declare (strict_types=1);
namespace Behastan202502\PhpParser\Node\Expr;

use Behastan202502\PhpParser\Node;
class Throw_ extends Node\Expr
{
    /** @var Node\Expr Expression */
    public $expr;
    /**
     * Constructs a throw expression node.
     *
     * @param Node\Expr $expr Expression
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Node\Expr $expr, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->expr = $expr;
    }
    public function getSubNodeNames(): array
    {
        return ['expr'];
    }
    public function getType(): string
    {
        return 'Expr_Throw';
    }
}
