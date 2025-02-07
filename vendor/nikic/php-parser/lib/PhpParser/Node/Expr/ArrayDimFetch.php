<?php

declare (strict_types=1);
namespace Behastan202502\PhpParser\Node\Expr;

use Behastan202502\PhpParser\Node\Expr;
class ArrayDimFetch extends Expr
{
    /** @var Expr Variable */
    public $var;
    /** @var null|Expr Array index / dim */
    public $dim;
    /**
     * Constructs an array index fetch node.
     *
     * @param Expr $var Variable
     * @param null|Expr $dim Array index / dim
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Expr $var, ?Expr $dim = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->dim = $dim;
    }
    public function getSubNodeNames(): array
    {
        return ['var', 'dim'];
    }
    public function getType(): string
    {
        return 'Expr_ArrayDimFetch';
    }
}
